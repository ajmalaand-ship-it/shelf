import 'dart:convert';

import 'package:http/http.dart' as http;

class ApiClient {
  ApiClient({http.Client? client, Uri? baseUri})
    : _client = client ?? http.Client(),
      baseUri = baseUri ?? Uri.parse('https://poetry.ajmalaand.com/api/');

  final http.Client _client;
  final Uri baseUri;

  Future<Map<String, dynamic>> getObject(String path) async {
    final value = await _getJson(path);
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

  Future<Map<String, dynamic>> getDataObject(String path) async {
    final response = await getObject(path);
    final data = response['data'];
    if (data is! Map<String, dynamic>) {
      throw const FormatException('Expected a data object');
    }
    return data;
  }

  Future<dynamic> _getJson(String path) async {
    final response = await _client
        .get(
          baseUri.resolve(path),
          headers: const {'Accept': 'application/json'},
        )
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
