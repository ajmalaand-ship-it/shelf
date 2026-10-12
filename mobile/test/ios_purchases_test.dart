import 'dart:io';

import 'package:flutter/foundation.dart';
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
        'store': 'APP_STORE',
        'public_sdk_key': 'appl_synthetic',
        'production_checkout_enabled': false,
        'sandbox_checkout_enabled': enabled,
        'allowed_product_ids': ['shelf_book_3'],
      };
    if (path.endsWith('/consent')) {
      consent = data;
      return {
        'accepted': true,
        'store': consentStoreMatches ? 'APP_STORE' : 'PLAY_STORE',
        'checkout_mode': 'sandbox',
        'product_id': 'shelf_book_3',
        'app_user_id': '1',
      };
    }
    if (path == 'library/confirm') owned = true;
    if (proofFails) throw StateError('Synthetic provider unavailable');
    return {
      'books': owned ? [appleBook.toJson()] : [],
      'offline_valid_until': DateTime.now()
          .add(const Duration(days: 30))
          .toIso8601String(),
      'buying_blocked': false,
    };
  }

  @override
  Future<Uint8List> media(String url, String token) async => Uint8List(0);
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
}
