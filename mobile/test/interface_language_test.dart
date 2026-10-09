import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/app.dart';
import 'package:shelf/l10n/app_strings.dart';
import 'package:shelf/settings/interface_language.dart';
import 'package:shelf/settings/reader_settings.dart';
import 'package:shelf/screens/poem_reader_screen.dart';
import 'package:shelf/share_cards/share_card_screen.dart';
import 'package:shelf/widgets/language_controls.dart';

import 'test_support.dart';

void main() {
  late SharedPreferences preferences;
  late ReaderSettings reader;
  late InterfaceLanguageSettings language;
  setUp(() async {
    SharedPreferences.setMockInitialValues({});
    preferences = await SharedPreferences.getInstance();
    reader = await ReaderSettings.load(preferences);
    language = InterfaceLanguageSettings.load(preferences);
  });
  Future<void> launch(
    WidgetTester tester, {
    bool preview = false,
    Key? key,
  }) async {
    await tester.pumpWidget(
      ShelfApp(
        key: key,
        repository: fixtureRepository(),
        readerSettings: reader,
        languageSettings: language,
        ownerPreviewMode: preview,
      ),
    );
    await tester.pumpAndSettle();
  }

  for (final choice in InterfaceLanguage.values) {
    testWidgets(
      'first launch chooses ${choice.name} once and restores on restart',
      (tester) async {
        await launch(tester);
        expect(find.byType(LanguageChoiceScreen), findsOneWidget);
        expect(find.text(AppStrings.pashto), findsOneWidget);
        expect(find.text(AppStrings.english), findsOneWidget);
        expect(find.byType(NavigationBar), findsNothing);
        await tester.tap(
          find.byKey(
            Key(
              choice == InterfaceLanguage.en
                  ? 'choose-english'
                  : 'choose-pashto',
            ),
          ),
        );
        await tester.pumpAndSettle();
        expect(find.byType(LanguageChoiceScreen), findsNothing);
        expect(language.language, choice);
        expect(
          preferences.getString(InterfaceLanguageSettings.preferenceKey),
          choice.name,
        );
        expect(
          Directionality.of(tester.element(find.byType(NavigationBar))),
          choice == InterfaceLanguage.en
              ? TextDirection.ltr
              : TextDirection.rtl,
        );
        language = InterfaceLanguageSettings.load(preferences);
        await launch(tester, key: const ValueKey('restart'));
        expect(find.byType(LanguageChoiceScreen), findsNothing);
        expect(language.language, choice);
      },
    );
  }

  testWidgets(
    'visible Store button switches in one tap and stays synced with Settings',
    (tester) async {
      tester.view.physicalSize = const Size(360, 640);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);
      await language.select(InterfaceLanguage.ps);
      await launch(tester);
      final header = find.byKey(const Key('store-language-toggle'));
      expect(header.hitTestable(), findsOneWidget);
      expect(
        find.descendant(of: header, matching: find.text('پښتو')),
        findsOneWidget,
      );
      await tester.tap(header);
      await tester.pumpAndSettle();
      await tester.tap(find.text('English'));
      await tester.pumpAndSettle();
      expect(language.language, InterfaceLanguage.en);
      expect(find.text('Store'), findsWidgets);
      expect(find.text('All books'), findsOneWidget);
      expect(
        Directionality.of(tester.element(find.byType(NavigationBar))),
        TextDirection.ltr,
      );
      await tester.tap(find.byIcon(Icons.settings_outlined));
      await tester.pumpAndSettle();
      expect(find.text('English'), findsOneWidget);
      await tester.tap(find.text('Reading preferences'));
      await tester.pumpAndSettle();
      expect(find.text('Text size'), findsOneWidget);
      expect(reader.fontFamily, 'Vazirmatn');
      expect(reader.fontSize, 16);
      await tester.binding.handlePopRoute();
      await tester.pumpAndSettle();
      await tester.tap(find.byKey(const Key('settings-language-toggle')));
      await tester.pumpAndSettle();
      await tester.tap(find.text('پښتو'));
      await tester.pumpAndSettle();
      expect(language.language, InterfaceLanguage.ps);
      expect(
        find.descendant(
          of: find.byKey(const Key('settings-language-toggle')),
          matching: find.text(AppStrings.pashto),
        ),
        findsOneWidget,
      );
      await tester.tap(find.byIcon(Icons.storefront_outlined));
      await tester.pumpAndSettle();
      expect(find.text(AppStrings.allBooks), findsOneWidget);
      expect(header.hitTestable(), findsOneWidget);
      expect(
        preferences.getString(InterfaceLanguageSettings.preferenceKey),
        'ps',
      );
      expect(
        Directionality.of(tester.element(find.byType(NavigationBar))),
        TextDirection.rtl,
      );
      expect(tester.takeException(), isNull);
    },
  );

  testWidgets(
    'English labels apply to tabs and book while source and reader stay RTL',
    (tester) async {
      await language.select(InterfaceLanguage.en);
      await launch(tester, preview: true);
      expect(find.text('Owner preview — unpublished content'), findsOneWidget);
      await tester.tap(find.byType(NavigationDestination).at(1));
      await tester.pumpAndSettle();
      expect(
        find.text('Search by book title, subtitle or author.'),
        findsOneWidget,
      );
      await tester.tap(find.byIcon(Icons.local_library_outlined));
      await tester.pumpAndSettle();
      expect(find.text('Sign in'), findsOneWidget);
      await tester.tap(find.byIcon(Icons.storefront_outlined));
      await tester.pumpAndSettle();
      expect(find.text(collectionJson['title']! as String), findsOneWidget);
      await tester.tap(find.text(collectionJson['title']! as String));
      await tester.pumpAndSettle();
      await tester.scrollUntilVisible(
        find.byKey(const Key('read-sample')),
        200,
      );
      expect(find.text('Read sample'), findsOneWidget);
      await tester.tap(find.byKey(const Key('read-sample')));
      await tester.pumpAndSettle();
      expect(find.byType(PoemReaderScreen), findsOneWidget);
      expect(
        Directionality.of(tester.element(find.byType(PoemReaderScreen))),
        TextDirection.rtl,
      );
      expect(
        find.text('ټ ډ ړ ږ ښ ڼ ې ۍ\nدويمه کرښه\n\nنوی بند'),
        findsOneWidget,
      );
      expect(reader.fontFamily, 'Vazirmatn');
      expect(reader.fontSize, 16);
      await tester.tap(find.byKey(const Key('reader-share')));
      await tester.pumpAndSettle();
      expect(
        Directionality.of(tester.element(find.byType(ShareCardScreen))),
        TextDirection.ltr,
      );
    },
  );

  testWidgets('an unknown stored language asks for an explicit choice', (
    tester,
  ) async {
    await preferences.setString(
      InterfaceLanguageSettings.preferenceKey,
      'invalid',
    );
    language = InterfaceLanguageSettings.load(preferences);
    await launch(tester);
    expect(find.byType(LanguageChoiceScreen), findsOneWidget);
  });
}
