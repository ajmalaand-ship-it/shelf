import 'dart:convert';

import 'package:http/http.dart' as http;

import '../purchases/entitlement_controller.dart';
import 'api_config.dart';

class ApiClient {
  ApiClient({
    http.Client? client,
    Uri? baseUri,
    this.entitlements,
    this.authorizationToken,
  }) : _client = client ?? http.Client(),
       baseUri = baseUri ?? apiBaseUri();

  final http.Client _client;
  final Uri baseUri;
  final EntitlementController? entitlements;
  final String? authorizationToken;

  Future<Map<String, dynamic>> getObject(
    String path, {
    bool refreshEntitlement = false,
  }) async {
    final value = await _getJson(path, refreshEntitlement: refreshEntitlement);
    if (value is! Map<String, dynamic>) {
      throw const FormatException('Expected a JSON object');
    }
    return value;
  }

  Future<List<Map<String, dynamic>>> getDataList(String path) async {
    final response = await getObject(path);
    final data = response['data'];
    if (data is! List) throw const FormatException('Expected a data list');
    return data
        .map((item) {
          if (item is! Map<String, dynamic>) {
            throw const FormatException('Expected an object in data list');
          }
          return item;
        })
        .toList(growable: false);
  }

  Future<Map<String, dynamic>> getDataObject(
    String path, {
    bool refreshEntitlement = false,
  }) async {
    final response = await getObject(
      path,
      refreshEntitlement: refreshEntitlement,
    );
    final data = response['data'];
    if (data is! Map<String, dynamic>) {
      throw const FormatException('Expected a data object');
    }
    return data;
  }

  Future<dynamic> _getJson(
    String path, {
    bool refreshEntitlement = false,
  }) async {
    final headers = <String, String>{'Accept': 'application/json'};
    if (authorizationToken case final token? when token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    final userId = entitlements?.userId;
    if (userId != null && userId.isNotEmpty) {
      headers['X-RC-User-Id'] = userId;
      if (refreshEntitlement) headers['X-RC-Refresh'] = '1';
    }
    final response = await _client
        .get(baseUri.resolve(path), headers: headers)
        .timeout(const Duration(seconds: 12));
    if (response.statusCode == 404) throw const ContentNotFoundException();
    if (response.statusCode < 200 || response.statusCode >= 300) {
      throw ApiException(response.statusCode);
    }
    try {
      return jsonDecode(utf8.decode(response.bodyBytes));
    } on FormatException {
      throw const FormatException('Server returned malformed JSON');
    }
  }
}

class ApiException implements Exception {
  const ApiException(this.statusCode);
  final int statusCode;
}

class ContentNotFoundException implements Exception {
  const ContentNotFoundException();
}
