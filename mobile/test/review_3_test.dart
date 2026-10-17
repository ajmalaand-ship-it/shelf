import 'dart:io';
import 'dart:ui' as ui;

import 'package:flutter/rendering.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/app.dart';
import 'package:shelf/l10n/app_strings.dart';
import 'package:shelf/models/poem.dart';
import 'package:shelf/settings/interface_language.dart';
import 'package:shelf/settings/reader_settings.dart';
import 'package:shelf/share_cards/poem_card_widget.dart';
import 'package:shelf/share_cards/share_card_models.dart';
import 'package:shelf/share_cards/share_card_screen.dart';
import 'package:shelf/widgets/shelf_assets.dart';
import 'package:shelf/theme/app_theme.dart';

import 'test_support.dart';

Future<void> capture(WidgetTester t, String name) async {
  final path = Platform.environment['SHELF_REVIEW3_RENDER_DIR'];
  if (path == null) return;
  await t.runAsync(() async {
    final boundary = t.renderObject<RenderRepaintBoundary>(
      find.byKey(const Key('review3-render')),
    );
    final image = await boundary.toImage(pixelRatio: 1);
    final bytes = await image.toByteData(format: ui.ImageByteFormat.png);
    await Directory(path).create(recursive: true);
    await File('$path/$name.png').writeAsBytes(bytes!.buffer.asUint8List());
    image.dispose();
  });
}

