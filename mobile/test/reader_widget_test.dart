import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:pitswal/app.dart';
import 'package:pitswal/repository/poetry_repository.dart';
import 'package:pitswal/screens/collection_detail_screen.dart';
import 'package:pitswal/screens/poem_reader_screen.dart';
import 'package:pitswal/services/api_client.dart';
import 'package:pitswal/settings/reader_settings.dart';
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
    final hasRtlRoot = tester
        .widgetList<Directionality>(find.byType(Directionality))
        .any((widget) => widget.textDirection == TextDirection.rtl);
    expect(hasRtlRoot, isTrue);
  });

  testWidgets(
    'reader preserves free verse, stanza breaks, and long scrolling',
    (tester) async {
      tester.view.physicalSize = const Size(360, 640);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);
      final longBody = List.generate(
        50,
        (index) => index == 2 ? '\nنوی بند' : 'ازاده کرښه ${index + 1}',
      ).join('\n');
      final repository = PoetryRepository(
        api: ApiClient(
          client: MockClient(
            (_) async => http.Response.bytes(
              utf8.encode(jsonEncode({'data': poemDetailJson(body: longBody)})),
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
            initialTitle: 'ازاده شاعري',
            contentVersion: 7,
            repository: repository,
            settings: await settings(),
          ),
        ),
      );
      await tester.pumpAndSettle();

      final text = tester.widget<SelectableText>(
        find.byKey(const Key('poem-body')),
      );
      expect(text.data, longBody);
      expect(text.data, contains('\n\nنوی بند'));
      expect(text.textDirection, TextDirection.rtl);
      expect(find.byKey(const Key('poem-scroll-view')), findsOneWidget);
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
      expect(find.text('بې سرليکه'), findsOneWidget);
      expect(find.text('ژمى'), findsOneWidget);
      expect(find.textContaining('اصلي شاعر: پروین پژواک'), findsOneWidget);
      expect(find.textContaining('پښتو ژباړه: اجمل اند'), findsOneWidget);
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
          initialTitle: 'شعر',
          contentVersion: 7,
          repository: fixtureRepository(),
          settings: readerSettings,
        ),
      ),
    );
    await tester.pumpAndSettle();
    await tester.tap(find.byTooltip('د لوست بڼه'));
    await tester.pumpAndSettle();

    await tester.tap(find.text('نسخ'));
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

  testWidgets('locked reader shows excerpt without a purchase control', (
    tester,
  ) async {
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
          initialTitle: 'تړلی شعر',
          contentVersion: 7,
          repository: repository,
          settings: await settings(),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('دا شعر تړلی دی'), findsOneWidget);
    expect(find.textContaining('لنډه برخه'), findsOneWidget);
    expect(find.textContaining('پېرود'), findsNothing);
  });
}
