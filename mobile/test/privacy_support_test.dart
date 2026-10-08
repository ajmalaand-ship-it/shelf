import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/l10n/app_strings.dart';
import 'package:shelf/settings/interface_language.dart';
import 'package:shelf/settings/privacy_support.dart';
import 'package:shelf/settings/reader_settings.dart';
import 'package:shelf/screens/home_screen.dart';

void main() {
  const channel = MethodChannel('services.shelf.app/external');
  for (final english in [true, false]) {
    for (final size in [const Size(320, 568), const Size(430, 932)]) {
      testWidgets('Settings links and failed email fallback $english $size', (
        tester,
      ) async {
        tester.view.physicalSize = size;
        tester.view.devicePixelRatio = 1;
        addTearDown(tester.view.resetPhysicalSize);
        addTearDown(tester.view.resetDevicePixelRatio);
        SharedPreferences.setMockInitialValues({
          'shelf.interface_language': english ? 'en' : 'ps',
        });
        final prefs = await SharedPreferences.getInstance();
        final language = InterfaceLanguageSettings.load(prefs);
        final reader = await ReaderSettings.load(prefs);
        final calls = <String>[];
        tester.binding.defaultBinaryMessenger.setMockMethodCallHandler(
          channel,
          (call) async {
            calls.add(call.arguments as String);
            return false;
          },
        );
        String? copied;
        tester.binding.defaultBinaryMessenger.setMockMethodCallHandler(
          SystemChannels.platform,
          (call) async {
            if (call.method == 'Clipboard.setData')
              copied = (call.arguments as Map)['text'] as String;
            return null;
          },
        );
        addTearDown(() {
          tester.binding.defaultBinaryMessenger.setMockMethodCallHandler(
            channel,
            null,
          );
          tester.binding.defaultBinaryMessenger.setMockMethodCallHandler(
            SystemChannels.platform,
            null,
          );
        });
        await tester.pumpWidget(
          InterfaceLanguageScope(
            settings: language,
            child: MaterialApp(
              home: Directionality(
                textDirection: english ? TextDirection.ltr : TextDirection.rtl,
                child: Scaffold(body: SettingsScreen(settings: reader)),
              ),
            ),
          ),
        );
        final s = BookstoreStrings(english);
        expect(find.text(s.privacyPolicy), findsOneWidget);
        expect(find.text(s.support), findsOneWidget);
        await tester.ensureVisible(find.byKey(const Key('settings-support')));
        await tester.tap(find.byKey(const Key('settings-support')));
        await tester.pumpAndSettle();
        final address = find.byType(SelectableText);
        expect(tester.widget<SelectableText>(address).data, supportEmail);
        expect(
          tester.widget<SelectableText>(address).textDirection,
          TextDirection.ltr,
        );
        await tester.tap(find.byKey(const Key('open-support-email')));
        await tester.pumpAndSettle();
        expect(calls.single, 'mailto:ajmalaand@gmail.com');
        expect(find.text(s.emailUnavailable), findsOneWidget);
        await tester.tap(find.byKey(const Key('copy-contact-address')));
        await tester.pumpAndSettle();
        expect(copied, supportEmail);
        expect(tester.takeException(), isNull);
        Navigator.of(tester.element(find.byType(AlertDialog))).pop();
        await tester.pumpAndSettle();
        await tester.ensureVisible(find.byKey(const Key('settings-privacy')));
        await tester.tap(find.byKey(const Key('settings-privacy')));
        await tester.pumpAndSettle();
        expect(calls.last, 'https://shelf.services/privacy');
        expect(find.text(s.browserUnavailable), findsOneWidget);
        await tester.tap(find.byKey(const Key('copy-contact-address')));
        await tester.pumpAndSettle();
        expect(copied, 'https://shelf.services/privacy');
        expect(tester.takeException(), isNull);
      });
    }
  }
  testWidgets('successful and throwing external handlers', (tester) async {
    tester.binding.defaultBinaryMessenger.setMockMethodCallHandler(
      channel,
      (_) async => true,
    );
    expect(
      await openShelfExternal(Uri.parse('https://shelf.services/privacy')),
      isTrue,
    );
    tester.binding.defaultBinaryMessenger.setMockMethodCallHandler(
      channel,
      (_) async => throw PlatformException(code: 'unavailable'),
    );
    expect(
      await openShelfExternal(Uri.parse('mailto:ajmalaand@gmail.com')),
      isFalse,
    );
    tester.binding.defaultBinaryMessenger.setMockMethodCallHandler(
      channel,
      null,
    );
  });
}
