import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:pitswal/app.dart';
import 'package:pitswal/settings/reader_settings.dart';
import 'package:pitswal/widgets/poetry_text.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'test_support.dart';

void main() {
  test('Vazirmatn is default and all three font choices persist', () async {
    SharedPreferences.setMockInitialValues({});
    final preferences = await SharedPreferences.getInstance();
    final settings = await ReaderSettings.load(preferences);
    expect(settings.font, ReaderFont.vazirmatn);
    expect(settings.fontFamily, 'Vazirmatn');

    for (final choice in ReaderFont.values) {
      await settings.setFont(choice);
      final restored = await ReaderSettings.load(preferences);
      expect(restored.font, choice);
      expect(restored.fontFamily, isNotEmpty);
    }
  });

  testWidgets('Home exposes the same persisted reading font control', (
    tester,
  ) async {
    SharedPreferences.setMockInitialValues({});
    final settings = await ReaderSettings.load(
      await SharedPreferences.getInstance(),
    );
    await tester.pumpWidget(
      PitswalApp(repository: fixtureRepository(), readerSettings: settings),
    );
    await tester.pumpAndSettle();
    Text collectionTitle() => tester.widget<Text>(
      find.byKey(const Key('collection-title-hendaray-aw-chine')),
    );
    expect(collectionTitle().style!.fontFamily, 'Vazirmatn');
    await tester.tap(find.byKey(const Key('home-font-chooser')));
    await tester.pumpAndSettle();
    expect(find.byKey(const Key('reading-font-selector')), findsOneWidget);
    expect(find.byType(RadioListTile<ReaderFont>), findsExactly(3));
    expect(find.text('وزيرمتن — Vazirmatn'), findsOneWidget);
    expect(find.text('شهرزاد نو — Scheherazade New'), findsOneWidget);
    expect(find.text('نوټو نستعليق — Noto Nastaliq Urdu'), findsOneWidget);
    await tester.tap(find.byKey(const Key('font-choice-scheherazade')));
    await tester.pumpAndSettle();
    expect(settings.font, ReaderFont.naskh);
    expect(collectionTitle().style!.fontFamily, 'ScheherazadeNew');
  });

  test('each reading selection maps to its intended bundled family', () async {
    SharedPreferences.setMockInitialValues({});
    final settings = await ReaderSettings.load(
      await SharedPreferences.getInstance(),
    );
    const expected = {
      ReaderFont.vazirmatn: 'Vazirmatn',
      ReaderFont.naskh: 'ScheherazadeNew',
      ReaderFont.literary: 'NotoNastaliqUrdu',
    };
    for (final entry in expected.entries) {
      await settings.setFont(entry.key);
      expect(settings.fontFamily, entry.value);
    }
  });

  testWidgets('reader preserves source newlines without automatic grouping', (
    tester,
  ) async {
    const source = '۱\n۲\n\n۳\n۴\n۵\n۶';
    await tester.pumpWidget(
      const MaterialApp(
        home: PoetryText(
          text: source,
          style: TextStyle(fontSize: 20, height: 2.2),
        ),
      ),
    );

    expect(tester.takeException(), isNull);
    expect(find.byKey(const Key('poem-body')), findsOneWidget);
    expect(find.text(source), findsOneWidget);
    expect(find.byType(SizedBox), findsNothing);
  });

  testWidgets(
    'representative final heh and Pashto letters render unclipped in three fonts',
    (tester) async {
      const sample = 'خانه ژبه لاره ښکاره ده مسوده\nټ ډ ړ ږ ښ ڼ ې ۍ هۀ';
      for (final family in [
        'Vazirmatn',
        'ScheherazadeNew',
        'NotoNastaliqUrdu',
      ]) {
        await tester.pumpWidget(
          MaterialApp(
            home: Scaffold(
              body: PoetryText(
                text: sample,
                style: TextStyle(fontFamily: family, fontSize: 28, height: 2),
              ),
            ),
          ),
        );
        await tester.pump();
        expect(find.text(sample), findsOneWidget);
        expect(tester.takeException(), isNull);
      }
    },
  );

  testWidgets(
    '16 point poetry uses small Android width efficiently across all fonts',
    (tester) async {
      tester.view.physicalSize = const Size(360, 640);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      const shortLine = 'لنډه کرښه';
      const normalLine = 'زړۀ مې د وطن په مينه ژوندی دی';
      const longLine =
          'د ژوند پر اوږده لاره د هيلو او ارمانونو رڼا له موږ سره روانه ده او تر لرې منزل پورې رسيږي';

      for (final font in {
        'Vazirmatn': 'assets/fonts/vazirmatn/Vazirmatn-wght.ttf',
        'ScheherazadeNew':
            'assets/fonts/scheherazade_new/ScheherazadeNew-Regular.ttf',
        'NotoNastaliqUrdu':
            'assets/fonts/noto_nastaliq_urdu/NotoNastaliqUrdu.ttf',
      }.entries) {
        await (FontLoader(
          font.key,
        )..addFont(rootBundle.load(font.value))).load();
      }

      for (final family in [
        'Vazirmatn',
        'ScheherazadeNew',
        'NotoNastaliqUrdu',
      ]) {
        int visualLineCount(String text) {
          final painter = TextPainter(
            text: TextSpan(
              text: text,
              style: TextStyle(fontFamily: family, fontSize: 16, height: 2.2),
            ),
            textDirection: TextDirection.rtl,
          )..layout(maxWidth: 336);
          return painter.computeLineMetrics().length;
        }

        expect(visualLineCount(shortLine), 1, reason: family);
        expect(visualLineCount(normalLine), 1, reason: family);
        expect(visualLineCount(longLine), greaterThan(1), reason: family);
      }
    },
  );
}
