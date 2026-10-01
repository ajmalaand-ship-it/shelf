import 'dart:convert';
import 'dart:typed_data';

import 'package:http/http.dart' as http;

import '../accounts/account_service.dart';
import '../services/api_config.dart';

abstract interface class LibraryService {
  Future<Map<String, dynamic>> request(
    String path, {
    String method = 'GET',
    String? token,
    Map<String, dynamic>? data,
  });
  Future<Uint8List> media(String url, String token);
}

class HttpLibraryService implements LibraryService {
  HttpLibraryService({http.Client? client, Uri? base})
    : _client = client ?? http.Client(),
      _base = base ?? apiBaseUri();
  final http.Client _client;
  final Uri _base;
  Uri _uri(String path) {
    final uri = _base.resolve(path);
    if (uri.origin != _base.origin ||
        !uri.path.startsWith(_base.path) ||
        uri.userInfo.isNotEmpty)
      throw const AccountFailure(400);
    return uri;
  }

  Future<http.Response> _send(
    String path,
    String method,
    String? token,
    Map<String, dynamic>? data,
  ) async {
    final request = http.Request(method, _uri(path))..followRedirects = false;
    request.headers.addAll({
      ...shelfTestHeaders(request.url),
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    });
    if (data != null) {
      request.headers['Content-Type'] = 'application/json';
      request.body = jsonEncode(data);
    }
    final response = await http.Response.fromStream(
      await _client.send(request).timeout(const Duration(seconds: 20)),
    ).timeout(const Duration(seconds: 60));
    if (response.statusCode < 200 || response.statusCode >= 300)
      throw AccountFailure(response.statusCode);
    return response;
  }

  @override
  Future<Map<String, dynamic>> request(
    String path, {
    String method = 'GET',
    String? token,
    Map<String, dynamic>? data,
  }) async => jsonDecode(
    utf8.decode((await _send(path, method, token, data)).bodyBytes),
  ) as Map<String, dynamic>;
  @override
  Future<Uint8List> media(String url, String token) async =>
      (await _send(url, 'GET', token, null)).bodyBytes;
}
