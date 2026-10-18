import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shelf/accounts/apple_relay_rehearsal.dart';
import 'package:shelf/accounts/account_controller.dart';
import 'package:shelf/accounts/account_screen.dart';
import 'package:shelf/accounts/account_service.dart';

import 'accounts_test.dart' show FakeAccounts, FakeGoogle, MemoryTokens;

class RelayFixture implements RelayRehearsalProvider {
  bool available = true, saved = false, cancel = false;
  String? error;
  final calls = <String>[];
  @override
  Future<RelayRehearsalStatus> status() async =>
      RelayRehearsalStatus(available: available, saved: saved);
  @override
  Future<bool> prepare() async {
    calls.add('prepare');
    if (error != null) {
      throw PlatformException(
        code: error!,
        message: 'synthetic-private-token',
        details: 'synthetic-code',
      );
    }
    if (cancel) return false;
    saved = true;
    return true;
  }

  @override
  Future<bool> copy() async {
    calls.add('copy');
    return saved;
  }

  @override
  Future<bool> forget() async {
    calls.add('forget');
    saved = false;
    return true;
  }
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  const channel = MethodChannel('services.shelf.app/identity');
  tearDown(() {
    debugDefaultTargetPlatformOverride = null;
    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(channel, null);
  });
  test('Android never asks native code for rehearsal availability', () async {
    var called = false;
    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(channel, (_) async {
          called = true;
          return {'available': true};
        });
    debugDefaultTargetPlatformOverride = TargetPlatform.android;
    expect(
      (await const NativeRelayRehearsalProvider().status()).available,
      false,
    );
    expect(called, false);
  });
  test(
    'native boundary uses only booleans and no arguments/credentials',
    () async {
      debugDefaultTargetPlatformOverride = TargetPlatform.iOS;
      final calls = <String>[];
      TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
          .setMockMethodCallHandler(channel, (call) async {
            expect(call.arguments, isNull);
            calls.add(call.method);
            return call.method == 'relayRehearsalStatus'
                ? {'available': true, 'saved': false}
                : true;
          });
      const p = NativeRelayRehearsalProvider();
      expect((await p.status()).available, true);
      expect(await p.prepare(), true);
      expect(await p.copy(), true);
      expect(await p.forget(), true);
      expect(calls, [
        'relayRehearsalStatus',
        'prepareRelayAddress',
        'copyRelayAddress',
        'forgetRelayAddress',
      ]);
    },
  );
  testWidgets('unavailable rehearsal renders no controls', (tester) async {
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: AppleRelayRehearsal(
            provider: RelayFixture()..available = false,
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();
    expect(find.text('Owner Apple email check'), findsNothing);
  });
  testWidgets(
    'prepare/copy/forget have clear states without displaying email or credentials',
    (tester) async {
      final p = RelayFixture();
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(body: AppleRelayRehearsal(provider: p)),
        ),
      );
      await tester.pumpAndSettle();
      await tester.tap(find.byKey(const Key('relay-rehearsal-start')));
      await tester.pumpAndSettle();
      expect(
        find.textContaining('No Shelf account was created'),
        findsOneWidget,
      );
      expect(find.byKey(const Key('relay-rehearsal-copy')), findsOneWidget);
      await tester.tap(find.byKey(const Key('relay-rehearsal-copy')));
      await tester.pumpAndSettle();
      expect(
        find.textContaining('one minute on this iPhone only'),
        findsOneWidget,
      );
      await tester.tap(find.byKey(const Key('relay-rehearsal-forget')));
      await tester.pumpAndSettle();
      expect(find.byKey(const Key('relay-rehearsal-start')), findsOneWidget);
      expect(p.calls, ['prepare', 'copy', 'forget']);
      expect(find.textContaining('@privaterelay.appleid.com'), findsNothing);
    },
  );
  testWidgets(
    'cancellation and platform errors never claim readiness or display provider details',
    (tester) async {
      final p = RelayFixture()..cancel = true;
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(body: AppleRelayRehearsal(provider: p)),
        ),
      );
      await tester.pumpAndSettle();
      await tester.tap(find.byKey(const Key('relay-rehearsal-start')));
      await tester.pumpAndSettle();
      expect(find.textContaining('Cancelled.'), findsOneWidget);
      p.cancel = false;
      p.error = 'relay_missing';
      await tester.tap(find.byKey(const Key('relay-rehearsal-start')));
      await tester.pumpAndSettle();
      expect(
        find.textContaining('Do not revoke a real account'),
        findsOneWidget,
      );
      expect(find.textContaining('synthetic-private-token'), findsNothing);
      expect(find.textContaining('synthetic-code'), findsNothing);
    },
  );
  testWidgets(
    'account session unchanged; existing normal Apple login appears after later configuration refresh',
    (tester) async {
      debugDefaultTargetPlatformOverride = TargetPlatform.iOS;
      tester.view.physicalSize = const Size(800, 1400);
      tester.view.devicePixelRatio = 1;
      try {
        var saved = false;
        TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
            .setMockMethodCallHandler(channel, (call) async {
              if (call.method == 'relayRehearsalStatus') {
                return {'available': true, 'saved': saved};
              }
              if (call.method == 'prepareRelayAddress') {
                saved = true;
                return true;
              }
              return null;
            });
        final api = FakeAccounts(), store = MemoryTokens();
        final owner = AccountController(
          service: api,
          store: store,
          google: FakeGoogle(),
        );
        await owner.initialize();
        // Keep the reader signed out so normal sign-in controls can be inspected.
        api.calls.clear();
        await tester.pumpWidget(
          MaterialApp(
            home: AccountScope(controller: owner, child: const AccountScreen()),
          ),
        );
        await tester.pumpAndSettle();
        expect(find.byKey(const Key('apple-sign-in')), findsNothing);
        await tester.ensureVisible(
          find.byKey(const Key('relay-rehearsal-start')),
        );
        await tester.tap(find.byKey(const Key('relay-rehearsal-start')));
        await tester.pumpAndSettle();
        expect(api.calls, isEmpty);
        expect(store.token, isNull);
        expect(owner.user, isNull);
        owner.configuration = const AccountConfiguration(
          enabled: true,
          appleEnabled: true,
        );
        owner.notifyListeners();
        await tester.pumpAndSettle();
        expect(find.byKey(const Key('apple-sign-in')), findsOneWidget);
        expect(find.text('Owner Apple email check'), findsNothing);
      } finally {
        tester.view.resetPhysicalSize();
        tester.view.resetDevicePixelRatio();
        debugDefaultTargetPlatformOverride = null;
      }
    },
  );
  testWidgets(
    'relay rehearsal cannot revoke or replace an existing reader session',
    (tester) async {
      debugDefaultTargetPlatformOverride = TargetPlatform.iOS;
      tester.view.physicalSize = const Size(800, 1400);
      tester.view.devicePixelRatio = 1;
      try {
        TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
            .setMockMethodCallHandler(
              channel,
              (call) async => call.method == 'relayRehearsalStatus'
                  ? {'available': true, 'saved': false}
                  : true,
            );
        final api = FakeAccounts(), store = MemoryTokens();
        final owner = AccountController(
          service: api,
          store: store,
          google: FakeGoogle(),
        );
        await owner.initialize();
        await owner.signIn('reader@example.test', 'fixture');
        final beforeToken = owner.readerToken, beforeId = owner.user!.id;
        api.calls.clear();
        await tester.pumpWidget(
          MaterialApp(
            home: AccountScope(controller: owner, child: const AccountScreen()),
          ),
        );
        await tester.pumpAndSettle();
        await tester.ensureVisible(
          find.byKey(const Key('relay-rehearsal-start')),
        );
        await tester.tap(find.byKey(const Key('relay-rehearsal-start')));
        await tester.pumpAndSettle();
        expect(find.textContaining('Address saved privately'), findsOneWidget);
        expect(api.calls, isEmpty);
        expect(owner.readerToken, beforeToken);
        expect(store.token, beforeToken);
        expect(owner.user!.id, beforeId);
      } finally {
        tester.view.resetPhysicalSize();
        tester.view.resetDevicePixelRatio();
        debugDefaultTargetPlatformOverride = null;
      }
    },
  );
}