void main() {
  setUpAll(() async {
    for (final entry in {
      'Vazirmatn': 'assets/fonts/vazirmatn/Vazirmatn-wght.ttf',
      'ScheherazadeNew':
          'assets/fonts/scheherazade_new/ScheherazadeNew-Regular.ttf',
      'NotoNastaliqUrdu':
          'assets/fonts/noto_nastaliq_urdu/NotoNastaliqUrdu.ttf',
    }.entries) {
      await (FontLoader(
        entry.key,
      )..addFont(rootBundle.load(entry.value))).load();
    }
  });
  test('font names and required settings text use one interface language', () {
    const ps = BookstoreStrings(false),
        en = BookstoreStrings(true),
        dari = BookstoreStrings(false, isDari: true);
    expect(ps.scheherazadeName, 'نوی شهرزاد');
    expect(ps.settings, 'سیټینګ');
    expect(dari.scheherazadeName, 'شهرزاد نو');
    expect(dari.library, 'کتابخانهٔ من');
    expect(en.scheherazadeName, 'Scheherazade New');
    for (final name in [
      ps.vazirmatnName,
      ps.nastaliqName,
      dari.vazirmatnName,
      dari.nastaliqName,
    ]) {
      expect(RegExp('[A-Za-z]').hasMatch(name), isFalse);
    }
  });
  for (final dark in [false, true]) {
    for (final language in InterfaceLanguage.values) {
      testWidgets(
        'logo, navigation and complete language names ${language.name} dark=$dark',
        (t) async {
          SharedPreferences.setMockInitialValues({
            'shelf.interface_language': language.name,
          });
          final prefs = await SharedPreferences.getInstance();
          final settings = await ReaderSettings.load(prefs);
          await t.pumpWidget(
            ShelfApp(
              repository: fixtureRepository(),
              readerSettings: settings,
              languageSettings: InterfaceLanguageSettings.load(prefs),
            ),
          );
          await t.pumpAndSettle();
          expect(find.byType(ShelfLogo), findsOneWidget);
          final header = t.widget<Image>(
            find.descendant(
              of: find.byType(ShelfLogo),
              matching: find.byType(Image),
            ),
          );
          expect(header.fit, BoxFit.contain);
          expect(
            (header.image as AssetImage).assetName,
            contains('shelf_header_dark.png'),
          );
          await t.tap(find.byKey(const Key('store-language-toggle')));
          await t.pumpAndSettle();
          expect(find.text('English'), findsWidgets);
          expect(find.text('دری'), findsWidgets);
          expect(find.text('پښتو'), findsWidgets);
          await t.tap(find.text('دری').last);
          await t.pumpAndSettle();
          expect(find.text('جستجو'), findsWidgets);
          expect(t.takeException(), isNull);
        },
      );
    }
    testWidgets(
      'supplied assets match light/dark background and inactive nav $dark',
      (t) async {
        await t.pumpWidget(
          MaterialApp(
            theme: ThemeData(
              brightness: dark ? Brightness.dark : Brightness.light,
            ),
            home: const Scaffold(
              body: Column(
                children: [
                  ShelfLogo(),
                  ShelfActionIcon('store'),
                  ShelfActionIcon('store', inactive: true),
                ],
              ),
            ),
          ),
        );
        await t.pumpAndSettle();
        final assets = t
            .widgetList<Image>(find.byType(Image))
            .map((i) => (i.image as AssetImage).assetName)
            .toList();
        expect(
          assets[0],
          contains(dark ? 'shelf_header_light' : 'shelf_header_dark'),
        );
        expect(assets[1], contains(dark ? '/dark/' : '/light/'));
        expect(assets[2], contains('/inactive/'));
        expect(t.takeException(), isNull);
      },
    );
  }
  for (final language in InterfaceLanguage.values) {
    for (final scale in [1.0, 2.0, 3.0]) {
      testWidgets('Store full-name controls ${language.name} at $scale', (
        t,
      ) async {
        t.view.physicalSize = const Size(320, 568);
        t.view.devicePixelRatio = 1;
        t.platformDispatcher.textScaleFactorTestValue = scale;
        addTearDown(t.view.resetPhysicalSize);
        addTearDown(t.view.resetDevicePixelRatio);
        addTearDown(t.platformDispatcher.clearTextScaleFactorTestValue);
        SharedPreferences.setMockInitialValues({
          'shelf.interface_language': language.name,
        });
        final prefs = await SharedPreferences.getInstance();
        await t.pumpWidget(
          RepaintBoundary(
            key: const Key('review3-render'),
            child: ShelfApp(
              repository: fixtureRepository(),
              readerSettings: await ReaderSettings.load(prefs),
              languageSettings: InterfaceLanguageSettings.load(prefs),
            ),
          ),
        );
        await t.pumpAndSettle();
        expect(find.byType(ShelfLogo).hitTestable(), findsOneWidget);
        expect(
          find.byKey(const Key('store-language-toggle')).hitTestable(),
          findsOneWidget,
        );
        if (scale == 2) await capture(t, '${language.name}-store-320-2x');
        expect(t.takeException(), isNull);
      });
    }
  }
  for (final direction in TextDirection.values) {
    testWidgets('back glyph follows $direction', (t) async {
      await t.pumpWidget(
        MaterialApp(
          home: Directionality(
            textDirection: direction,
            child: const ShelfActionIcon('back', directional: true),
          ),
        ),
      );
      final transform = t.widget<Transform>(find.byType(Transform).last);
      expect(
        transform.transform.entry(0, 0),
        direction == TextDirection.ltr ? -1 : 1,
      );
    });
  }
  for (final language in InterfaceLanguage.values) {
    for (final size in [const Size(320, 568), const Size(568, 240)]) {
      testWidgets(
        'share preview pinned and live ${language.name} $size at 2x',
        (t) async {
          t.view.physicalSize = size;
          t.view.devicePixelRatio = 1;
          t.platformDispatcher.textScaleFactorTestValue = 2;
          addTearDown(t.view.resetPhysicalSize);
          addTearDown(t.view.resetDevicePixelRatio);
          addTearDown(t.platformDispatcher.clearTextScaleFactorTestValue);
          SharedPreferences.setMockInitialValues({
            'shelf.interface_language': language.name,
          });
          final languageSettings = InterfaceLanguageSettings.load(
            await SharedPreferences.getInstance(),
          );
          final poem = PoemDetail.fromJson(
            poemDetailJson(body: 'لومړۍ کرښه\nدویمه کرښه\nدرېیمه کرښه'),
          );
          await t.pumpWidget(
            MaterialApp(
              theme: AppTheme.light,
              builder: (context, child) => InterfaceLanguageScope(
                settings: languageSettings,
                child: Directionality(
                  textDirection: language == InterfaceLanguage.en
                      ? TextDirection.ltr
                      : TextDirection.rtl,
                  child: child!,
                ),
              ),
              home: RepaintBoundary(
                key: const Key("review3-render"),
                child: ShareCardScreen(poem: poem),
              ),
            ),
          );
          await t.pumpAndSettle();
          final preview = find.byKey(const Key('card-preview'));
          final before = t.getRect(preview);
          await capture(t, '${language.name}-${size.width.round()}-initial');
          await t.scrollUntilVisible(
            find.byKey(const Key('share-line-1')),
            80,
            scrollable: find
                .descendant(
                  of: find.byKey(const Key('share-controls-scroll')),
                  matching: find.byType(Scrollable),
                )
                .first,
            maxScrolls: 100,
          );
          await t.pumpAndSettle();
          await t.tap(find.byKey(const Key('share-line-1')));
          await t.pumpAndSettle();
          expect(
            t
                .widget<PoemCardWidget>(find.byType(PoemCardWidget).first)
                .request
                .text,
            'لومړۍ کرښه\nدویمه کرښه',
          );
          await t.scrollUntilVisible(
            find.byKey(const Key('share-font')),
            80,
            scrollable: find
                .descendant(
                  of: find.byKey(const Key('share-controls-scroll')),
                  matching: find.byType(Scrollable),
                )
                .first,
            maxScrolls: 100,
          );
          await t.pumpAndSettle();
          t
              .widget<DropdownButton<String>>(
                find.byKey(const Key('share-font')),
              )
              .onChanged!('ScheherazadeNew');
          await t.pumpAndSettle();
          await t.scrollUntilVisible(
            find.byKey(const Key('share-font-size')),
            80,
            scrollable: find
                .descendant(
                  of: find.byKey(const Key('share-controls-scroll')),
                  matching: find.byType(Scrollable),
                )
                .first,
            maxScrolls: 100,
          );
          await t.pumpAndSettle();
          t.widget<Slider>(find.byKey(const Key('share-font-size'))).onChanged!(
            28,
          );
          await t.pumpAndSettle();
          await t.scrollUntilVisible(
            find.byKey(const Key('card-theme')),
            80,
            scrollable: find
                .descendant(
                  of: find.byKey(const Key('share-controls-scroll')),
                  matching: find.byType(Scrollable),
                )
                .first,
            maxScrolls: 100,
          );
          await t.pumpAndSettle();
          t
              .widget<SegmentedButton<ShareCardTheme>>(
                find.byKey(const Key('card-theme')),
              )
              .onSelectionChanged!({ShareCardTheme.dark});
          await t.pumpAndSettle();
          final request = t
              .widget<PoemCardWidget>(find.byType(PoemCardWidget).first)
              .request;
          expect(request.fontFamily, 'ScheherazadeNew');
          expect(request.fontSize, 28);
          expect(request.theme, ShareCardTheme.dark);
          await t.scrollUntilVisible(
            find.byKey(const Key('share-cards')),
            80,
            scrollable: find
                .descendant(
                  of: find.byKey(const Key('share-controls-scroll')),
                  matching: find.byType(Scrollable),
                )
                .first,
            maxScrolls: 100,
          );
          await t.pumpAndSettle();
          await t.pumpAndSettle();
          await capture(t, "${language.name}-${size.width.round()}-controls");
          expect(t.getRect(preview), before);
          expect(
            find.byKey(const Key('share-cards')).hitTestable(),
            findsOneWidget,
          );
          expect(t.takeException(), isNull);
        },
      );
    }
  }
}
