import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:pitswal/app.dart';
import 'package:pitswal/models/poem.dart';
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
    await tester.tap(find.byKey(const Key('home-font-chooser')));
    await tester.pumpAndSettle();
    expect(find.byKey(const Key('reading-font-selector')), findsOneWidget);
    await tester.tap(find.text('نسخ'));
    await tester.pumpAndSettle();
    expect(settings.font, ReaderFont.naskh);
  });

  test(
    'visual grouping preserves exact lines and authored blank separators',
    () {
      const source = '۱\n۲\n\n۳\n۴\n۵\n۶';
      expect(poetryDisplayGroups(source, 2), ['۱\n۲', '', '۳\n۴', '۵\n۶']);
      expect(poetryDisplayGroups(source, 4), ['۱\n۲', '', '۳\n۴\n۵\n۶']);
      expect(poetryDisplayGroups(source, 2).join('\n'), source);
    },
  );

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
                layoutMode: PoemLayoutMode.source,
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
}
