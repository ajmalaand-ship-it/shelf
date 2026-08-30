import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:pitswal/purchases/entitlement_controller.dart';
import 'package:pitswal/repository/poetry_repository.dart';
import 'package:pitswal/services/api_client.dart';

import 'test_support.dart';

void main() {
  test('API sends anonymous ID only to configured Pitswal API and can force refresh', () async {
    final provider = _MutableProvider()..entitled = true;
    final controller = EntitlementController(provider);
    await controller.initialize();
    late http.Request captured;
    final client = ApiClient(
      entitlements: controller,
      client: MockClient((request) async {
        captured = request;
        return http.Response.bytes(
          utf8.encode(jsonEncode({'data': poemDetailJson()})),
          200,
        );
      }),
      baseUri: Uri.parse('https://poetry.ajmalaand.com/api/'),
    );

    await client.getDataObject('poems/301', refreshEntitlement: true);

    expect(captured.url.host, 'poetry.ajmalaand.com');
    expect(captured.headers['X-RC-User-Id'], r'$RCAnonymousID:p5-test');
    expect(captured.headers['X-RC-Refresh'], '1');
  });

  test('unentitled context never reads paid full-text cache', () async {
    final cache = MemoryCacheStore();
    final provider = _MutableProvider();
    final controller = EntitlementController(provider);
    await controller.initialize();
    final online = PoetryRepository(
      entitlements: controller,
      cache: cache,
      api: ApiClient(
        entitlements: controller,
        client: MockClient((request) async {
          final paid = controller.entitled;
          return http.Response.bytes(
            utf8.encode(
              jsonEncode({
                'data': poemDetailJson(
                  locked: !paid,
                  body: paid ? 'PAID FULL TEXT' : null,
                ),
              }),
            ),
            200,
          );
        }),
      ),
    );
    expect((await online.loadPoem(301, 7)).locked, isTrue);
    provider.entitled = true;
    await controller.restore();
    expect((await online.loadPoem(301, 7)).body, 'PAID FULL TEXT');
    provider.entitled = false;
    await controller.initialize();

    final offline = PoetryRepository(
      entitlements: controller,
      cache: cache,
      api: ApiClient(
        entitlements: controller,
        client: MockClient((_) async => throw http.ClientException('offline')),
      ),
    );
    final poem = await offline.loadPoem(301, 7);

    expect(poem.locked, isTrue);
    expect(poem.body, isNull);
    expect(
      cache.values.keys.where((key) => key.contains('.paid.')),
      isNotEmpty,
    );
  });
}

class _MutableProvider implements PurchaseProvider {
  bool entitled = false;
  @override
  Future<String?> appUserId() async => r'$RCAnonymousID:p5-test';
  @override
  Future<void> configure() async {}
  @override
  Future<PurchaseSnapshot> customerInfo() async =>
      PurchaseSnapshot(entitled: entitled);
  @override
  Future<PurchaseProduct?> product() async =>
      const PurchaseProduct(identifier: revenueCatProductId, price: '€4.99');
  @override
  Future<PurchaseSnapshot> purchase() async =>
      PurchaseSnapshot(entitled: entitled);
  @override
  Future<PurchaseSnapshot> restore() async =>
      PurchaseSnapshot(entitled: entitled);
}
