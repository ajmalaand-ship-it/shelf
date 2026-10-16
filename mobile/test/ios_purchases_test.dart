import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/repository/poetry_repository.dart';
import 'package:shelf/screens/collection_detail_screen.dart';
import 'package:shelf/purchases/purchase_screen.dart';
import 'package:shelf/settings/reader_settings.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shelf/accounts/account_controller.dart';
import 'package:shelf/models/poetry_collection.dart';
import 'package:shelf/purchases/book_purchase_provider.dart';
import 'package:shelf/purchases/library_controller.dart';
import 'package:shelf/purchases/library_service.dart';

import 'accounts_test.dart' show FakeAccounts, FakeGoogle, MemoryTokens;
import 'book_purchases_test.dart' show FakeBookStore, MemoryDownloads;

const appleBook = PoetryCollection(
  id: 3,
  title: 'Test book',
  slug: 'test',
  sortOrder: 1,
  productId: 'shelf_book_3',
);

class AppleApi implements LibraryService {
  bool enabled = true,
      owned = false,
      consentStoreMatches = true,
      proofFails = false;
  bool ownedTestAllowed = false,
      appleConfirmed = false,
      confirmApple = true,
      echoTestAuthorization = true;
  String sessionIdentity = 'shelf_apple_${'a' * 64}';
  int sessionReader = 1;
  bool expiredSession = false, oldSessionVersion = false;
  Future<void> Function()? beforeConsent;
  final paths = <String>[];
  Map<String, dynamic>? consent;
  @override
  Future<Map<String, dynamic>> request(
    String path, {
    String method = 'GET',
    String? token,
    Map<String, dynamic>? data,
  }) async {
    paths.add(path);
    if (path.startsWith('purchases/config'))
      return {
        'enabled': enabled,
        'purchase_session_version': oldSessionVersion ? 0 : 1,
        'store': 'APP_STORE',
        'public_sdk_key': 'appl_synthetic',
        'production_checkout_enabled': false,
        'sandbox_checkout_enabled': enabled,
        'allowed_product_ids': ['shelf_book_3'],
      };
    if (path.endsWith('/consent')) {
      consent = data;
      await beforeConsent?.call();
      return {
        'accepted': true,
        'owned_book_sandbox_test':
            echoTestAuthorization && data?['owned_book_sandbox_test'] == true,
        'store': consentStoreMatches ? 'APP_STORE' : 'PLAY_STORE',
        'checkout_mode': 'sandbox',
        'product_id': 'shelf_book_3',
        'purchase_session_version': oldSessionVersion ? 0 : 1,
        'reader_id': sessionReader,
        'session_expires_at_ms': DateTime.now()
            .add(Duration(minutes: expiredSession ? -1 : 15))
            .millisecondsSinceEpoch,
        'app_user_id': sessionIdentity,
      };
    }
    if (path == 'library/confirm') {
      owned = true;
      if (confirmApple) appleConfirmed = true;
    }
    if (proofFails) throw StateError('Synthetic provider unavailable');
    return {
      'books': owned ? [appleBook.toJson()] : [],
      'offline_valid_until': DateTime.now()
          .add(const Duration(days: 30))
          .toIso8601String(),
      'buying_blocked': false,
      'apple_owned_book3_test_allowed': ownedTestAllowed,
      'apple_sandbox_book3_confirmed': appleConfirmed,
    };
  }

  @override
  Future<Uint8List> media(String url, String token) async => Uint8List(0);
}

class OwnedTestBooks implements PoetryDataSource {
  @override
  Future<CollectionBundle> loadCollection(
    String slug,
    int contentVersion,
  ) async => const CollectionBundle(collection: appleBook, poems: []);
  @override
  dynamic noSuchMethod(Invocation invocation) => super.noSuchMethod(invocation);
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  late AccountController accounts;
  late LibraryController library;
  late FakeBookStore provider;
  late AppleApi api;
  late MemoryDownloads downloads;
  setUp(() async {
    debugDefaultTargetPlatformOverride = TargetPlatform.iOS;
    accounts = AccountController(
      service: FakeAccounts(),
      store: MemoryTokens(),
      google: FakeGoogle(),
    );
    await accounts.initialize();
    await accounts.signIn('synthetic@example.test', 'synthetic');
    api = AppleApi();
    provider = FakeBookStore();
    downloads = MemoryDownloads();
    library = LibraryController(
      accounts: accounts,
      service: api,
      provider: provider,
      downloads: downloads,
      internalTestCheckout: false,
    );
    await library.product(appleBook);
  });
  tearDown(() {
    library.dispose();
    accounts.dispose();
    debugDefaultTargetPlatformOverride = null;
  });

