import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/l10n/app_strings.dart';
import 'package:shelf/screens/collection_detail_screen.dart';
import 'package:shelf/settings/interface_language.dart';
import 'package:shelf/settings/reader_settings.dart';
import 'package:shelf/settings/reading_preferences_sheet.dart';
import 'package:shelf/widgets/shelf_assets.dart';
import 'package:shelf/widgets/untitled_poem_marker.dart';

import 'test_support.dart';

void main() {
  test('display numbers preserve integer identity and English digits', () {
    const ps = BookstoreStrings(false),
        dari = BookstoreStrings(false, isDari: true),
        en = BookstoreStrings(true);
    for (final strings in [ps, dari]) {
      expect(strings.number(1023456789), '۱۰۲۳۴۵۶۷۸۹');
      expect(strings.number(0), '۰');
      expect(strings.number(-12), '-۱۲');
    }
    expect(en.number(1023456789), '1023456789');
    expect(ps.readingPreferences, 'د لوست سیټینګ');
  });
  for (final language in InterfaceLanguage.values) {
    testWidgets('Contents numbering and untitled semantics ${language.name}', (
      t,
    ) async {
      SharedPreferences.setMockInitialValues({});
      final prefs = await SharedPreferences.getInstance();
      final settings = await ReaderSettings.load(prefs);
      final languages = InterfaceLanguageSettings.load(prefs);
      await languages.select(language);
      await t.pumpWidget(
        InterfaceLanguageScope(
          settings: languages,
          child: MaterialApp(
            home: CollectionDetailScreen(
              slug: 'hendaray-aw-chine',
              contentVersion: 7,
              repository: fixtureRepository(),
              readerSettings: settings,
            ),
          ),
        ),
      );
      await t.pumpAndSettle();
      await t.scrollUntilVisible(
        find.byKey(const Key('poem-list-title-301')),
        200,
      );
      expect(find.byType(UntitledPoemMarker), findsOneWidget);
      expect(find.text('د لومړۍ کرښې پېژندنه'), findsNothing);
      expect(find.text('ژمى'), findsOneWidget);
      expect(
        find.text(language == InterfaceLanguage.en ? '1' : '۱'),
        findsOneWidget,
      );
      expect(
        find.text(language == InterfaceLanguage.en ? '2' : '۲'),
        findsOneWidget,
      );
      final semantics = t.ensureSemantics();
      await t.pump();
      expect(
        find.bySemanticsLabel(switch (language) {
          InterfaceLanguage.en => 'Untitled poem',
          InterfaceLanguage.ps => 'بې سرلیکه شعر',
          InterfaceLanguage.dari => 'شعر بدون عنوان',
        }),
        findsOneWidget,
      );
      semantics.dispose();
      final marker = t.widget<ShelfActionIcon>(
        find.byKey(const Key('untitled-poem-indicator')),
      );
      expect(marker.name, 'poetry');
      expect(poemSummaryJson['id'], 301);
      expect(poemSummaryJson['sort_order'], 1);
      expect(t.takeException(), isNull);
    });
  }
  testWidgets('Pashto pinned reading-settings title is exact', (t) async {
    SharedPreferences.setMockInitialValues({});
    final settings = await ReaderSettings.load(
      await SharedPreferences.getInstance(),
    );
    await t.pumpWidget(
      MaterialApp(
        home: Builder(
          builder: (context) => Scaffold(
            body: TextButton(
              onPressed: () => showReadingPreferences(context, settings),
              child: const Text('Open'),
            ),
          ),
        ),
      ),
    );
    await t.tap(find.text('Open'));
    await t.pumpAndSettle();
    expect(find.text('د لوست سیټینګ'), findsOneWidget);
    expect(find.text('د لوست امستنې'), findsNothing);
    expect(t.takeException(), isNull);
  });
  test('launcher source keeps supplied mark and separate Test identity', () {
    final res = Directory('android/app/src/main/res');
    for (final density in ['mdpi', 'hdpi', 'xhdpi', 'xxhdpi', 'xxxhdpi']) {
      for (final suffix in [
        '',
        '_round',
        '_foreground',
        '_background',
        '_monochrome',
      ]) {
        expect(
          File('${res.path}/mipmap-$density/ic_launcher$suffix.png')
              .existsSync(),
          true,
        );
      }
    }
    final testIcon = File('${res.path}/drawable/shelf_test_icon.xml')
        .readAsStringSync();
    expect(testIcon, contains('@mipmap/ic_launcher'));
    expect(testIcon, contains('@drawable/shelf_test_badge'));
    final gradle = File('android/app/build.gradle.kts').readAsStringSync();
    expect(gradle, contains('applicationId = "services.shelf.app"'));
    expect(gradle, contains('applicationIdSuffix = ".staging"'));
    expect(
      gradle,
      contains('manifestPlaceholders["shelfLabel"] = "Shelf Test"'),
    );
    expect(gradle, contains('SHELF_SIGNING_PROPERTIES'));
  });
}
