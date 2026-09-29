import 'dart:async';
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/accounts/account_controller.dart';
import 'package:shelf/accounts/account_service.dart';
import 'package:shelf/accounts/account_screen.dart';
import 'package:shelf/app.dart';
import 'package:shelf/l10n/app_strings.dart';
import 'package:shelf/settings/interface_language.dart';
import 'package:shelf/settings/reader_settings.dart';

import 'test_support.dart';

class MemoryTokens implements AccountTokenStore {
  String? token;
  bool failWrite = false;
  @override
  Future<String?> read() async => token;
  @override
  Future<void> write(String value) async {
    if (failWrite) throw StateError('Synthetic storage failure');
    token = value;
  }

  @override
  Future<void> clear() async {
    token = null;
  }
}

class FakeGoogle implements GoogleAccountProvider {
  int exits = 0;
  @override
  Future<String> idToken(String id) async => 'synthetic-google';
  @override
  Future<void> signOut() async {
    exits++;
  }
}

class FakeAccounts implements AccountService {
  bool google = false;
  int? error;
  int id = 1;
  Completer<Map<String, dynamic>>? pendingMe;
  final calls = <String>[];
  Map<String, dynamic> profile() => {
    'id': id,
    'email': 'reader$id@example.test',
    'name': null,
    'email_verified': false,
    'sign_in_method': 'email',
  };
  @override
  Future<AccountConfiguration> configuration() async => AccountConfiguration(
    enabled: true,
    googleEnabled: google,
    webClientId: google ? 'synthetic-web' : null,
  );
  @override
  Future<Map<String, dynamic>> request(
    String path, {
    String method = 'POST',
    Map<String, dynamic> data = const {},
    String? token,
  }) async {
    calls.add(path);
    if (error != null) throw AccountFailure(error!);
    if (path == 'me' && pendingMe != null) return pendingMe!.future;
    if (path == 'me') return {'user': profile()};
    if (path == 'login' || path == 'google')
      return {'user': profile(), 'token': 'synthetic-token-$id'};
    return {};
  }
}