  test('native target declares in-app purchase capability without changing its identity', () {
    final project = File('ios/Runner.xcodeproj/project.pbxproj')
        .readAsStringSync();
    expect(project, contains('com.apple.InAppPurchase = { enabled = 1; };'));
    expect(
      project,
      contains('PRODUCT_BUNDLE_IDENTIFIER = services.shelf.app;'),
    );
  });
  test('iOS selects Apple configuration and sandbox even in the normal internal app', () async {
    expect(api.paths, contains('purchases/config?platform=ios'));
    expect(library.checkoutMode, 'sandbox');
    expect(library.checkoutAvailable, true);
    expect(provider.user, '1');
    expect(purchaseStoreName, 'App Store');
  });
  test(
    'only book 3 is offered and other books cannot reach the store',
    () async {
      const other = PoetryCollection(
        id: 4,
        title: 'Other',
        slug: 'other',
        sortOrder: 1,
        productId: 'shelf_book_4',
      );
      expect(await library.product(other), null);
      await expectLater(
        library.purchase(
          other,
          const StoreBookProduct('shelf_book_4', 'US\$2.99'),
          true,
        ),
        throwsA(isA<Exception>()),
      );
      expect(provider.buys, 0);
      expect(api.consent, null);
    },
  );
  test(
    'purchase binds Apple consent and unlocks only after server confirmation',
    () async {
      expect(library.owns(3), false);
      expect(
        await library.purchase(
          appleBook,
          const StoreBookProduct('shelf_book_3', 'US\$2.99'),
          true,
        ),
        StorePurchaseResult.confirming,
      );
      expect(api.consent?['store'], 'APP_STORE');
      expect(api.consent?['checkout_mode'], 'sandbox');
      expect(provider.buys, 1);
      expect(provider.user, api.sessionIdentity);
      expect(api.consent?['purchase_session_version'], 1);
      expect(api.paths, contains('library/confirm'));
      expect(library.owns(3), true);
    },
  );
  test('disabled Apple gate and wrong-store consent cannot trigger an SDK purchase', () async {
    api.enabled = false;
    await expectLater(
      library.purchase(
        appleBook,
        const StoreBookProduct('shelf_book_3', 'US\$2.99'),
        true,
      ),
      throwsA(isA<Exception>()),
    );
    api.enabled = true;
    api.consentStoreMatches = false;
    await expectLater(
      library.purchase(
        appleBook,
        const StoreBookProduct('shelf_book_3', 'US\$2.99'),
        true,
      ),
      throwsA(isA<Exception>()),
    );
    expect(provider.buys, 0);
    expect(library.owns(3), false);
  });
  test('restore calls native restore then server proof and refund removes downloaded content', () async {
    api.owned = true;
    await library.restore();
    expect(provider.restores, 1);
    expect(api.paths, contains('library/restore'));
    expect(library.owns(3), true);
    library.downloaded.add(3);
    downloads.copies['1/3'] = {};
    api.owned = false;
    await library.refresh();
    expect(library.owns(3), false);
    expect(downloads.copies['1/3'], null);
    expect(library.downloaded, isEmpty);
  });
  test('unavailable server proof cannot renew the offline lease', () async {
    final expiry = library.validUntil;
    api.proofFails = true;
    await expectLater(library.refresh(), throwsStateError);
    expect(library.validUntil, expiry);
    expect(library.refreshFailed, true);
  });
  test(
    'cancelled and pending store results never unlock or repeat purchase',
    () async {
      for (final result in [
        StorePurchaseResult.cancelled,
        StorePurchaseResult.pending,
      ]) {
        provider.result = result;
        expect(
          await library.purchase(
            appleBook,
            const StoreBookProduct('shelf_book_3', 'US\$2.99'),
            true,
          ),
          result,
        );
        expect(library.owns(3), false);
      }
      expect(provider.buys, 2);
      expect(api.paths.where((p) => p == 'library/confirm'), isEmpty);
    },
  );
  test(
    'platform-specific SDK keys reject the other store and keep Android mode',
    () async {
      final realProvider = RevenueCatBookProvider();
      await expectLater(
        realProvider.identify('goog_synthetic', '1'),
        throwsStateError,
      );
      debugDefaultTargetPlatformOverride = TargetPlatform.android;
      expect(applePurchasePlatform, false);
      expect(purchaseStoreName, 'Google Play');
      expect(library.checkoutMode, 'production');
      await expectLater(
        realProvider.identify('appl_synthetic', '1'),
        throwsStateError,
      );
    },
  );
  test('owned Apple test is denied by default and cannot waive normal duplicate protection', () async {
    api.owned = true;
    await library.refresh();
    expect(library.canTestOwnedAppleBook(appleBook), false);
    expect(
      await library.purchase(
        appleBook,
        const StoreBookProduct('shelf_book_3', 'US\$2.99'),
        true,
        ownedBookSandboxTest: true,
      ),
      StorePurchaseResult.failed,
    );
    api.ownedTestAllowed = true;
    await library.refresh();
    expect(library.canTestOwnedAppleBook(appleBook), true);
    expect(
      await library.purchase(
        appleBook,
        const StoreBookProduct('shelf_book_3', 'US\$2.99'),
        true,
      ),
      StorePurchaseResult.failed,
    );
    expect(provider.buys, 0);
    debugDefaultTargetPlatformOverride = TargetPlatform.android;
    expect(library.canTestOwnedAppleBook(appleBook), false);
    expect(
      await library.purchase(
        appleBook,
        const StoreBookProduct('shelf_book_3', 'US\$2.99'),
        true,
        ownedBookSandboxTest: true,
      ),
      StorePurchaseResult.failed,
    );
    expect(provider.buys, 0);
  });

