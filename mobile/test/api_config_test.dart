import 'package:flutter_test/flutter_test.dart';
import 'package:shelf/services/api_client.dart';
import 'package:shelf/services/api_config.dart';

void main() {
  test('public and owner preview use the same compile-time base', () {
    final base = apiBaseUri(shelfApiBaseUrl);
    expect(ApiClient().baseUri, base);
    expect(ownerPreviewBaseUri(), base.resolve('owner-preview/'));
  });

  test('custom base preserves its API path with or without a trailing slash', () {
    for (final value in [
      'https://example.test/custom/api',
      'https://example.test/custom/api/',
    ]) {
      expect(
        apiBaseUri(value).resolve('collections').toString(),
        'https://example.test/custom/api/collections',
      );
      expect(
        ownerPreviewBaseUri(value).resolve('collections').toString(),
        'https://example.test/custom/api/owner-preview/collections',
      );
    }
  });

  test('invalid or credential-bearing base URLs are rejected', () {
    for (final value in [
      '',
      '/api/',
      'ftp://example.test/api/',
      'https://user:password@example.test/api/',
      'https://example.test/api/?token=fixture',
      'https://example.test/api/#fragment',
    ]) {
      expect(() => apiBaseUri(value), throwsFormatException);
    }
  });
}
