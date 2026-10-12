import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shelf/accounts/account_controller.dart';
import 'package:shelf/accounts/account_service.dart';
import 'package:shelf/accounts/account_screen.dart';
import 'package:crypto/crypto.dart';

import 'dart:convert';

import 'package:http/testing.dart';
import 'package:http/http.dart' as http;

import 'accounts_test.dart' show FakeAccounts, FakeGoogle, MemoryTokens;

class AppleApi extends FakeAccounts {
  Map<String, dynamic>? submitted;
  @override
  Future<AccountConfiguration> configuration() async =>
      const AccountConfiguration(enabled: true, appleEnabled: true);
  @override
  Future<Map<String, dynamic>> request(
    String path, {
    String method = 'POST',
    Map<String, dynamic> data = const {},
    String? token,
  }) async {
    if (path == 'apple-challenge')
      return {'challenge': 'synthetic-challenge', 'nonce': 'synthetic-nonce'};
    if (path == 'apple') {
      submitted = data;
      return {'user': profile(), 'token': 'apple-reader-token'};
    }
    return super.request(path, method: method, data: data, token: token);
  }
}

class FakeApple implements AppleAccountProvider {
  bool cancel = false;
  String? nonce;
  @override
  Future<Map<String, dynamic>?> credential(String value) async {
    nonce = value;
    return cancel
        ? null
        : {
            'id_token': 'synthetic-apple',
            'authorization_code': 'synthetic-code',
          };
  }
}

class CancelGoogle extends FakeGoogle {
  @override
  Future<String> idToken(String id) async => throw const AccountCancelled();
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  test('Apple request carries server challenge and credential; cancellation preserves session', () async {
    final api = AppleApi(), store = MemoryTokens(), apple = FakeApple();
    final c = AccountController(
      service: api,
      store: store,
      google: FakeGoogle(),
      apple: apple,
    );
    await c.initialize();
    await c.appleSignIn();
    expect(api.submitted, {
      'challenge': 'synthetic-challenge',
      'id_token': 'synthetic-apple',
      'authorization_code': 'synthetic-code',
    });
    expect(apple.nonce, 'synthetic-nonce');
    expect(store.token, 'apple-reader-token');
    apple.cancel = true;
    await c.appleSignIn();
    expect(store.token, 'apple-reader-token');
    expect(api.calls, isNot(contains('logout')));
  });
  test(
    'Native Apple receives hash; Google missing client fails closed on iOS',
    () async {
      debugDefaultTargetPlatformOverride = TargetPlatform.iOS;
      const channel = MethodChannel('services.shelf.app/identity');
      TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
          .setMockMethodCallHandler(channel, (call) async {
            if (call.method == 'apple')
              expect(
                call.arguments,
                sha256.convert(utf8.encode('nonce')).toString(),
              );
            return null;
          });
      try {
        expect(
          await const PlatformAppleAccountProvider().credential('nonce'),
          isNull,
        );
        expect(await iosGoogleClientId(), isNull);
      } finally {
        debugDefaultTargetPlatformOverride = null;
        TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
            .setMockMethodCallHandler(channel, null);
      }
    },
  );
  test('Google cancellation keeps the current reader session', () async {
    final api = FakeAccounts()..google = true, store = MemoryTokens();
    final c = AccountController(
      service: api,
      store: store,
      google: CancelGoogle(),
    );
    await c.initialize();
    await c.signIn('reader', 'password');
    final before = store.token;
    await c.googleSignIn();
    expect(store.token, before);
    expect(api.calls, isNot(contains('logout')));
  });
  test('Google iOS is gated by a native client while Android retains server configuration', () async {
    const channel = MethodChannel('services.shelf.app/identity');
    String? client;
    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(channel, (_) async => client);
    final api = HttpAccountService(
      base: Uri.parse('https://example.test/api/'),
      client: MockClient(
        (_) async => http.Response(
          jsonEncode({
            'enabled': true,
            'google_enabled': true,
            'google_web_client_id': 'synthetic-web',
            'apple_enabled': true,
          }),
          200,
        ),
      ),
    );
    try {
      debugDefaultTargetPlatformOverride = TargetPlatform.iOS;
      expect((await api.configuration()).googleEnabled, false);
      client = 'synthetic-client.apps.googleusercontent.com';
      final ready = await api.configuration();
      expect(ready.googleEnabled, true);
      expect(ready.appleEnabled, true);
      debugDefaultTargetPlatformOverride = TargetPlatform.android;
      client = null;
      final android = await api.configuration();
      expect(android.googleEnabled, true);
      expect(android.appleEnabled, false);
    } finally {
      debugDefaultTargetPlatformOverride = null;
      TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
          .setMockMethodCallHandler(channel, null);
    }
  });
  testWidgets(
    'Apple sign-in control appears only when enabled and retains email/password',
    (tester) async {
      final c = AccountController(
        service: AppleApi(),
        store: MemoryTokens(),
        google: FakeGoogle(),
        apple: FakeApple(),
      );
      await c.initialize();
      await tester.pumpWidget(
        MaterialApp(
          home: AccountScope(controller: c, child: const AccountScreen()),
        ),
      );
      expect(find.byKey(const Key('apple-sign-in')), findsOneWidget);
      expect(find.text('Sign in with Apple'), findsOneWidget);
      expect(find.byKey(const Key('google-sign-in')), findsNothing);
      c.configuration = const AccountConfiguration(enabled: true);
      c.notifyListeners();
      await tester.pump();
      expect(find.byKey(const Key('apple-sign-in')), findsNothing);
    },
  );
}
