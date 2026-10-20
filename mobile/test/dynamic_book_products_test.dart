import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/accounts/account_controller.dart';
import 'package:shelf/models/poetry_collection.dart';
import 'package:shelf/purchases/book_purchase_provider.dart';
import 'package:shelf/purchases/library_controller.dart';
import 'package:shelf/purchases/purchase_screen.dart';
import 'package:shelf/settings/reader_settings.dart';

import 'accounts_test.dart' show FakeAccounts, FakeGoogle, MemoryTokens;
import 'book_purchases_test.dart' show FakeBookStore, MemoryDownloads;
import 'ios_customer_purchases_test.dart' show CustomerApi, book;

class MissingStore extends FakeBookStore {
  bool unavailable = true;
  @override
  Future<StoreBookProduct?> product(String id) async =>
      unavailable ? null : StoreBookProduct(id, '€4.49');
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  const channel = MethodChannel('purchases_flutter');
  for (final platform in [TargetPlatform.iOS, TargetPlatform.android]) {
    test(
      'dynamic native StoreProduct lookup and selected purchase on $platform',
      () async {
        debugDefaultTargetPlatformOverride = platform;
        final calls = <MethodCall>[];
        var products = <Map<String, dynamic>>[];
        TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
            .setMockMethodCallHandler(channel, (call) async {
              calls.add(call);
              if (call.method == 'getAppUserID') return '1';
              if (call.method == 'getProductInfo') return products;
              if (call.method == 'purchaseProduct')
                throw PlatformException(code: '1');
              return null;
            });
        try {
          final provider = RevenueCatBookProvider();
          await provider.identify(
            platform == TargetPlatform.iOS
                ? 'appl_synthetic'
                : 'goog_synthetic',
            '1',
          );
          for (final id in [9, 42, 125, 1001]) {
            products = [
              {
                'identifier': 'shelf_book_$id',
                'description': 'Fixture',
                'title': 'Future book',
                'price': 4.49,
                'priceString': '€4.49',
                'currencyCode': 'EUR',
                'productCategory': 'NON_SUBSCRIPTION',
              },
            ];
            final product = await provider.product('shelf_book_$id');
            expect(product?.price, '€4.49');
            expect(product?.native?.identifier, 'shelf_book_$id');
            final lookup = calls.lastWhere((c) => c.method == 'getProductInfo');
            expect(lookup.arguments['productIdentifiers'], ['shelf_book_$id']);
            expect(lookup.arguments['type'], 'nonSubscription');
            await provider.buy(product!);
            expect(
              calls
                  .lastWhere((c) => c.method == 'purchaseProduct')
                  .arguments['productIdentifier'],
              'shelf_book_$id',
            );
          }
          products = [];
          expect(await provider.product('shelf_book_125'), null);
          products = [
            {
              'identifier': 'shelf_book_999',
              'description': '',
              'title': '',
              'price': 4.49,
              'priceString': '€4.49',
              'currencyCode': 'EUR',
            },
          ];
          expect(await provider.product('shelf_book_125'), null);
          products[0]['identifier'] = 'shelf_book_125';
          products[0]['priceString'] = '';
          expect(await provider.product('shelf_book_125'), null);
          products[0]['priceString'] = '€4.49';
          products.add({...products[0]});
          expect(await provider.product('shelf_book_125'), null);
          final before = calls.length;
          expect(await provider.product('unlock_all'), null);
          expect(await provider.product('pitswal_unlock_all_v1'), null);
          expect(calls.length, before);
          expect(
            await provider.buy(
              const StoreBookProduct('shelf_book_125', '€4.49'),
            ),
            StorePurchaseResult.failed,
          );
        } finally {
          TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
              .setMockMethodCallHandler(channel, null);
          debugDefaultTargetPlatformOverride = null;
        }
      },
    );
  }

  test('platform mapping survives cache and an explicit missing mapping never falls back', () {
    final future = PoetryCollection.fromJson({
      ...book(125).toJson(),
      'store_product_ids': {'APP_STORE': null, 'PLAY_STORE': 'shelf_book_125'},
    });
    final cached = PoetryCollection.fromJson(future.toJson());
    expect(cached.productIdForStore('APP_STORE'), null);
    expect(cached.productIdForStore('PLAY_STORE'), 'shelf_book_125');
    expect(book(125).productIdForStore('APP_STORE'), 'shelf_book_125');
  });

  testWidgets(
    'missing future product shows availability and no purchase button',
    (tester) async {
      debugDefaultTargetPlatformOverride = TargetPlatform.iOS;
      final accounts = AccountController(
        service: FakeAccounts(),
        store: MemoryTokens(),
        google: FakeGoogle(),
      );
      await accounts.initialize();
      await accounts.signIn('ordinary@example.test', 'synthetic');
      final api = CustomerApi()..allowed.add(125);
      final provider = MissingStore();
      final library = LibraryController(
        accounts: accounts,
        service: api,
        provider: provider,
        downloads: MemoryDownloads(),
      );
      try {
        SharedPreferences.setMockInitialValues({});
        final settings = await ReaderSettings.load(
          await SharedPreferences.getInstance(),
        );
        await library.product(book(125));
        final action = OutlinedButton(
          key: const Key('future-buy'),
          onPressed: () {},
          child: const Text('Buy book'),
        );
        Widget host(Widget child) => AccountScope(
          controller: accounts,
          child: LibraryScope(
            controller: library,
            child: MaterialApp(home: Scaffold(body: child)),
          ),
        );
        await tester.pumpWidget(
          host(BookPriceLabel(book: book(125), purchaseAction: action)),
        );
        await tester.pumpAndSettle();
        expect(find.byKey(const Key('future-buy')), findsNothing);
        expect(find.text('دا کتاب لا د پېرودلو لپاره نشته.'), findsOneWidget);
        provider.unavailable = false;
        await tester.pumpWidget(
          host(
            BookPriceLabel(
              key: const ValueKey('available'),
              book: book(125),
              purchaseAction: action,
            ),
          ),
        );
        await tester.pumpAndSettle();
        expect(find.byKey(const Key('future-buy')), findsOneWidget);
        expect(find.text('€4.49'), findsOneWidget);
        provider.unavailable = true;
        await tester.pumpWidget(
          host(PurchaseScreen(book: book(125), settings: settings)),
        );
        await tester.pumpAndSettle();
        expect(find.byKey(const Key('purchase-buy')), findsNothing);
        expect(find.text('Check availability'), findsOneWidget);
        expect(provider.buys, 0);
      } finally {
        await tester.pumpWidget(const SizedBox.shrink());
        library.dispose();
        accounts.dispose();
        debugDefaultTargetPlatformOverride = null;
      }
    },
  );
}
