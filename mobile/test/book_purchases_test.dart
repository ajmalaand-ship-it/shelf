import 'dart:async';
import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/accounts/account_controller.dart';
import 'package:shelf/accounts/account_service.dart';
import 'package:shelf/models/poetry_collection.dart';
import 'package:shelf/purchases/book_purchase_provider.dart';
import 'package:shelf/purchases/download_store.dart';
import 'package:shelf/purchases/library_controller.dart';
import 'package:shelf/purchases/library_service.dart';
import 'package:shelf/purchases/purchase_screen.dart';
import 'package:shelf/settings/reader_settings.dart';

import 'accounts_test.dart' show FakeAccounts, FakeGoogle, MemoryTokens;

const book = PoetryCollection(
  id: 42,
  title: 'کتاب',
  slug: 'test-book',
  sortOrder: 1,
  productId: 'shelf_book_42',
);

class FakeBookStore implements BookPurchaseProvider {
  StorePurchaseResult result = StorePurchaseResult.confirming;
  String? user;
  int buys = 0, restores = 0;
  @override
  Future<void> identify(String key, String readerId) async {
    user = readerId;
  }

  @override
  Future<StoreBookProduct?> product(String id) async =>
      StoreBookProduct(id, '€2.79');
  @override
  Future<StorePurchaseResult> buy(StoreBookProduct product) async {
    buys++;
    return result;
  }

  @override
  Future<void> restore() async {
    restores++;
  }

  @override
  Future<void> signOut() async {
    user = null;
  }
}

class MemoryDownloads implements BookDownloadStore {
  final manifests = <int, Map<String, dynamic>>{};
  final copies = <String, Map<String, dynamic>>{};
  final cleared = <int>[];
  @override
  Future<Map<String, dynamic>?> readManifest(int reader) async =>
      manifests[reader];
  @override
  Future<void> writeManifest(int reader, Map<String, dynamic> value) async {
    manifests[reader] = value;
  }

  @override
  Future<Map<String, dynamic>?> readBook(int reader, int book) async =>
      copies['$reader/$book'];
  @override
  Future<void> writeBook(
    int reader,
    int book,
    Map<String, dynamic> value,
  ) async {
    copies['$reader/$book'] = value;
  }

  @override
  Future<void> removeBook(int reader, int book) async {
    copies.remove('$reader/$book');
  }

  @override
  Future<void> clear(int reader) async {
    manifests.remove(reader);
    copies.removeWhere((key, _) => key.startsWith('$reader/'));
    cleared.add(reader);
  }

  @override
  Future<String> writeMedia(
    int reader,
    int book,
    String name,
    Uint8List bytes,
  ) async => 'file:///private/$reader/$book/$name';
}

class FakeLibraryApi implements LibraryService {
  FakeLibraryApi(this.clock);
  final DateTime Function() clock;
  bool owned = false, offline = false;
  bool realCheckout = false, sandboxCheckout = true, rejectConsent = false;
  Map<String, dynamic>? consentOverride;
  Map<String, dynamic>? lastConsent;
  int? fail;
  Completer<Map<String, dynamic>>? delayed;
  final calls = <String>[];
  @override
  Future<Map<String, dynamic>> request(
    String path, {
    String method = 'GET',
    String? token,
    Map<String, dynamic>? data,
  }) async {
    calls.add(path);
    if (offline) throw StateError('Offline');
    if (fail != null) throw AccountFailure(fail!);
    if (path == 'purchases/config')
      return {
        'enabled': true,
        'test_mode': !realCheckout,
        'production_checkout_enabled': realCheckout,
        'sandbox_checkout_enabled': sandboxCheckout,
        'public_sdk_key': 'goog_synthetic',
      };
    if (path.endsWith('/consent')) {
      lastConsent = data;
      if (rejectConsent) throw const AccountFailure(503);
      return consentOverride ?? {'accepted': true, 'app_user_id': '1',
        'product_id': 'shelf_book_42', 'checkout_mode': data?['checkout_mode']};
    }
    if (path == 'library' ||
        path == 'library/confirm' ||
        path == 'library/restore') {
      if (delayed != null) return delayed!.future;
      return {
        'books': owned ? [book.toJson()] : [],
        'offline_valid_until': clock()
            .add(const Duration(days: 30))
            .toIso8601String(),
        'buying_blocked': false,
      };
    }
    if (path == 'library/books/test-book') return {'data': book.toJson()};
    if (path == 'library/books/test-book/content')
      return {
        'data': [
          {
            'id': 1,
            'locked': false,
            'has_audio': false,
            'is_free_sample': false,
          },
        ],
      };
    if (path == 'library/poems/1')
      return {
        'data': {
          'id': 1,
          'body': 'Exact source\r\nمتن',
          'locked': false,
          'artwork': {'url': null},
          'audio': {'available': false},
        },
      };
    throw StateError(path);
  }

