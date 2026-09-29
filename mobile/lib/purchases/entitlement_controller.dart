import 'package:flutter/foundation.dart';

// Purchases are not available until verified, per-book ownership is implemented.
// This compatibility shell never calls a provider or grants global access.
enum EntitlementStatus { unknown, notEntitled, entitled, error }

enum PurchaseOutcome { entitled, cancelled, notEntitled, error }

class PurchaseProduct {
  const PurchaseProduct({required this.identifier, required this.price});
  final String identifier;
  final String price;
}

class PurchaseSnapshot {
  const PurchaseSnapshot({required this.entitled});
  final bool entitled;
}

abstract interface class PurchaseProvider {
  Future<void> configure();
  Future<String?> appUserId();
  Future<PurchaseProduct?> product();
  Future<PurchaseSnapshot> customerInfo();
  Future<PurchaseSnapshot> purchase();
  Future<PurchaseSnapshot> restore();
}

class EntitlementController extends ChangeNotifier {
  EntitlementController(PurchaseProvider provider);
  EntitlementStatus get status => EntitlementStatus.notEntitled;
  PurchaseProduct? get product => null;
  String? get userId => null;
  bool get busy => false;
  bool get entitled => false;
  Future<void> initialize() async {}
  Future<PurchaseOutcome> purchase() async => PurchaseOutcome.notEntitled;
  Future<PurchaseOutcome> restore() async => PurchaseOutcome.notEntitled;
}

class InactivePurchaseProvider implements PurchaseProvider {
  const InactivePurchaseProvider();
  @override
  Future<void> configure() async {}
  @override
  Future<String?> appUserId() async => null;
  @override
  Future<PurchaseProduct?> product() async => null;
  @override
  Future<PurchaseSnapshot> customerInfo() async =>
      const PurchaseSnapshot(entitled: false);
  @override
  Future<PurchaseSnapshot> purchase() async =>
      const PurchaseSnapshot(entitled: false);
  @override
  Future<PurchaseSnapshot> restore() async =>
      const PurchaseSnapshot(entitled: false);
}

PurchaseProvider createPurchaseProvider({
  required bool qaMode,
  required String publicSdkKey,
}) => const InactivePurchaseProvider();
bool debugPurchaseProviderAllowed({
  required bool productBuild,
  required bool requestedQa,
}) => false;
