import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:purchases_flutter/purchases_flutter.dart';

const revenueCatEntitlementId = 'unlock_all';
const revenueCatProductId = 'pitswal_unlock_all_v1';
const revenueCatOfferingId = 'default';

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

class RevenueCatPurchaseProvider implements PurchaseProvider {
  RevenueCatPurchaseProvider(this.publicSdkKey);
  final String publicSdkKey;
  Package? _package;

  @override
  Future<void> configure() => Purchases.configure(
    PurchasesConfiguration(publicSdkKey)
      ..automaticDeviceIdentifierCollectionEnabled = false,
  );

  @override
  Future<String?> appUserId() => Purchases.appUserID;

  @override
  Future<PurchaseProduct?> product() async {
    final offering = (await Purchases.getOfferings()).getOffering(
      revenueCatOfferingId,
    );
    if (offering == null) return null;
    final package = offering.availablePackages.cast<Package?>().firstWhere(
      (item) => item?.storeProduct.identifier == revenueCatProductId,
      orElse: () => offering.lifetime,
    );
    if (package == null ||
        package.storeProduct.identifier != revenueCatProductId) {
      return null;
    }
    _package = package;
    return PurchaseProduct(
      identifier: package.storeProduct.identifier,
      price: package.storeProduct.priceString,
    );
  }

  @override
  Future<PurchaseSnapshot> customerInfo() async =>
      _snapshot(await Purchases.getCustomerInfo());

  @override
  Future<PurchaseSnapshot> purchase() async {
    final package = _package;
    if (package == null) throw StateError('Purchase product is unavailable');
    final result = await Purchases.purchase(PurchaseParams.package(package));
    return _snapshot(result.customerInfo);
  }

  @override
  Future<PurchaseSnapshot> restore() async =>
      _snapshot(await Purchases.restorePurchases());

  PurchaseSnapshot _snapshot(CustomerInfo info) => PurchaseSnapshot(
    entitled: info.entitlements.active.containsKey(revenueCatEntitlementId),
  );
}

class EntitlementController extends ChangeNotifier {
  EntitlementController(this._provider);

  final PurchaseProvider _provider;
  EntitlementStatus status = EntitlementStatus.unknown;
  PurchaseProduct? product;
  String? userId;
  bool busy = false;
  String? message;

  bool get entitled => status == EntitlementStatus.entitled;

  Future<void> initialize() async {
    try {
      await _provider.configure();
      userId = await _provider.appUserId();
      product = await _provider.product();
      _apply(await _provider.customerInfo());
    } catch (_) {
      status = EntitlementStatus.error;
      message = 'د پېر معلومات اوس ترلاسه نه شول.';
      notifyListeners();
    }
  }

  Future<PurchaseOutcome> purchase() => _run(() => _provider.purchase());

  Future<PurchaseOutcome> restore() => _run(() => _provider.restore());

  Future<PurchaseOutcome> _run(
    Future<PurchaseSnapshot> Function() action,
  ) async {
    busy = true;
    message = null;
    notifyListeners();
    try {
      final snapshot = await action();
      _apply(snapshot);
      return snapshot.entitled
          ? PurchaseOutcome.entitled
          : PurchaseOutcome.notEntitled;
    } on PlatformException catch (error) {
      if (PurchasesErrorHelper.getErrorCode(error) ==
          PurchasesErrorCode.purchaseCancelledError) {
        return PurchaseOutcome.cancelled;
      }
      status = EntitlementStatus.error;
      message = 'د پلورنځي غوښتنه بشپړه نه شوه.';
      notifyListeners();
      return PurchaseOutcome.error;
    } catch (_) {
      status = EntitlementStatus.error;
      message = 'د پلورنځي غوښتنه بشپړه نه شوه.';
      notifyListeners();
      return PurchaseOutcome.error;
    } finally {
      busy = false;
      notifyListeners();
    }
  }

  void _apply(PurchaseSnapshot snapshot) {
    status = snapshot.entitled
        ? EntitlementStatus.entitled
        : EntitlementStatus.notEntitled;
    notifyListeners();
  }
}

class InactivePurchaseProvider implements PurchaseProvider {
  const InactivePurchaseProvider();
  @override
  Future<String?> appUserId() async => null;
  @override
  Future<void> configure() async {}
  @override
  Future<PurchaseSnapshot> customerInfo() async =>
      const PurchaseSnapshot(entitled: false);
  @override
  Future<PurchaseProduct?> product() async => null;
  @override
  Future<PurchaseSnapshot> purchase() async =>
      throw StateError('RevenueCat is not configured');
  @override
  Future<PurchaseSnapshot> restore() async =>
      throw StateError('RevenueCat is not configured');
}

PurchaseProvider createPurchaseProvider({
  required bool qaMode,
  required String publicSdkKey,
}) {
  if (debugPurchaseProviderAllowed(
    productBuild: kReleaseMode,
    requestedQa: qaMode,
  )) {
    return DebugPurchaseProvider();
  }
  if (publicSdkKey.isEmpty) return const InactivePurchaseProvider();
  return RevenueCatPurchaseProvider(publicSdkKey);
}

bool debugPurchaseProviderAllowed({
  required bool productBuild,
  required bool requestedQa,
}) => !productBuild && requestedQa;

class DebugPurchaseProvider implements PurchaseProvider {
  bool entitled = false;
  @override
  Future<String?> appUserId() async => r'$RCAnonymousID:debug-payment-fixture';
  @override
  Future<void> configure() async {}
  @override
  Future<PurchaseSnapshot> customerInfo() async =>
      PurchaseSnapshot(entitled: entitled);
  @override
  Future<PurchaseProduct?> product() async => const PurchaseProduct(
    identifier: revenueCatProductId,
    price: 'TEST — بيه نه ده',
  );
  @override
  Future<PurchaseSnapshot> purchase() async {
    entitled = true;
    return const PurchaseSnapshot(entitled: true);
  }

  @override
  Future<PurchaseSnapshot> restore() async =>
      PurchaseSnapshot(entitled: entitled);
}