  @override
  Future<Uint8List> media(String url, String token) async => Uint8List(0);
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  late AccountController accounts;
  late FakeAccounts accountApi;
  late LibraryController library;
  late FakeLibraryApi api;
  late FakeBookStore provider;
  late MemoryDownloads store;
  late DateTime current;
  setUp(() async {
    current = DateTime.utc(2026, 9, 29);
    accountApi = FakeAccounts();
    accounts = AccountController(
      service: accountApi,
      store: MemoryTokens(),
      google: FakeGoogle(),
    );
    await accounts.initialize();
    await accounts.signIn('synthetic@example.test', 'synthetic');
    api = FakeLibraryApi(() => current);
    provider = FakeBookStore();
    store = MemoryDownloads();
    library = LibraryController(
      internalTestCheckout: true,
      accounts: accounts,
      service: api,
      provider: provider,
      downloads: store,
      clock: () => current,
    );
    await library.product(book);
  });
  tearDown(() {
    library.dispose();
    accounts.dispose();
  });

  test('default release refuses disabled real gate before payment; restore still works', () async {
    library.dispose();
    library = LibraryController(accounts: accounts, service: api, provider: provider, downloads: store);
    await library.product(book);
    expect(library.checkoutAvailable, false);
    await expectLater(library.purchase(book, const StoreBookProduct('shelf_book_42', '€2.79'), true), throwsA(isA<AccountFailure>()));
    expect(provider.buys, 0);
    expect(api.lastConsent, isNull);
    await library.restore();
    expect(provider.restores, 1);
    api.realCheckout = true;
    api.owned = true;
    await library.purchase(book, const StoreBookProduct('shelf_book_42', '€2.79'), true);
    expect(provider.buys, 1);
    expect(api.lastConsent, {'agree': true, 'checkout_mode': 'production'});
    expect(library.owns(42), true);
  });
  test('fresh gate, server consent and mapping must pass before payment', () async {
    api.sandboxCheckout = false;
    await expectLater(library.purchase(book, const StoreBookProduct('shelf_book_42', '€2.79'), true), throwsA(isA<AccountFailure>()));
    expect(provider.buys, 0);
    api.sandboxCheckout = true;
    api.rejectConsent = true;
    await expectLater(library.purchase(book, const StoreBookProduct('shelf_book_42', '€2.79'), true), throwsA(isA<AccountFailure>()));
    api.rejectConsent = false;
    for (final invalid in [
      {'accepted': false},
      {'accepted': true, 'app_user_id': '2', 'product_id': 'shelf_book_42', 'checkout_mode': 'sandbox'},
      {'accepted': true, 'app_user_id': '1', 'product_id': 'shelf_book_43', 'checkout_mode': 'sandbox'},
      {'accepted': true, 'app_user_id': '1', 'product_id': 'shelf_book_42', 'checkout_mode': 'production'},
    ]) {
      api.consentOverride = invalid;
      await expectLater(library.purchase(book, const StoreBookProduct('shelf_book_42', '€2.79'), true), throwsA(isA<AccountFailure>()));
    }
    expect(provider.buys, 0);
    expect(library.owns(42), false);
    expect(await library.purchase(book, const StoreBookProduct('shelf_book_43', '€2.79'), true), StorePurchaseResult.failed);
    expect(provider.buys, 0);
  });
  test('agreement required, cancelled and pending never unlock, server confirms only this book', () async {
    const product = StoreBookProduct('shelf_book_42', '€2.79');
    expect(
      await library.purchase(book, product, false),
      StorePurchaseResult.failed,
    );
    expect(provider.buys, 0);
    for (final result in [
      StorePurchaseResult.cancelled,
      StorePurchaseResult.pending,
      StorePurchaseResult.failed,
    ]) {
      provider.result = result;
      expect(await library.purchase(book, product, true), result);
      expect(library.owns(42), false);
    }
    provider.result = StorePurchaseResult.confirming;
    api.owned = true;
    await library.purchase(book, product, true);
    expect(library.owns(42), true);
    expect(library.owns(43), false);
    expect(provider.user, '${accounts.user!.id}');
  });
  test('confirmed ownership notifies immediately, startup and resume refresh automatically', () async {
    expect(api.calls, contains('library')); // startup
    var observed = false;
    library.addListener(() {
      if (library.owns(42)) observed = true;
    });
    api.owned = true;
    await library.purchase(
      book,
      const StoreBookProduct('shelf_book_42', '€2.79'),
      true,
    );
    expect(observed, true);
    expect(api.calls, contains('library/confirm'));
    api.owned = false;
    library.didChangeAppLifecycleState(AppLifecycleState.resumed);
    await library.refresh();
    expect(library.owns(42), false);
  });
  test('automatic refresh errors are visible and clear on recovery', () async {
    api.offline = true;
    library.didChangeAppLifecycleState(AppLifecycleState.resumed);
    await expectLater(library.refresh(), throwsStateError);
    expect(library.refreshFailed, true);
    expect(library.message, contains('do not buy again'));
    api.offline = false;
    await library.refresh();
    expect(library.refreshFailed, false);
    expect(library.message, isNull);
  });
  test('download preserves source, offline limit, clock rollback, refund and restore', () async {
    api.owned = true;
    await library.download(book);
    expect(store.copies['1/42']!['poems'][0]['body'], 'Exact source\r\nمتن');
    api.offline = true;
    current = current.add(const Duration(days: 29));
    expect(await library.copy(book), isNotNull);
    current = current.subtract(const Duration(days: 2));
    await expectLater(library.copy(book), throwsA(isA<AccountFailure>()));
    current = DateTime.utc(2026, 10, 30);
    await expectLater(library.copy(book), throwsA(isA<AccountFailure>()));
    api.offline = false;
    api.owned = false;
    await library.refresh();
    expect(library.owns(42), false);
    expect(store.copies, isEmpty);
    expect(library.downloaded, isEmpty);
    await library.restore();
    expect(library.owns(42), false);
    expect(provider.restores, 1);
  });
  test('account switch clears downloads and a former account late response cannot grant access', () async {
    api.owned = true;
    await library.download(book);
    api.delayed = Completer<Map<String, dynamic>>();
    final old = library.refresh();
    await Future<void>.delayed(Duration.zero);
    await accounts.signOut();
    expect(library.books, isEmpty);
    api.delayed!.complete({
      'books': [book.toJson()],
      'offline_valid_until': current
          .add(const Duration(days: 30))
          .toIso8601String(),
    });
    await expectLater(old, throwsA(isA<AccountFailure>()));
    api.delayed = null;
    api.owned = false;
    accountApi.id = 2;
    await accounts.signIn('second@example.test', 'synthetic');
    await library.product(book);
    expect(library.readerId, 2);
    expect(library.books, isEmpty);
    expect(store.copies, isEmpty);
    expect(store.cleared, contains(1));
  });
  test('ownership check auth rejection removes local data', () async {
    api.owned = true;
    await library.download(book);
    api.fail = 401;
    await expectLater(library.refresh(), throwsA(isA<AccountFailure>()));
    expect(library.books, isEmpty);
    expect(store.copies, isEmpty);
  });
  test('authenticated media cannot send reader token to another host or follow redirects', () async {
    var sent = 0;
    final service = HttpLibraryService(
      base: Uri.parse('https://example.test/api/'),
      client: MockClient((req) async {
        sent++;
        expect(req.followRedirects, false);
        return http.Response(
          '',
          302,
          headers: {'location': 'https://evil.test/'},
        );
      }),
    );
    await expectLater(
      service.media('https://evil.test/media', 'synthetic'),
      throwsA(isA<AccountFailure>()),
    );
    expect(sent, 0);
    await expectLater(
      service.media('https://example.test/api/library/media', 'synthetic'),
      throwsA(isA<AccountFailure>()),
    );
    expect(sent, 1);
  });
  for (final width in [320.0, 430.0]) {
    for (final testCheckout in [true, false]) {
    testWidgets(
      'purchase screen $width test=$testCheckout uses store price and server gate',
      (tester) async {
        if (!testCheckout) {
          library.dispose();
          await tester.runAsync(() async {
            library = LibraryController(accounts: accounts, service: api, provider: provider, downloads: store);
          });
        }
        tester.view.physicalSize = Size(width, 932);
        tester.view.devicePixelRatio = 1;
        addTearDown(tester.view.resetPhysicalSize);
        addTearDown(tester.view.resetDevicePixelRatio);
        SharedPreferences.setMockInitialValues({});
        final settings = await ReaderSettings.load(
          await SharedPreferences.getInstance(),
        );
        await tester.runAsync(() async {
          await tester.pumpWidget(
            AccountScope(
              controller: accounts,
              child: LibraryScope(
                controller: library,
                child: MaterialApp(
                  home: Directionality(
                    textDirection: TextDirection.rtl,
                    child: PurchaseScreen(book: book, settings: settings),
                  ),
                ),
              ),
            ),
          );
          await Future<void>.delayed(const Duration(milliseconds: 50));
        });
        await tester.pumpAndSettle();
        final buy = find.byKey(const Key('purchase-buy'));
        expect(Directionality.of(tester.element(buy)), TextDirection.ltr);
        expect(
          tester.widget<Text>(find.text(book.title)).textDirection,
          TextDirection.rtl,
        );
        expect(find.text('€2.79'), findsOneWidget);
        expect(tester.widget<FilledButton>(buy).onPressed, isNull);
        await tester.tap(find.byKey(const Key('purchase-agreement')));
        await tester.pumpAndSettle();
        expect(tester.widget<FilledButton>(buy).onPressed, testCheckout ? isNotNull : isNull);
        if (!testCheckout) {
          expect(find.textContaining('Purchases are not available yet'), findsOneWidget);
          expect(provider.buys, 0);
        }
        expect(tester.getSize(buy).height, greaterThanOrEqualTo(52));
        expect(tester.takeException(), isNull);
        await tester.pumpWidget(const SizedBox.shrink());
      },
    );
    }
  }
}
