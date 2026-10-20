import 'dart:typed_data';

import 'package:flutter/foundation.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shelf/accounts/account_controller.dart';
import 'package:shelf/models/poetry_collection.dart';
import 'package:shelf/purchases/book_purchase_provider.dart';
import 'package:shelf/purchases/library_controller.dart';
import 'package:shelf/purchases/library_service.dart';

import 'accounts_test.dart' show FakeAccounts, FakeGoogle, MemoryTokens;
import 'book_purchases_test.dart' show FakeBookStore, MemoryDownloads;

PoetryCollection book(int id) => PoetryCollection(
  id: id,
  title: 'Synthetic $id',
  slug: 'test-$id',
  sortOrder: id,
  productId: 'shelf_book_$id',
);

class CustomerApi implements LibraryService {
  bool checkout = true, proofFails = false, wrongIdentity = false;
  final allowed = <int>{3, 4, 5, 6, 7, 8};
  final owned = <int>{};
  int? pending;
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
    if (path.startsWith('purchases/config')) {
      return {
        'enabled': true,
        'customer_mode': true,
        'purchase_session_version': 2,
        'store': 'APP_STORE',
        'public_sdk_key': 'appl_synthetic',
        'production_checkout_enabled': checkout,
        'sandbox_checkout_enabled': false,
        'allowed_product_ids': [for (final id in allowed) 'shelf_book_$id'],
      };
    }
    if (path.endsWith('/consent')) {
      consent = data;
      pending = int.parse(path.split('/')[2]);
      return {
        'accepted': true,
        'checkout_mode': 'production',
        'store': 'APP_STORE',
        'purchase_session_version': 2,
        'reader_id': 1,
        'app_user_id': wrongIdentity ? '99' : '1',
        'product_id': 'shelf_book_$pending',
      };
    }
    if (proofFails) throw StateError('Synthetic proof unavailable');
    if (path == 'library/confirm' && pending != null) owned.add(pending!);
    return {
      'books': [for (final id in owned) book(id).toJson()],
      'offline_valid_until': DateTime.now()
          .add(const Duration(days: 30))
          .toIso8601String(),
      'buying_blocked': false,
      'apple_owned_book3_test_allowed': false,
      'apple_sandbox_book3_confirmed': false,
    };
  }

  @override
  Future<Uint8List> media(String url, String token) async => Uint8List(0);
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  late AccountController accounts;
  late LibraryController library;
  late CustomerApi api;
  late FakeBookStore provider;
  late MemoryDownloads downloads;
  setUp(() async {
    debugDefaultTargetPlatformOverride = TargetPlatform.iOS;
    accounts = AccountController(
      service: FakeAccounts(),
      store: MemoryTokens(),
      google: FakeGoogle(),
    );
    await accounts.initialize();
    await accounts.signIn('ordinary@example.test', 'synthetic');
    api = CustomerApi();
    provider = FakeBookStore();
    downloads = MemoryDownloads();
    library = LibraryController(
      accounts: accounts,
      service: api,
      provider: provider,
      downloads: downloads,
      internalTestCheckout: false,
    );
    await library.product(book(3));
  });
  tearDown(() {
    library.dispose();
    accounts.dispose();
    debugDefaultTargetPlatformOverride = null;
  });

  test('all six normal products purchase under the same authenticated numeric identity', () async {
    for (var id = 3; id <= 8; id++) {
      final product = await library.product(book(id));
      expect(product?.id, 'shelf_book_$id');
      expect(library.checkoutMode, 'production');
      expect(
        await library.purchase(book(id), product!, true),
        StorePurchaseResult.confirming,
      );
      expect(provider.user, '1');
      expect(api.consent?['purchase_session_version'], 2);
      expect(api.consent?['checkout_mode'], 'production');
      expect(library.owns(id), true);
      expect(
        await library.purchase(book(id), product, true),
        StorePurchaseResult.failed,
      );
    }
    expect(provider.buys, 6);
    expect(library.canTestOwnedAppleBook(book(3)), false);
    expect(await library.product(book(9)), null);
  });

  test('future IDs come from catalogue and server allowlist, not six Flutter constants', () async {
    api.allowed.addAll([9, 42, 125, 1001]);
    for (final id in [9, 42, 125, 1001]) {
      final future = PoetryCollection.fromJson({
        ...book(id).toJson(),
        'store_product_ids': {
          'APP_STORE': 'shelf_book_$id',
          'PLAY_STORE': 'shelf_book_$id',
        },
      });
      final product = await library.product(future);
      expect(product?.id, 'shelf_book_$id');
      expect(product?.price, '€2.79');
      expect(
        await library.purchase(future, product!, true),
        StorePurchaseResult.confirming,
      );
      expect(library.owns(id), true);
      expect(provider.user, '1');
    }
    expect(await library.product(book(9999)), null);
    final missingApple = PoetryCollection.fromJson({
      ...book(42).toJson(),
      'store_product_ids': {'PLAY_STORE': 'shelf_book_42'},
    });
    expect(await library.product(missingApple), null);
    final mismatch = PoetryCollection.fromJson({
      ...book(42).toJson(),
      'store_product_ids': {'APP_STORE': 'shelf_book_125'},
    });
    expect(await library.product(mismatch), null);
  });

  test('restore uses the same identity then trusts only server books and clears a refund download', () async {
    api.owned.addAll([3, 4, 5, 6, 7, 8]);
    await library.restore();
    expect(provider.user, '1');
    expect(provider.restores, 1);
    expect(api.paths, contains('library/restore'));
    expect(library.owns(8), true);
    library.downloaded.add(8);
    downloads.copies['1/8'] = {};
    api.owned.remove(8);
    await library.restore();
    expect(library.owns(8), false);
    expect(downloads.copies['1/8'], null);
    expect(library.owns(3), true);
  });

  test(
    'checkout OFF and another customer identity never reach store purchase',
    () async {
      api.checkout = false;
      await expectLater(
        library.purchase(
          book(4),
          const StoreBookProduct('shelf_book_4', 'USD2.99'),
          true,
        ),
        throwsA(isA<Exception>()),
      );
      expect(provider.buys, 0);
      expect(api.consent, null);
      api.checkout = true;
      api.wrongIdentity = true;
      await expectLater(
        library.purchase(
          book(4),
          const StoreBookProduct('shelf_book_4', 'USD2.99'),
          true,
        ),
        throwsA(isA<Exception>()),
      );
      expect(provider.buys, 0);
    },
  );

  test(
    'cancelled and pending purchases do not unlock or run another purchase',
    () async {
      for (final result in [
        StorePurchaseResult.cancelled,
        StorePurchaseResult.pending,
      ]) {
        provider.result = result;
        expect(
          await library.purchase(
            book(8),
            const StoreBookProduct('shelf_book_8', 'USD2.99'),
            true,
          ),
          result,
        );
        expect(library.owns(8), false);
      }
      expect(provider.buys, 2);
      expect(api.paths, isNot(contains('library/confirm')));
    },
  );

  test(
    'failed server proof cannot renew lease or move ownership after sign-out',
    () async {
      api.owned.add(3);
      await library.restore();
      final expiry = library.validUntil;
      api.proofFails = true;
      await expectLater(library.restore(), throwsStateError);
      expect(library.validUntil, expiry);
      expect(library.refreshFailed, true);
      await accounts.signOut();
      expect(library.owns(3), false);
    },
  );
}
