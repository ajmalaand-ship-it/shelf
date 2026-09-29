import 'dart:io';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/accounts/account_controller.dart';
import 'package:shelf/accounts/account_screen.dart';
import 'package:shelf/app.dart';
import 'package:shelf/settings/interface_language.dart';
import 'package:shelf/settings/reader_settings.dart';

import 'accounts_test.dart' show FakeAccounts, MemoryTokens, FakeGoogle;
import 'test_support.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  setUpAll(() async {
    final appFont = FontLoader('Vazirmatn')
      ..addFont(rootBundle.load('assets/fonts/vazirmatn/Vazirmatn-wght.ttf'));
    await appFont.load();
    for (final font in ['Roboto', 'Ahem']) {
      final loader = FontLoader(font)
        ..addFont(
          Future.value(
            ByteData.sublistView(
              await File(
                '/home/shelf/flutter/bin/cache/artifacts/material_fonts/Roboto-Regular.ttf',
              ).readAsBytes(),
            ),
          ),
        );
      await loader.load();
    }
    final icons = FontLoader('MaterialIcons')
      ..addFont(
        Future.value(
          ByteData.sublistView(
            await File(
              '/home/shelf/flutter/bin/cache/artifacts/material_fonts/MaterialIcons-Regular.otf',
            ).readAsBytes(),
          ),
        ),
      );
    await icons.load();
  });
  for (final language in InterfaceLanguage.values) {
    for (final size in [const Size(320, 568), const Size(430, 932)]) {
      testWidgets(
        '${language.name} ${size.width}: account screenshots and layout',
        (tester) async {
          tester.view.physicalSize = size;
          tester.view.devicePixelRatio = 1;
          addTearDown(tester.view.resetPhysicalSize);
          addTearDown(tester.view.resetDevicePixelRatio);
          SharedPreferences.setMockInitialValues({
            InterfaceLanguageSettings.preferenceKey: language.name,
          });
          final prefs = await SharedPreferences.getInstance();
          final api = FakeAccounts();
          final controller = AccountController(
            service: api,
            store: MemoryTokens(),
            google: FakeGoogle(),
          );
          await controller.initialize();
          final boundary = GlobalKey();
          await tester.pumpWidget(
            RepaintBoundary(
              key: boundary,
              child: ShelfApp(
                repository: fixtureRepository(includeCover: false),
                readerSettings: await ReaderSettings.load(prefs),
                languageSettings: InterfaceLanguageSettings.load(prefs),
                accountController: controller,
              ),
            ),
          );
          await tester.pumpAndSettle();
          Future<void> reveal(Finder target) => tester.scrollUntilVisible(
            target,
            150,
            scrollable: find.descendant(
              of: find.byType(AccountScreen),
              matching: find.byWidgetPredicate(
                (widget) =>
                    widget is Scrollable &&
                    widget.axisDirection == AxisDirection.down,
              ),
            ),
          );
          Future<void> capture(String state) async {
            expect(tester.takeException(), isNull, reason: state);
            final directory = Platform.environment['SHELF_ACCOUNT_SCREENSHOTS'];
            if (directory != null) {
              await tester.runAsync(() async {
                final render =
                    boundary.currentContext!.findRenderObject()!
                        as RenderRepaintBoundary;
                final image = await render.toImage();
                final bytes = await image.toByteData(
                  format: ui.ImageByteFormat.png,
                );
                await Directory(directory).create(recursive: true);
                await File(
                  '$directory/${language.name}-${size.width.toInt()}-$state.png',
                ).writeAsBytes(bytes!.buffer.asUint8List());
                image.dispose();
              });
            }
          }

          await capture('store-signed-out');
          await tester.tap(find.byKey(const Key('store-language-toggle')));
          await tester.pumpAndSettle();
          expect(find.text('English'), findsOneWidget);
          await capture('language-dropdown');
          await tester.binding.handlePopRoute();
          await tester.pumpAndSettle();
          await tester.tap(find.byKey(const Key('store-account')));
          await tester.pumpAndSettle();
          expect(find.byKey(const Key('google-sign-in')), findsNothing);
          final screen = find.descendant(
            of: find.byType(AccountScreen),
            matching: find.byType(Scaffold),
          );
          expect(Directionality.of(tester.element(screen)), TextDirection.ltr);
          await capture('sign-in');
          await tester.tap(find.text('Create account'));
          await tester.pumpAndSettle();
          await capture('create-account');
          await tester.enterText(
            find.byKey(const Key('account-password')),
            'Synthetic!Pass123',
          );
          await tester.enterText(
            find.byKey(const Key('confirm-password')),
            'Synthetic!Pass123',
          );
          await reveal(find.byKey(const Key('account-submit')));
          await tester.pumpAndSettle();
          expect(find.byIcon(Icons.check_circle), findsNWidgets(7));
          await capture('password-checkmarks');
          await reveal(find.text('Forgot password?'));
          await tester.tap(find.text('Forgot password?'));
          await tester.pumpAndSettle();
          await capture('forgot-password');
          await tester.tap(find.text('Sign in'));
          await tester.pumpAndSettle();
          api.error = 422;
          await tester.tap(find.byKey(const Key('account-submit')));
          await tester.pumpAndSettle();
          await capture('validation-error');
          api.error = null;
          await controller.signIn('reader@example.test', 'synthetic');
          await tester.pumpAndSettle();
          await capture('verify-email');
          await tester.tap(find.text('Change password'));
          await tester.pumpAndSettle();
          await capture('change-password');
          await reveal(find.byKey(const Key('account-delete')));
          await tester.tap(find.byKey(const Key('account-delete')));
          await tester.pumpAndSettle();
          await capture('delete-account');
          expect(
            Directionality.of(tester.element(find.byType(AlertDialog))),
            TextDirection.ltr,
          );
          await tester.tap(find.text('Cancel'));
          await tester.pumpAndSettle();
          await tester.pageBack();
          await tester.pumpAndSettle();
          await capture('store-signed-in');
          expect(find.byIcon(Icons.account_circle), findsOneWidget);
        },
      );
    }
  }
}