void main() {
  late FakeAccounts api;
  late MemoryTokens store;
  late FakeGoogle google;
  late AccountController c;
  setUp(() async {
    api = FakeAccounts();
    store = MemoryTokens();
    google = FakeGoogle();
    c = AccountController(service: api, store: store, google: google);
    await c.initialize();
  });
  test(
    'switching identity revokes first account and stores only the new token',
    () async {
      await c.signIn('one', 'password');
      expect(c.user!.id, 1);
      api.id = 2;
      await c.signIn('two', 'password');
      expect(api.calls, ['login', 'logout', 'login']);
      expect(c.user!.id, 2);
      expect(store.token, 'synthetic-token-2');
      await c.signOut();
      expect(c.user, isNull);
      expect(store.token, isNull);
    },
  );
  test('password change clears identity; revoked session cannot restore cached profile', () async {
    await c.signIn('one', 'password');
    await c.changePassword('old', 'new', 'new');
    expect(c.user, isNull);
    expect(store.token, isNull);
    await c.signIn('one', 'password');
    api.error = 401;
    await expectLater(c.refresh(), throwsA(isA<AccountFailure>()));
    expect(c.user, isNull);
    expect(store.token, isNull);
  });
  test(
    'failed secure storage revokes issued token and exposes no user',
    () async {
      store.failWrite = true;
      await expectLater(c.signIn('one', 'password'), throwsStateError);
      expect(api.calls, ['login', 'logout']);
      expect(c.user, isNull);
    },
  );
  test(
    'late startup response cannot replace the newly signed-in reader',
    () async {
      store.token = 'old';
      api.pendingMe = Completer();
      final startup = c.initialize();
      await Future<void>.delayed(Duration.zero);
      api.id = 2;
      await c.signIn('two', 'password');
      api.pendingMe!.complete({
        'user': {
          'id': 1,
          'email': 'old@example.test',
          'name': null,
          'email_verified': true,
          'sign_in_method': 'email',
        },
      });
      await startup;
      expect(c.user!.id, 2);
      expect(store.token, 'synthetic-token-2');
    },
  );
  test(
    'HTTP account client keeps auth separate and refuses redirects',
    () async {
      final client = HttpAccountService(
        base: Uri.parse('https://example.test/api/'),
        client: MockClient((request) async {
          expect(request.url.path, '/api/auth/me');
          expect(request.headers['Authorization'], 'Bearer synthetic-reader');
          expect(request.headers.containsKey('X-Owner-Preview-Token'), false);
          expect(request.followRedirects, false);
          return http.Response(jsonEncode({'user': api.profile()}), 200);
        }),
      );
      await client.request('me', method: 'GET', token: 'synthetic-reader');
    },
  );
  for (final language in InterfaceLanguage.values) {
    Future<BookstoreStrings> launch(
      WidgetTester tester, {
      bool signedIn = false,
      bool googleEnabled = false,
    }) async {
      api.google = googleEnabled;
      await c.initialize();
      if (signedIn) await c.signIn('one', 'password');
      SharedPreferences.setMockInitialValues({
        InterfaceLanguageSettings.preferenceKey: language.name,
      });
      final prefs = await SharedPreferences.getInstance();
      await tester.pumpWidget(
        ShelfApp(
          repository: fixtureRepository(),
          readerSettings: await ReaderSettings.load(prefs),
          languageSettings: InterfaceLanguageSettings.load(prefs),
          accountController: c,
        ),
      );
      await tester.pumpAndSettle();
      final s = AppStrings.of(tester.element(find.byType(NavigationBar)));
      await tester.tap(find.text(s.settings));
      await tester.pumpAndSettle();
      await tester.tap(find.byKey(const Key('settings-account')));
      await tester.pumpAndSettle();
      return s;
    }

    testWidgets(
      '${language.name}: register, forgot, login, verify and delete screen stays RTL',
      (tester) async {
        final s = await launch(tester);
        expect(find.byKey(const Key('google-sign-in')), findsNothing);
        expect(
          Directionality.of(tester.element(find.byType(AccountScreen))),
          TextDirection.rtl,
        );
        await tester.tap(find.text(s.createAccount));
        await tester.pumpAndSettle();
        await tester.enterText(
          find.byKey(const Key('account-email')),
          'reader@example.test',
        );
        await tester.enterText(
          find.byKey(const Key('account-password')),
          'Synthetic!Pass123',
        );
        await tester.enterText(
          find.byKey(const Key('confirm-password')),
          'Synthetic!Pass123',
        );
        await tester.ensureVisible(find.byKey(const Key('account-submit')));
        await tester.tap(find.byKey(const Key('account-submit')));
        await tester.pumpAndSettle();
        expect(api.calls, contains('register'));
        expect(find.text(s.emailSent), findsOneWidget);
        await tester.ensureVisible(find.text(s.forgotPassword));
        await tester.tap(find.text(s.forgotPassword));
        await tester.pumpAndSettle();
        await tester.tap(find.byKey(const Key('account-submit')));
        await tester.pumpAndSettle();
        expect(api.calls, contains('forgot-password'));
        await tester.tap(find.text(s.signIn));
        await tester.pumpAndSettle();
        await tester.enterText(
          find.byKey(const Key('account-password')),
          'Synthetic!Pass123',
        );
        await tester.tap(find.byKey(const Key('account-submit')));
        await tester.pumpAndSettle();
        expect(find.text('reader1@example.test'), findsOneWidget);
        await tester.tap(find.text(s.resendVerification));
        await tester.pumpAndSettle();
        expect(api.calls, contains('verify/resend'));
        await tester.tap(find.byKey(const Key('account-delete')));
        await tester.pumpAndSettle();
        await tester.tap(find.text(s.confirmDelete));
        await tester.pumpAndSettle();
        expect(api.calls, contains('delete-request'));
        expect(c.user, isNotNull);
        expect(find.text(s.deleteEmailSent), findsOneWidget);
        await tester.tap(find.byKey(const Key('account-sign-out')));
        await tester.pumpAndSettle();
        expect(c.user, isNull);
      },
    );
    testWidgets(
      '${language.name}: Google enabled, library identity and password form',
      (tester) async {
        final s = await launch(tester, googleEnabled: true);
        await tester.tap(find.byKey(const Key('google-sign-in')));
        await tester.pumpAndSettle();
        expect(api.calls, contains('google'));
        await tester.tap(find.text(s.changePassword));
        await tester.pumpAndSettle();
        await tester.enterText(
          find.byKey(const Key('current-password')),
          'old',
        );
        await tester.enterText(
          find.byKey(const Key('account-password')),
          'Synthetic!Pass123',
        );
        await tester.enterText(
          find.byKey(const Key('confirm-password')),
          'Synthetic!Pass123',
        );
        await tester.ensureVisible(find.text(s.changePassword));
        await tester.tap(find.text(s.changePassword));
        await tester.pumpAndSettle();
        expect(c.user, isNull);
        expect(find.text(s.passwordChanged), findsWidgets);
        await c.signIn('one', 'password');
        await tester.pageBack();
        await tester.pumpAndSettle();
        await tester.tap(find.text(s.library));
        await tester.pumpAndSettle();
        expect(find.text(s.booksWillAppear), findsOneWidget);
        await c.signOut();
        await tester.pumpAndSettle();
        expect(find.text(s.signIn), findsOneWidget);
        expect(find.text(s.booksWillAppear), findsNothing);
        expect(find.text('reader1@example.test'), findsNothing);
        await tester.tap(find.byKey(const Key('library-account')));
        await tester.pumpAndSettle();
        expect(find.byType(AccountScreen), findsOneWidget);
      },
    );
    testWidgets(
      '${language.name}: validation and network errors are safe localized messages',
      (tester) async {
        final s = await launch(tester);
        api.error = 422;
        await tester.tap(find.byKey(const Key('account-submit')));
        await tester.pumpAndSettle();
        expect(find.text(s.accountError(422)), findsOneWidget);
        expect(find.text('Synthetic!Pass123'), findsNothing);
      },
    );
  }
}
