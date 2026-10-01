import 'package:flutter_test/flutter_test.dart';
import 'package:shelf/services/api_config.dart';

void main() {
  test('production refuses staging RevenueCat identity prefix', () {
    final prefix = shelfTestMode ? 'staging_' : '';
    expect(shelfPurchaseIdentity(7, prefix), '${prefix}7');
    expect(() => shelfPurchaseIdentity(7, shelfTestMode ? '' : 'staging_'), throwsFormatException);
    expect(shelfTestHeaders(Uri.parse('https://elsewhere.test/api/')), isEmpty);
  });
}
