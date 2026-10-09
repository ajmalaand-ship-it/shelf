import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/settings/interface_language.dart';
import 'package:shelf/settings/reader_palette_colors.dart';
import 'package:shelf/settings/reader_settings.dart';
import 'package:shelf/settings/reading_preferences_sheet.dart';
import 'package:shelf/theme/app_theme.dart';

void main() {
  setUpAll(() async {
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
    for (final size in [
      const Size(320, 568),
      const Size(390, 844),
      const Size(568, 240),
    ]) {
      for (final scale in [1.0, 2.0]) {
        testWidgets('pinned preview ${language.name} $size scale $scale', (
          tester,
        ) async {
          tester.view.physicalSize = size;
          tester.view.devicePixelRatio = 1;
          tester.platformDispatcher.textScaleFactorTestValue = scale;
          addTearDown(tester.view.resetPhysicalSize);
          addTearDown(tester.view.resetDevicePixelRatio);
          addTearDown(tester.platformDispatcher.clearTextScaleFactorTestValue);
          SharedPreferences.setMockInitialValues({
            InterfaceLanguageSettings.preferenceKey: language.name,
            'reader.font_size': 38.0,
          });
          final prefs = await SharedPreferences.getInstance();
          final settings = await ReaderSettings.load(prefs);
          final interface = InterfaceLanguageSettings.load(prefs);
          final direction = language == InterfaceLanguage.en
              ? TextDirection.ltr
              : TextDirection.rtl;
          await tester.pumpWidget(
            MaterialApp(
              theme: AppTheme.light,
              builder: (context, child) => InterfaceLanguageScope(
                settings: interface,
                child: Directionality(textDirection: direction, child: child!),
              ),
              home: Scaffold(
                body: Builder(
                  builder: (context) => Center(
                    child: TextButton(
                      onPressed: () =>
                          showReadingPreferences(context, settings),
                      child: const Text('Open preferences'),
                    ),
                  ),
                ),
              ),
            ),
          );
          await tester.tap(find.text('Open preferences'));
          await tester.pumpAndSettle();
          final preview = find.byKey(const Key('reading-live-preview'));
          final previewText = find.byKey(
            const Key('reading-live-preview-text'),
          );
          final close = find.byKey(const Key('reading-preferences-close'));
          final controls = find.byKey(const Key('reading-preferences-scroll'));
          final previewTop = tester.getTopLeft(preview);
          final closeTop = tester.getTopLeft(close);
          final scrollState = tester.state<ScrollableState>(
            find
                .descendant(of: controls, matching: find.byType(Scrollable))
                .first,
          );
          final beforeScroll = scrollState.position.pixels;
          await tester.drag(controls, const Offset(0, -160));
          await tester.pumpAndSettle();
          if (scrollState.position.maxScrollExtent > 0) {
            expect(scrollState.position.pixels, greaterThan(beforeScroll));
          } else {
            expect(scrollState.position.pixels, beforeScroll);
          }
          expect(tester.getTopLeft(preview), previewTop);
          expect(tester.getTopLeft(close), closeTop);
          expect(tester.getSize(controls).height, greaterThanOrEqualTo(48));
          expect(
            tester.getTopLeft(controls).dy,
            greaterThan(tester.getBottomLeft(preview).dy),
          );
          expect(Directionality.of(tester.element(previewText)), direction);

          void expectLivePreview() {
            final text = tester.widget<Text>(previewText);
            final colors = ReaderPaletteColors.forPalette(settings.palette);
            expect(text.style!.fontSize, settings.fontSize);
            expect(text.style!.fontFamily, settings.fontFamily);
            expect(text.style!.color, colors.foreground);
            expect(
              MediaQuery.textScalerOf(tester.element(previewText))
                  .scale(settings.fontSize),
              settings.fontSize * scale,
            );
            expect(
              (tester.widget<Container>(preview).decoration! as BoxDecoration)
                  .color,
              colors.background,
            );
            expect(tester.getSize(close).height, greaterThanOrEqualTo(48));
            expect(
              tester.getBottomLeft(close).dy,
              lessThan(tester.getTopLeft(controls).dy),
            );
            expect(tester.takeException(), isNull);
          }

          expectLivePreview();

          for (final font in ReaderFont.values) {
            final key = switch (font) {
              ReaderFont.vazirmatn => 'font-choice-vazirmatn',
              ReaderFont.naskh => 'font-choice-scheherazade',
              ReaderFont.literary => 'font-choice-noto-nastaliq',
            };
            final radio = find.descendant(
              of: find.byKey(Key(key)),
              matching: find.byType(Radio<ReaderFont>),
            );
            await tester.ensureVisible(radio);
            await tester.tap(radio);
            await tester.pump();
            expect(settings.font, font);
            expectLivePreview();
            await tester.pumpAndSettle();
          }
          for (final palette in ReaderPalette.values) {
            final label = find.descendant(
              of: find.byKey(Key('theme-${palette.name}')),
              matching: find.text('Aa'),
            );
            await tester.ensureVisible(label);
            await tester.tap(label);
            await tester.pump();
            expect(settings.palette, palette);
            expectLivePreview();
            await tester.pumpAndSettle();
          }
          final decrease = find.byKey(const Key('font-size-decrease'));
          await tester.ensureVisible(decrease);
          await tester.tap(decrease);
          await tester.pump();
          expect(settings.fontSize, 36);
          expectLivePreview();
          await tester.pumpAndSettle();
          final increase = find.byKey(const Key('font-size-increase'));
          await tester.ensureVisible(increase);
          await tester.tap(increase);
          await tester.pump();
          expect(settings.fontSize, 38);
          expectLivePreview();
          await tester.pumpAndSettle();
          final slider = find.byKey(const Key('font-size-slider'));
          await tester.ensureVisible(slider);
          await tester.drag(
            slider,
            Offset(direction == TextDirection.ltr ? -1000 : 1000, 0),
          );
          await tester.pumpAndSettle();
          expect(settings.fontSize, 18);
          expectLivePreview();
          final restored = await ReaderSettings.load(prefs);
          expect(restored.font, ReaderFont.literary);
          expect(restored.palette, ReaderPalette.dark);
          expect(restored.fontSize, 18);
          // Close stays reachable even after scrolling to the final controls.
          await tester.ensureVisible(find.byKey(const Key('theme-dark')));
          await tester.pumpAndSettle();
          await tester.tap(close);
          await tester.pumpAndSettle();
          expect(find.byType(ReadingPreferences), findsNothing);
          expect(tester.takeException(), isNull);
        });
      }
    }
  }
}