  test('owned test needs server authorization and Apple confirmation separate from Play access', () async {
    api.owned = api.ownedTestAllowed = true;
    await library.refresh();
    api.echoTestAuthorization = false;
    await expectLater(
      library.purchase(
        appleBook,
        const StoreBookProduct('shelf_book_3', 'US\$2.99'),
        true,
        ownedBookSandboxTest: true,
      ),
      throwsA(isA<Exception>()),
    );
    expect(provider.buys, 0);
    api.echoTestAuthorization = true;
    await library.purchase(
      appleBook,
      const StoreBookProduct('shelf_book_3', 'US\$2.99'),
      true,
      ownedBookSandboxTest: true,
    );
    expect(api.consent?['owned_book_sandbox_test'], true);
    expect(library.appleBook3Confirmed, true);
    expect(library.message, contains('Apple sandbox purchase confirmed'));
    expect(library.owns(3), true);
    expect(library.canTestOwnedAppleBook(appleBook), false);
    expect(provider.buys, 1);
    await accounts.signOut();
    expect(library.appleBook3Confirmed, false);
    expect(library.canTestOwnedAppleBook(appleBook), false);
  });

  test(
    'existing Play ownership alone cannot confirm Apple even after SDK success',
    () async {
      api.owned = api.ownedTestAllowed = true;
      api.confirmApple = false;
      await library.refresh();
      await library.purchase(
        appleBook,
        const StoreBookProduct('shelf_book_3', 'US\$2.99'),
        true,
        ownedBookSandboxTest: true,
      );
      expect(library.owns(3), true);
      expect(library.appleBook3Confirmed, false);
      expect(library.message, contains('Still confirming'));
      expect(provider.buys, 1);
    },
  );

  test(
    'cancel pending and failed owned tests preserve existing book and download',
    () async {
      api.owned = api.ownedTestAllowed = true;
      await library.refresh();
      library.downloaded.add(3);
      downloads.copies['1/3'] = {};
      for (final result in [
        StorePurchaseResult.cancelled,
        StorePurchaseResult.pending,
        StorePurchaseResult.failed,
      ]) {
        provider.result = result;
        expect(
          await library.purchase(
            appleBook,
            const StoreBookProduct('shelf_book_3', 'US\$2.99'),
            true,
            ownedBookSandboxTest: true,
          ),
          result,
        );
        expect(library.owns(3), true);
        expect(library.appleBook3Confirmed, false);
        expect(downloads.copies['1/3'], isNotNull);
      }
      api.proofFails = true;
      await expectLater(library.refresh(), throwsStateError);
      expect(library.canTestOwnedAppleBook(appleBook), false);
    },
  );

