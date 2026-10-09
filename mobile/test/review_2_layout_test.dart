import 'dart:io';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/app.dart';
import 'package:shelf/settings/interface_language.dart';
import 'package:shelf/settings/reader_settings.dart';

import 'test_support.dart';

void main() {
  setUpAll(() async {
    for (final font in {
      'MaterialIcons': '/home/shelf/flutter/bin/cache/artifacts/material_fonts/MaterialIcons-Regular.otf',
      'Roboto': '/home/shelf/flutter/bin/cache/artifacts/material_fonts/Roboto-Regular.ttf',
      'Ahem': '/home/shelf/flutter/bin/cache/artifacts/material_fonts/Roboto-Regular.ttf',
    }.entries) {
      await (FontLoader(font.key)..addFont(
            Future.value(
              ByteData.sublistView(await File(font.value).readAsBytes()),
            ),
          ))
          .load();
    }
    for (final font in {
      'Vazirmatn': 'assets/fonts/vazirmatn/Vazirmatn-wght.ttf',
      'ScheherazadeNew':
          'assets/fonts/scheherazade_new/ScheherazadeNew-Regular.ttf',
      'NotoNastaliqUrdu':
          'assets/fonts/noto_nastaliq_urdu/NotoNastaliqUrdu.ttf',
    }.entries) {
      await (FontLoader(font.key)..addFont(rootBundle.load(font.value))).load();
    }
  });
  for (final language in InterfaceLanguage.values) {
    for (final width in [320.0, 390.0]) {
      for (final scale in [1.0, 1.8]) {
        testWidgets('Review 2 ${language.name} $width at text scale $scale', (
          tester,
        ) async {
          tester.view.physicalSize = Size(width, width == 320 ? 568 : 844);
          tester.view.devicePixelRatio = 1;
          tester.platformDispatcher.textScaleFactorTestValue = scale;
          addTearDown(tester.platformDispatcher.clearTextScaleFactorTestValue);
          addTearDown(tester.view.resetPhysicalSize);
          addTearDown(tester.view.resetDevicePixelRatio);
          SharedPreferences.setMockInitialValues({
            InterfaceLanguageSettings.preferenceKey: language.name,
          });
          final prefs = await SharedPreferences.getInstance();
          final settings = await ReaderSettings.load(prefs);
          final boundary = GlobalKey();
          await tester.pumpWidget(
            RepaintBoundary(
              key: boundary,
              child: MediaQuery(
                data: MediaQueryData(textScaler: TextScaler.linear(scale)),
                child: ShelfApp(
                  repository: fixtureRepository(includeCover: false),
                  readerSettings: settings,
                  languageSettings: InterfaceLanguageSettings.load(prefs),
                ),
              ),
            ),
          );
          await tester.pumpAndSettle();
          Future<void> capture(String screen) async {
            expect(tester.takeException(), isNull);
            final dir = Platform.environment['SHELF_REVIEW_SCREENSHOTS'];
            if (dir != null) {
              await tester.runAsync(() async {
                final render =
                    boundary.currentContext!.findRenderObject()!
                        as RenderRepaintBoundary;
                final image = await render.toImage();
                final bytes = await image.toByteData(
                  format: ui.ImageByteFormat.png,
                );
                await Directory(dir).create(recursive: true);
                await File(
                  '$dir/${language.name}-${width.toInt()}-$scale-$screen.png',
                ).writeAsBytes(bytes!.buffer.asUint8List());
                image.dispose();
              });
            }
          }

          expect(
            Directionality.of(tester.element(find.byType(NavigationBar))),
            language == InterfaceLanguage.en
                ? TextDirection.ltr
                : TextDirection.rtl,
          );
          await capture('store');
          await tester.tap(find.byKey(const Key('store-search-entry')));
          await tester.pumpAndSettle();
          await tester.enterText(
            find.byKey(const Key('book-search')),
            'هېندارې',
          );
          await tester.pump(const Duration(milliseconds: 350));
          await tester.pumpAndSettle();
          await capture('search');
          await tester.tap(find.byType(NavigationDestination).at(0));
          await tester.pumpAndSettle();
          final title = find.byKey(
            const Key('collection-title-hendaray-aw-chine'),
          );
          await tester.scrollUntilVisible(
            title,
            180,
            scrollable: find.byType(Scrollable).first,
          );
          await Scrollable.ensureVisible(tester.element(title), alignment: .3);
          await tester.pumpAndSettle();
          await tester.tap(title);
          await tester.pumpAndSettle();
          await capture('details');
          final action = find.byKey(const Key('read-sample'));
          expect(tester.getSize(action).height, greaterThanOrEqualTo(48));
          await tester.tap(action);
          await tester.pumpAndSettle();
          await capture('reader');
          await tester.tap(find.byKey(const Key('reader-font-chooser')));
          await tester.pumpAndSettle();
          await capture('preferences');
          for (final palette in ReaderPalette.values) {
            final swatch = find.byKey(Key('theme-${palette.name}'));
            await tester.ensureVisible(swatch);
            await tester.tap(swatch);
            await tester.pumpAndSettle();
            expect(settings.palette, palette);
          }
          final restored = await ReaderSettings.load(prefs);
          expect(restored.palette, ReaderPalette.dark);
          await tester.ensureVisible(
            find.byKey(const Key('reading-preferences-close')),
          );
          await tester.tap(find.byKey(const Key('reading-preferences-close')));
          await tester.pumpAndSettle();
          await capture('reader-dark');
        });
      }
    }
  }
}
