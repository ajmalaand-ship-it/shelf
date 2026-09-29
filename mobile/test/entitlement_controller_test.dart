import 'package:flutter_test/flutter_test.dart';
import 'package:shelf/purchases/entitlement_controller.dart';

void main() {
  test(
    'legacy provider cannot be configured, purchased, restored or grant access',
    () async {
      final provider = _MustNotBeCalled();
      final controller = EntitlementController(provider);
      await controller.initialize();
      expect(controller.entitled, isFalse);
      expect(controller.product, isNull);
      expect(controller.userId, isNull);
      expect(await controller.purchase(), PurchaseOutcome.notEntitled);
      expect(await controller.restore(), PurchaseOutcome.notEntitled);
      expect(controller.entitled, isFalse);
    },
  );
  test('QA and configured SDK keys cannot activate old purchases', () {
    for (final qa in [true, false]) {
      expect(
        createPurchaseProvider(qaMode: qa, publicSdkKey: 'synthetic'),
        isA<InactivePurchaseProvider>(),
      );
      expect(
        debugPurchaseProviderAllowed(productBuild: false, requestedQa: qa),
        isFalse,
      );
    }
  });
}

class _MustNotBeCalled implements PurchaseProvider {
  @override
  Future<void> configure() async =>
      throw StateError('Unexpected provider call');
  @override
  Future<String?> appUserId() async =>
      throw StateError('Unexpected provider call');
  @override
  Future<PurchaseProduct?> product() async =>
      throw StateError('Unexpected provider call');
  @override
  Future<PurchaseSnapshot> customerInfo() async =>
      throw StateError('Unexpected provider call');
  @override
  Future<PurchaseSnapshot> purchase() async =>
      throw StateError('Unexpected provider call');
  @override
  Future<PurchaseSnapshot> restore() async =>
      throw StateError('Unexpected provider call');
}