  testWidgets(
    'owned test screen clearly identifies no charge and requires consent',
    (tester) async {
      api.owned = api.ownedTestAllowed = true;
      await tester.runAsync(() => library.refresh());
      SharedPreferences.setMockInitialValues({});
      final settings = await ReaderSettings.load(
        await SharedPreferences.getInstance(),
      );
      await tester.pumpWidget(
        AccountScope(
          controller: accounts,
          child: LibraryScope(
            controller: library,
            child: MaterialApp(
              home: CollectionDetailScreen(
                slug: 'test',
                contentVersion: 1,
                repository: OwnedTestBooks(),
                readerSettings: settings,
                ownedMode: true,
              ),
            ),
          ),
        ),
      );
      await tester.pumpAndSettle();
      final entry = find.byKey(const Key('owner-apple-sandbox-test'));
      expect(entry, findsOneWidget);
      await tester.ensureVisible(entry);
      await tester.runAsync(() async {
        await tester.tap(entry);
        await tester.pump();
        await Future<void>.delayed(const Duration(milliseconds: 50));
      });
      await tester.pumpAndSettle();
      expect(
        tester
            .widget<PurchaseScreen>(find.byType(PurchaseScreen))
            .ownedBookSandboxTest,
        true,
      );
      expect(find.text('Apple sandbox test'), findsOneWidget);
      expect(find.textContaining('will not charge you'), findsOneWidget);
      final buy = find.byKey(const Key('purchase-buy'));
      await tester.scrollUntilVisible(
        buy,
        150,
        scrollable: find.byType(Scrollable).last,
      );
      expect(find.text('Test Apple purchase · €2.79'), findsOneWidget);
      expect(tester.widget<FilledButton>(buy).onPressed, isNull);
      await tester.ensureVisible(find.byType(Checkbox));
      await tester.tap(find.byType(Checkbox));
      await tester.pumpAndSettle();
      expect(tester.widget<FilledButton>(buy).onPressed, isNotNull);
      await tester.ensureVisible(buy);
      await tester.runAsync(() async {
        await tester.tap(buy);
        await tester.pump();
        await Future<void>.delayed(const Duration(milliseconds: 50));
      });
      await tester.pumpAndSettle();
      expect(
        find.text(
          'Apple sandbox purchase confirmed. Your existing book stays available.',
        ),
        findsOneWidget,
      );
      expect(library.owns(3), true);
      expect(tester.widget<FilledButton>(buy).onPressed, isNull);
      expect(tester.takeException(), isNull);
      await tester.pumpWidget(const SizedBox.shrink());
      settings.dispose();
      debugDefaultTargetPlatformOverride = null;
    },
    timeout: const Timeout(Duration(seconds: 45)),
  );
  test('unbound numeric, wrong reader, expired and incompatible session consent never buys', () async {
    for (final identity in ['1', 'shelf_apple_short']) {
      api.sessionIdentity = identity;
      await expectLater(
        library.purchase(
          appleBook,
          const StoreBookProduct('shelf_book_3', 'USD2.99'),
          true,
        ),
        throwsA(isA<Exception>()),
      );
    }
    api.sessionIdentity = 'shelf_apple_${'b' * 64}';
    api.sessionReader = 99;
    await expectLater(
      library.purchase(
        appleBook,
        const StoreBookProduct('shelf_book_3', 'USD2.99'),
        true,
      ),
      throwsA(isA<Exception>()),
    );
    api.sessionReader = 1;
    api.expiredSession = true;
    await expectLater(
      library.purchase(
        appleBook,
        const StoreBookProduct('shelf_book_3', 'USD2.99'),
        true,
      ),
      throwsA(isA<Exception>()),
    );
    api.expiredSession = false;
    api.oldSessionVersion = true;
    await expectLater(
      library.purchase(
        appleBook,
        const StoreBookProduct('shelf_book_3', 'USD2.99'),
        true,
      ),
      throwsA(isA<Exception>()),
    );
    expect(provider.buys, 0);
  });

  test(
    'account change while awaiting session cannot start old owner SDK buy',
    () async {
      api.beforeConsent = () => accounts.signOut();
      await expectLater(
        library.purchase(
          appleBook,
          const StoreBookProduct('shelf_book_3', 'USD2.99'),
          true,
        ),
        throwsA(isA<Exception>()),
      );
      expect(provider.buys, 0);
      expect(library.readerId, null);
      expect(library.appleBook3Confirmed, false);
    },
  );
}
