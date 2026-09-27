/// Override with --dart-define=SHELF_API_BASE_URL=https://host/api/.
const shelfApiBaseUrl = String.fromEnvironment(
  'SHELF_API_BASE_URL',
  defaultValue: 'https://shelf.services/api/',
);

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
