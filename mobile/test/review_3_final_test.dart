import 'package:shelf/theme/app_theme.dart';
import 'package:shelf/screens/collection_detail_screen.dart';
import 'package:shelf/screens/poem_reader_screen.dart';
import 'package:shelf/settings/reader_settings.dart';

import 'dart:ui' as ui;
import 'dart:io';

import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:shelf/share_cards/share_card_models.dart';
import 'package:shelf/share_cards/share_card_renderer.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/l10n/app_strings.dart';
import 'package:shelf/models/poem.dart';
import 'package:shelf/settings/interface_language.dart';
import 'package:shelf/share_cards/poem_card_widget.dart';
import 'package:shelf/share_cards/share_card_screen.dart';
import 'package:shelf/widgets/shelf_assets.dart';

import 'test_support.dart';

void main() {
  setUpAll(() async {
    final icons = File('/home/shelf/flutter/bin/cache/artifacts/material_fonts/MaterialIcons-Regular.otf');
    if (await icons.exists()) await (FontLoader('MaterialIcons')..addFont(Future.value(ByteData.sublistView(await icons.readAsBytes())))).load();

    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger.setMockMethodCallHandler(
      const MethodChannel('plugins.flutter.io/path_provider'),
      (call) async => Directory.systemTemp.path,
    );
    await (FontLoader('Vazirmatn')..addFont(
          rootBundle.load('assets/fonts/vazirmatn/Vazirmatn-wght.ttf'),
        ))
        .load();
  });
  testWidgets(
    'exported cards render supplied logos and both localized page counters',
    (t) async {
      for (final theme in ShareCardTheme.values) {
        final poem = PoemDetail.fromJson(
          poemDetailJson(
            title: 'Synthetic title',
            body: 'Synthetic original line',
          ),
        );
        final request = ShareCardRequest(
          poem: poem,
          collectionTitle: 'Synthetic book',
          scope: ShareCardScope.selection,
          text: poem.readableText,
          theme: theme,
          includeTitle: true,
        );
        await t.pumpWidget(
          MaterialApp(
            builder: (context, child) => Material(child: child),
            home: Center(
              child: RepaintBoundary(
                key: const Key('export-card'),
                child: PoemCardWidget(
                  request: request,
                  page: ShareCardPage(
                    text: request.text,
                    pageNumber: 2,
                    pageCount: 12,
                  ),
                ),
              ),
            ),
          ),
        );
        await t.pumpAndSettle();
        expect(find.text('۲/۱۲'), findsOneWidget);
        expect(find.text('Synthetic title'), findsOneWidget);
        final image = t.widget<Image>(
          find.descendant(
            of: find.byType(ShelfLogo),
            matching: find.byType(Image),
          ),
        );
        expect(
          (image.image as AssetImage).assetName,
          contains(
            theme == ShareCardTheme.dark
                ? 'shelf_header_light'
                : 'shelf_header_dark',
          ),
        );
        await t.runAsync(() => precacheImage(image.image, t.element(find.byType(PoemCardWidget))));
        await t.pumpAndSettle();
        final dir = Platform.environment['SHELF_REVIEW3_RENDER_DIR'];
        if (dir != null) {
          final bytes = await t.runAsync(
            () => const ShareCardRenderer().capturePaintedBoundary(
              t.renderObject<RenderRepaintBoundary>(
                find.byKey(const Key('export-card')),
              ),
            ),
          );
          await t.runAsync(
            () => File('$dir/export-${theme.name}.png').writeAsBytes(bytes!),
          );
        }
        expect(t.takeException(), isNull);
      }
    },
  );
  test(
    'locked first-line metadata never becomes readable text or a stored title',
    () {
      final json = {
        ...poemSummaryJson,
        'title': null,
        'first_line': '  First line  ',
        'excerpt': null,
        'locked': true,
        'sample_mode': 'none',
      };
      final summary = PoemSummary.fromJson(json);
      expect(summary.displayTitle, 'First line');
      expect(summary.title, isNull);
      expect(summary.excerpt, isNull);
      expect(summary.toJson()['first_line'], '  First line  ');
      final detail = PoemDetail.fromJson(
        poemDetailJson(locked: true, body: null),
      );
      expect(detail.readableText, isEmpty);
    },
  );
  for (final lang in InterfaceLanguage.values) {
    testWidgets('Contents and untitled reader renders ${lang.name}', (t) async {
      t.view.physicalSize = const Size(390, 844);
      t.view.devicePixelRatio = 1;
      addTearDown(t.view.resetPhysicalSize);
      addTearDown(t.view.resetDevicePixelRatio);
      SharedPreferences.setMockInitialValues({
        'shelf.interface_language': lang.name,
      });
      final prefs = await SharedPreferences.getInstance();
      final languages = InterfaceLanguageSettings.load(prefs);
      final settings = await ReaderSettings.load(prefs);
      for (final reader in [false, true]) {
        await t.pumpWidget(
          InterfaceLanguageScope(
            settings: languages,
            child: MaterialApp(
              theme: AppTheme.light,
              builder: (context, child) => Directionality(textDirection: lang == InterfaceLanguage.en ? TextDirection.ltr : TextDirection.rtl, child: child!),
              home: RepaintBoundary(
                key: const Key('screen-render'),
                child: reader
                    ? PoemReaderScreen(
                        poemId: 301,
                        contentVersion: 7,
                        repository: fixtureRepository(includeCover: false),
                        settings: settings,
                      )
                    : CollectionDetailScreen(
                        slug: 'hendaray-aw-chine',
                        contentVersion: 7,
                        repository: fixtureRepository(includeCover: false),
                        readerSettings: settings,
                      ),
              ),
            ),
          ),
        );
        await t.pumpAndSettle();
        if (!reader)
          await t.scrollUntilVisible(
            find.byKey(const Key('poem-list-title-301')),
            200,
          );
        for (final element in find.byType(Image).evaluate()) {
          final image = element.widget as Image;
          if (image.image is AssetImage) await t.runAsync(() => precacheImage(image.image, element));
        }
        await t.pumpAndSettle();
        if (reader) {
          expect(
            find.byKey(const Key('untitled-poem-indicator')),
            findsOneWidget,
          );
          expect(find.text('Sample — نمونه'), findsNothing);
          expect(find.text('Font'), findsNothing);
          expect(
            t.widget<IconButton>(find.byKey(const Key('reader-share'))).icon,
            isA<Icon>().having(
              (i) => i.icon,
              'previous share glyph',
              Icons.ios_share_rounded,
            ),
          );
        }
        final dir = Platform.environment['SHELF_REVIEW3_RENDER_DIR'];
        if (dir != null)
          await t.runAsync(() async {
            final image = await t
                .renderObject<RenderRepaintBoundary>(
                  find.byKey(const Key('screen-render')),
                )
                .toImage();
            final bytes = await image.toByteData(
              format: ui.ImageByteFormat.png,
            );
            await File(
              '$dir/${lang.name}-${reader ? 'reader' : 'contents'}.png',
            ).writeAsBytes(bytes!.buffer.asUint8List());
            image.dispose();
          });
        expect(t.takeException(), isNull);
      }
    });

    testWidgets(
      'independent title, localized counts and icon-only font ${lang.name}',
      (t) async {
        SharedPreferences.setMockInitialValues({
          'shelf.interface_language': lang.name,
        });
        final settings = InterfaceLanguageSettings.load(
          await SharedPreferences.getInstance(),
        );
        final poem = PoemDetail.fromJson(
          poemDetailJson(
            title: 'Synthetic title',
            body: 'First line\nSecond line',
          ),
        );
        await t.pumpWidget(
          InterfaceLanguageScope(
            settings: settings,
            child: MaterialApp(home: ShareCardScreen(poem: poem)),
          ),
        );
        await t.pumpAndSettle();
        var card = t.widget<PoemCardWidget>(find.byType(PoemCardWidget));
        expect(card.request.includeTitle, isFalse);
        expect(
          find.text(lang == InterfaceLanguage.en ? '1/4' : '۱/۴'),
          findsOneWidget,
        );
        expect(
          AppStrings.of(t.element(find.byType(ShareCardScreen))).sample,
          switch (lang) {
            InterfaceLanguage.ps => 'بېلګه',
            InterfaceLanguage.dari => 'نمونه',
            InterfaceLanguage.en => 'Sample',
          },
        );
        await t.tap(find.byKey(const Key('share-include-title')));
        await t.pumpAndSettle();
        card = t.widget<PoemCardWidget>(find.byType(PoemCardWidget));
        expect(card.request.includeTitle, isTrue);
        expect(card.request.text, 'First line');
        expect(find.text('Synthetic title'), findsNWidgets(2));
        await t.pumpWidget(
          InterfaceLanguageScope(
            settings: settings,
            child: MaterialApp(
              home: Scaffold(
                appBar: AppBar(actions: [ShelfFontButton(onPressed: () {})]),
              ),
            ),
          ),
        );
        await t.pumpAndSettle();
        expect(find.byType(Text), findsNothing);
        expect(find.byType(ShelfActionIcon), findsOneWidget);
        expect(
          t.widget<IconButton>(find.byType(IconButton)).tooltip,
          BookstoreStrings(
            lang == InterfaceLanguage.en,
            isDari: lang == InterfaceLanguage.dari,
          ).readingPreferences,
        );
      },
    );
  }
}
