import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:pitswal/app.dart';
import 'package:pitswal/purchases/entitlement_controller.dart';
import 'package:pitswal/repository/poetry_repository.dart';
import 'package:pitswal/screens/collection_detail_screen.dart';
import 'package:pitswal/screens/poem_reader_screen.dart';
import 'package:pitswal/services/api_client.dart';
import 'package:pitswal/settings/reader_settings.dart';
import 'package:pitswal/widgets/untitled_poem_marker.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'test_support.dart';

void main() {
  Future<ReaderSettings> settings() async {
    SharedPreferences.setMockInitialValues({});
    return ReaderSettings.load(await SharedPreferences.getInstance());
  }

  testWidgets('app is explicitly RTL and shows approved Pashto identity', (
    tester,
  ) async {
    await tester.pumpWidget(
      PitswalApp(
        repository: fixtureRepository(),
        readerSettings: await settings(),
        qaMode: false,
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('پېڅوَل'), findsWidgets);
    expect(find.text('اجمل اند بشپړه شاعري'), findsOneWidget);
    expect(find.text('ټولګې'), findsOneWidget);
    expect(find.text('ټولې ټولګې'), findsNothing);
    final homeForwardIcon = tester.widget<Icon>(
      find.byIcon(Icons.arrow_forward_rounded),
    );
    expect(homeForwardIcon.icon!.matchTextDirection, isTrue);
    final hasRtlRoot = tester
        .widgetList<Directionality>(find.byType(Directionality))
        .any((widget) => widget.textDirection == TextDirection.rtl);
    expect(hasRtlRoot, isTrue);
  });

  testWidgets(
    'reader ignores legacy layout mode and preserves source newlines',
    (tester) async {
      tester.view.physicalSize = const Size(360, 640);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);
      final longBody = List.generate(
        50,
        (index) => index == 2 ? '\nنوی بند' : 'ازاده کرښه ${index + 1}',
      ).join('\n');
      const poemTitle = 'اوږد ازموينيز شعر';
      final readerSettings = await settings();
      await readerSettings.setFontSize(38);
      final repository = PoetryRepository(
        api: ApiClient(
          client: MockClient(
            (_) async => http.Response.bytes(
              utf8.encode(
                jsonEncode({
                  'data': poemDetailJson(
                    title: poemTitle,
                    body: longBody,
                    layoutMode: 'COUPLET',
                  ),
                }),
              ),
              200,
              headers: {'content-type': 'application/json; charset=utf-8'},
            ),
          ),
        ),
        cache: MemoryCacheStore(),
      );

      await tester.pumpWidget(
        MaterialApp(
          home: PoemReaderScreen(
            poemId: 301,
            contentVersion: 7,
            repository: repository,
            settings: readerSettings,
          ),
        ),
      );
      await tester.pumpAndSettle();

      final text = tester.widget<SelectableText>(
        find.byKey(const Key('poem-body')),
      );
      expect(text.data, longBody);
      expect(text.data, contains('\n\nنوی بند'));
      expect(find.byKey(const Key('poem-body')), findsOneWidget);
      expect(text.textDirection, TextDirection.rtl);
      expect(text.style!.fontSize, 38);
      expect(find.byKey(const Key('poem-scroll-view')), findsOneWidget);
      expect(find.text(poemTitle), findsOneWidget);

      final appBar = tester.widget<AppBar>(
        find.byKey(const Key('reader-app-bar')),
      );
      expect(appBar.title, isNull);
      expect(appBar.backgroundColor!.a, 1);
      expect(appBar.scrolledUnderElevation, 0);

      final toolbarBottom = tester.getBottomLeft(
        find.byKey(const Key('reader-app-bar')),
      );
      final readerTop = tester.getTopLeft(
        find.byKey(const Key('reader-body-clip')),
      );
      expect(readerTop.dy, greaterThanOrEqualTo(toolbarBottom.dy));

      await tester.drag(
        find.byKey(const Key('poem-scroll-view')),
        const Offset(0, -900),
      );
      await tester.pumpAndSettle();
      expect(
        tester.getTopLeft(find.byKey(const Key('reader-body-clip'))).dy,
        greaterThanOrEqualTo(toolbarBottom.dy),
      );
      expect(tester.takeException(), isNull);
    },
  );

  testWidgets(
    'small Android reader defaults to 16 and exposes 336px body width',
    (tester) async {
      tester.view.physicalSize = const Size(360, 640);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await tester.pumpWidget(
        MaterialApp(
          home: PoemReaderScreen(
            poemId: 301,
            contentVersion: 7,
            repository: fixtureRepository(),
            settings: await settings(),
          ),
        ),
      );
      await tester.pumpAndSettle();

      final body = tester.widget<SelectableText>(
        find.byKey(const Key('poem-body')),
      );
      expect(body.style!.fontSize, 16);
      expect(tester.getSize(find.byKey(const Key('poem-body'))).width, 336);
      expect(tester.takeException(), isNull);
    },
  );

  testWidgets(
    'poem list distinguishes untitled identity and translated attribution',
    (tester) async {
      await tester.pumpWidget(
        MaterialApp(
          home: CollectionDetailScreen(
            slug: 'hendaray-aw-chine',
            contentVersion: 7,
            repository: fixtureRepository(),
            readerSettings: await settings(),
          ),
        ),
      );
      await tester.pumpAndSettle();

      expect(find.text('د لومړۍ کرښې پېژندنه'), findsOneWidget);
      expect(find.text('بې سرليکه'), findsNothing);
      expect(find.text('ژمى'), findsOneWidget);
      expect(find.textContaining('اصلي شاعر: پروین پژواک'), findsOneWidget);
      expect(find.textContaining('پښتو ژباړه: اجمل اند'), findsOneWidget);

      final readerSettings = tester
          .widget<CollectionDetailScreen>(find.byType(CollectionDetailScreen))
          .readerSettings;
      expect(
        tester
            .widget<Text>(find.byKey(const Key('poem-list-title-301')))
            .style!
            .fontFamily,
        'Vazirmatn',
      );
      await tester.tap(find.byKey(const Key('collection-font-chooser')));
      await tester.pumpAndSettle();
      expect(find.byType(RadioListTile<ReaderFont>), findsExactly(3));
      await tester.tap(find.byKey(const Key('font-choice-noto-nastaliq')));
      await tester.pumpAndSettle();
      expect(readerSettings.font, ReaderFont.literary);
      expect(
        tester
            .widget<Text>(find.byKey(const Key('poem-list-title-301')))
            .style!
            .fontFamily,
        'NotoNastaliqUrdu',
      );
    },
  );

  testWidgets('reader controls change font, size, and dark palette', (
    tester,
  ) async {
    final readerSettings = await settings();
    await tester.pumpWidget(
      MaterialApp(
        home: PoemReaderScreen(
          poemId: 301,
          contentVersion: 7,
          repository: fixtureRepository(),
          settings: readerSettings,
        ),
      ),
    );
    await tester.pumpAndSettle();
    expect(find.text('شریکول'), findsOneWidget);
    expect(find.text('لیکبڼه'), findsOneWidget);
    await tester.tap(find.byKey(const Key('reader-font-chooser')));
    await tester.pumpAndSettle();

    expect(find.byType(RadioListTile<ReaderFont>), findsExactly(3));
    await tester.tap(find.byKey(const Key('font-choice-scheherazade')));
    await tester.pumpAndSettle();
    await tester.tap(find.text('تياره'));
    await tester.pumpAndSettle();
    await tester.drag(
      find.byKey(const Key('font-size-slider')),
      const Offset(80, 0),
    );
    await tester.pumpAndSettle();

    expect(readerSettings.font, ReaderFont.naskh);
    expect(readerSettings.palette, ReaderPalette.dark);
    expect(readerSettings.fontSize, greaterThan(24));
  });

  testWidgets(
    'untitled reader uses an indicator and never promotes its first line',
    (tester) async {
      await tester.pumpWidget(
        MaterialApp(
          home: PoemReaderScreen(
            poemId: 301,
            contentVersion: 7,
            repository: fixtureRepository(),
            settings: await settings(),
          ),
        ),
      );
      await tester.pumpAndSettle();

      expect(find.byKey(const Key('untitled-poem-indicator')), findsOneWidget);
      final marker = tester.widget<CustomPaint>(
        find.byKey(const Key('untitled-poem-indicator')),
      );
      expect(marker.painter, isA<ManuscriptPageQuillPainter>());
      expect(
        find.descendant(
          of: find.byKey(const Key('untitled-poem-indicator')),
          matching: find.byType(InkWell),
        ),
        findsNothing,
      );
      expect(find.byKey(const Key('poem-title')), findsNothing);
      expect(find.text('بې سرليکه'), findsNothing);
    },
  );

  testWidgets('literary untitled marker remains subtle in every theme', (
    tester,
  ) async {
    final readerSettings = await settings();
    await tester.pumpWidget(
      MaterialApp(
        home: PoemReaderScreen(
          poemId: 301,
          contentVersion: 7,
          repository: fixtureRepository(),
          settings: readerSettings,
        ),
      ),
    );
    await tester.pumpAndSettle();

    for (final palette in ReaderPalette.values) {
      await readerSettings.setPalette(palette);
      await tester.pump();
      expect(find.byKey(const Key('untitled-poem-indicator')), findsOneWidget);
      expect(
        find.bySemanticsLabel('بې نومه شعر؛ اصلي سرليک نه لري'),
        findsOneWidget,
      );
      expect(find.text('بې سرليکه'), findsNothing);
      expect(tester.takeException(), isNull, reason: palette.name);
    }
  });

  testWidgets('reader font controls title and body and date follows body', (
    tester,
  ) async {
    final readerSettings = await settings();
    final repository = PoetryRepository(
      api: ApiClient(
        client: MockClient(
          (_) async => http.Response.bytes(
            utf8.encode(
              jsonEncode({'data': poemDetailJson(title: 'رښتینی سرليک')}),
            ),
            200,
            headers: {'content-type': 'application/json; charset=utf-8'},
          ),
        ),
      ),
      cache: MemoryCacheStore(),
    );
    await tester.pumpWidget(
      MaterialApp(
        home: PoemReaderScreen(
          poemId: 301,
          contentVersion: 7,
          repository: repository,
          settings: readerSettings,
        ),
      ),
    );
    await tester.pumpAndSettle();
    await readerSettings.setFont(ReaderFont.naskh);
    await tester.pump();

    final title = tester.widget<Text>(find.byKey(const Key('poem-title')));
    final body = tester.widget<SelectableText>(
      find.byKey(const Key('poem-body')),
    );
    final date = tester.widget<Text>(find.byKey(const Key('poem-date-place')));
    expect(title.style!.fontFamily, 'ScheherazadeNew');
    expect(body.style!.fontFamily, 'ScheherazadeNew');
    expect(date.style!.fontSize, lessThan(body.style!.fontSize!));
    expect(
      tester.getTopLeft(find.byKey(const Key('poem-title'))).dy,
      lessThan(tester.getTopLeft(find.byKey(const Key('poem-body'))).dy),
    );
    expect(
      tester.getTopLeft(find.byKey(const Key('poem-body'))).dy,
      lessThan(tester.getTopLeft(find.byKey(const Key('poem-date-place'))).dy),
    );
  });

  testWidgets('missing date and place creates no placeholder', (tester) async {
    final repository = PoetryRepository(
      api: ApiClient(
        client: MockClient(
          (_) async => http.Response.bytes(
            utf8.encode(
              jsonEncode({'data': poemDetailJson(sourceDatePlace: null)}),
            ),
            200,
            headers: {'content-type': 'application/json; charset=utf-8'},
          ),
        ),
      ),
      cache: MemoryCacheStore(),
    );
    await tester.pumpWidget(
      MaterialApp(
        home: PoemReaderScreen(
          poemId: 301,
          contentVersion: 7,
          repository: repository,
          settings: await settings(),
        ),
      ),
    );
    await tester.pumpAndSettle();
    expect(find.byKey(const Key('poem-date-place')), findsNothing);
    expect(find.text('—'), findsNothing);
  });

  testWidgets(
    'locked reader shows excerpt with purchase and restore controls',
    (tester) async {
      final entitlements = EntitlementController(_ReaderPurchaseProvider());
      await entitlements.initialize();
      final repository = PoetryRepository(
        api: ApiClient(
          client: MockClient(
            (_) async => http.Response.bytes(
              utf8.encode(jsonEncode({'data': poemDetailJson(locked: true)})),
              200,
              headers: {'content-type': 'application/json; charset=utf-8'},
            ),
          ),
        ),
        cache: MemoryCacheStore(),
      );
      await tester.pumpWidget(
        MaterialApp(
          home: PoemReaderScreen(
            poemId: 301,
            contentVersion: 7,
            repository: repository,
            settings: await settings(),
            entitlements: entitlements,
          ),
        ),
      );
      await tester.pumpAndSettle();

      expect(find.text('دا شعر تړلی دی'), findsOneWidget);
      expect(find.textContaining('لنډه برخه'), findsOneWidget);
      expect(find.text('TEST PRICE'), findsOneWidget);
      expect(find.byKey(const Key('unlock-all')), findsOneWidget);
      expect(find.byKey(const Key('restore-purchases')), findsOneWidget);
      expect(find.textContaining('ټ ډ ړ ږ ښ ڼ ې ۍ'), findsNothing);
    },
  );
}

class _ReaderPurchaseProvider implements PurchaseProvider {
  @override
  Future<String?> appUserId() async => r'$RCAnonymousID:reader-test';
  @override
  Future<void> configure() async {}
  @override
  Future<PurchaseSnapshot> customerInfo() async =>
      const PurchaseSnapshot(entitled: false);
  @override
  Future<PurchaseProduct?> product() async => const PurchaseProduct(
    identifier: revenueCatProductId,
    price: 'TEST PRICE',
  );
  @override
  Future<PurchaseSnapshot> purchase() async =>
      const PurchaseSnapshot(entitled: false);
  @override
  Future<PurchaseSnapshot> restore() async =>
      const PurchaseSnapshot(entitled: false);
}
