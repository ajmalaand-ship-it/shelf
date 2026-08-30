import 'package:flutter_test/flutter_test.dart';
import 'package:flutter/services.dart';
import 'package:pitswal/purchases/entitlement_controller.dart';
import 'package:purchases_flutter/purchases_flutter.dart';

void main() {
  test(
    'unknown initializes to not entitled with localized product price',
    () async {
      final provider = _FakeProvider();
      final controller = EntitlementController(provider);
      expect(controller.status, EntitlementStatus.unknown);

      await controller.initialize();

      expect(controller.status, EntitlementStatus.notEntitled);
      expect(controller.product?.price, '€4.99');
      expect(controller.userId, startsWith(r'$RCAnonymousID:'));
    },
  );

  test(
    'purchase success is accepted only from active entitlement snapshot',
    () async {
      final provider = _FakeProvider(purchaseEntitled: true);
      final controller = EntitlementController(provider);
      await controller.initialize();

      expect(await controller.purchase(), PurchaseOutcome.entitled);
      expect(controller.entitled, isTrue);
    },
  );

  test('completed purchase without entitlement stays locked', () async {
    final controller = EntitlementController(_FakeProvider());
    await controller.initialize();

    expect(await controller.purchase(), PurchaseOutcome.notEntitled);
    expect(controller.entitled, isFalse);
  });

  test('restore reports success and no-purchase distinctly', () async {
    final restored = EntitlementController(
      _FakeProvider(restoreEntitled: true),
    );
    await restored.initialize();
    expect(await restored.restore(), PurchaseOutcome.entitled);

    final none = EntitlementController(_FakeProvider());
    await none.initialize();
    expect(await none.restore(), PurchaseOutcome.notEntitled);
  });

  test('provider errors are recoverable and do not grant access', () async {
    final controller = EntitlementController(_FakeProvider(fail: true));
    await controller.initialize();

    expect(controller.status, EntitlementStatus.error);
    expect(await controller.purchase(), PurchaseOutcome.error);
    expect(controller.entitled, isFalse);
  });

  test('user cancellation is distinct from provider failure', () async {
    final controller = EntitlementController(_FakeProvider(cancel: true));
    await controller.initialize();

    expect(await controller.purchase(), PurchaseOutcome.cancelled);
    expect(controller.entitled, isFalse);
  });

  test('release product builds reject requested QA entitlement provider', () {
    expect(
      debugPurchaseProviderAllowed(productBuild: true, requestedQa: true),
      isFalse,
    );
    expect(
      debugPurchaseProviderAllowed(productBuild: false, requestedQa: true),
      isTrue,
    );
  });
}

class _FakeProvider implements PurchaseProvider {
  _FakeProvider({
    this.purchaseEntitled = false,
    this.restoreEntitled = false,
    this.fail = false,
    this.cancel = false,
  });

  final bool purchaseEntitled;
  final bool restoreEntitled;
  final bool fail;
  final bool cancel;

  @override
  Future<void> configure() async {
    if (fail) throw StateError('provider unavailable');
  }

  @override
  Future<String?> appUserId() async => r'$RCAnonymousID:test-user';

  @override
  Future<PurchaseSnapshot> customerInfo() async =>
      const PurchaseSnapshot(entitled: false);

  @override
  Future<PurchaseProduct?> product() async =>
      const PurchaseProduct(identifier: revenueCatProductId, price: '€4.99');

  @override
  Future<PurchaseSnapshot> purchase() async {
    if (cancel) {
      throw PlatformException(
        code: PurchasesErrorCode.purchaseCancelledError.index.toString(),
      );
    }
    if (fail) throw StateError('purchase failed');
    return PurchaseSnapshot(entitled: purchaseEntitled);
  }

  @override
  Future<PurchaseSnapshot> restore() async {
    if (fail) throw StateError('restore failed');
    return PurchaseSnapshot(entitled: restoreEntitled);
  }
}
