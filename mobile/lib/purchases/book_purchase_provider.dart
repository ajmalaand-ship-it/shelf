import 'package:flutter/services.dart';
import '../services/api_config.dart';
import 'package:purchases_flutter/purchases_flutter.dart';

class StoreBookProduct {
  const StoreBookProduct(this.id, this.price, [this.native]);
  final String id, price;
  final StoreProduct? native;
}

enum StorePurchaseResult { confirming, cancelled, pending, failed }

abstract interface class BookPurchaseProvider {
  Future<void> identify(String key, String readerId);
  Future<StoreBookProduct?> product(String id);
  Future<StorePurchaseResult> buy(StoreBookProduct product);
  Future<void> restore();
  Future<void> signOut();
}

class RevenueCatBookProvider implements BookPurchaseProvider {
  String? _key, _reader;
  @override
  Future<void> identify(String key, String readerId) async {
    if (!key.startsWith('goog_') ||
        !RegExp(shelfTestMode ? r'^staging_[1-9][0-9]*$' : r'^[1-9][0-9]*$').hasMatch(readerId)) {
      throw StateError('Shelf purchase configuration is unavailable');
    }
    if (_key == null) {
      await Purchases.setLogLevel(LogLevel.error);
      await Purchases.configure(
        PurchasesConfiguration(key)..appUserID = readerId,
      );
      _key = key;
    } else {
      if (_key != key)
        throw StateError('Purchase project changed; restart Shelf');
      if (_reader != readerId) await Purchases.logIn(readerId);
    }
    _reader = readerId;
    if (await Purchases.appUserID != readerId)
      throw StateError('Purchase account mismatch');
  }

  @override
  Future<StoreBookProduct?> product(String id) async {
    if (_reader == null || !RegExp(r'^shelf_book_[1-9][0-9]*$').hasMatch(id))
      return null;
    final products = await Purchases.getProducts([
      id,
    ], productCategory: ProductCategory.nonSubscription);
    final matches = products.where((p) => p.identifier == id);
    return matches.isEmpty
        ? null
        : StoreBookProduct(id, matches.first.priceString, matches.first);
  }

  @override
  Future<StorePurchaseResult> buy(StoreBookProduct product) async {
    if (_reader == null ||
        await Purchases.appUserID != _reader ||
        product.native == null)
      return StorePurchaseResult.failed;
    try {
      await Purchases.purchase(PurchaseParams.storeProduct(product.native!));
      // CustomerInfo never unlocks a book; only Shelf server confirmation can.
      return StorePurchaseResult.confirming;
    } on PlatformException catch (error) {
      final code = PurchasesErrorHelper.getErrorCode(error);
      if (code == PurchasesErrorCode.purchaseCancelledError)
        return StorePurchaseResult.cancelled;
      if (code == PurchasesErrorCode.paymentPendingError)
        return StorePurchaseResult.pending;
      return StorePurchaseResult.failed;
    }
  }

  @override
  Future<void> restore() async {
    if (_reader == null || await Purchases.appUserID != _reader)
      throw StateError('Sign in first');
    await Purchases.restorePurchases();
  }

  @override
  Future<void> signOut() async {
    if (_key != null && _reader != null) await Purchases.logOut();
    _reader = null;
  }
}
