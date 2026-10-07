/// Override with --dart-define=SHELF_API_BASE_URL=https://host/api/.
const shelfApiBaseUrl = String.fromEnvironment(
  'SHELF_API_BASE_URL',
  defaultValue: 'https://shelf.services/api/',
);

const shelfTestMode = bool.fromEnvironment('SHELF_TEST_MODE');
// Enables owner license-test checkout only; never classifies a transaction.
const shelfInternalTestPurchases = shelfTestMode ||
    bool.fromEnvironment('SHELF_INTERNAL_TEST_PURCHASES');
const _testAccessKey = String.fromEnvironment('SHELF_STAGING_ACCESS_KEY');

Map<String, String> shelfTestHeaders(Uri target) {
  if (!shelfTestMode || _testAccessKey.isEmpty) return const {};
  final base = apiBaseUri();
  if (target.origin != base.origin ||
      !(target.path.startsWith(base.path) || target.path.startsWith('/media/'))) return const {};
  return {'X-Shelf-Test-Key': _testAccessKey};
}

String shelfPurchaseIdentity(int reader, String prefix) {
  final expected = shelfTestMode ? 'staging_' : '';
  if (prefix != expected) throw const FormatException('Wrong purchase environment');
  return '$prefix$reader';
}

Uri apiBaseUri([String value = shelfApiBaseUrl]) {
  final uri = Uri.parse(value);
  if ((uri.scheme != 'https' && uri.scheme != 'http') ||
      uri.host.isEmpty ||
      uri.userInfo.isNotEmpty ||
      uri.hasQuery ||
      uri.hasFragment) {
    throw const FormatException('Invalid SHELF_API_BASE_URL');
  }
  return uri.replace(path: uri.path.endsWith('/') ? uri.path : '${uri.path}/');
}

Uri ownerPreviewBaseUri([String value = shelfApiBaseUrl]) =>
    apiBaseUri(value).resolve('owner-preview/');
