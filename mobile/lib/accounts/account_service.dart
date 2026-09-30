import 'dart:convert';

import 'package:crypto/crypto.dart';

import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:google_sign_in/google_sign_in.dart';

import '../services/api_config.dart';

class ReaderAccount {
  const ReaderAccount({
    required this.id,
    required this.email,
    required this.verified,
    required this.method,
    this.name,
  });
  factory ReaderAccount.fromJson(Map<String, dynamic> json) => ReaderAccount(
    id: json['id'] as int,
    email: json['email'] as String,
    name: json['name'] as String?,
    verified: json['email_verified'] as bool,
    method: json['sign_in_method'] as String,
  );
  final int id;
  final String email;
  final String? name;
  final bool verified;
  final String method;
}

class AccountConfiguration {
  const AccountConfiguration({
    this.enabled = false,
    this.googleEnabled = false,
    this.webClientId,
    this.ownerTestingOnly = false,
  });
  final bool enabled, googleEnabled, ownerTestingOnly;
  final String? webClientId;
}

class AccountFailure implements Exception {
  const AccountFailure(this.status, [this.validationMessage]);
  final int status;
  final String? validationMessage;
}

abstract interface class AccountService {
  Future<AccountConfiguration> configuration();
  Future<Map<String, dynamic>> request(
    String path, {
    String method = 'POST',
    Map<String, dynamic> data = const {},
    String? token,
  });
}

class HttpAccountService implements AccountService {
  HttpAccountService({http.Client? client, Uri? base})
    : _client = client ?? http.Client(),
      _base = base ?? apiBaseUri();
  final http.Client _client;
  final Uri _base;
  @override
  Future<AccountConfiguration> configuration() async {
    final json = await request('config', method: 'GET');
    return AccountConfiguration(
      enabled: json['enabled'] == true,
      googleEnabled: json['google_enabled'] == true,
      webClientId: json['google_web_client_id'] as String?,
      ownerTestingOnly: json['owner_testing_only'] == true,
    );
  }

  @override
  Future<Map<String, dynamic>> request(
    String path, {
    String method = 'POST',
    Map<String, dynamic> data = const {},
    String? token,
  }) async {
    // This API is separate from the book/owner-preview API. Never share tokens.
    if (!RegExp(r'^[a-z/-]+$').hasMatch(path) || path.contains('..'))
      throw const AccountFailure(400);
    final req = http.Request(method, _base.resolve('auth/$path'))
      ..followRedirects = false;
    req.headers.addAll({
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    });
    if (method != 'GET') req.body = jsonEncode(data);
    final response = await http.Response.fromStream(
      await _client.send(req).timeout(const Duration(seconds: 15)),
    ).timeout(const Duration(seconds: 15));
    if (response.statusCode < 200 || response.statusCode >= 300) {
      String? validationMessage;
      if (response.statusCode == 422) {
        try {
          final payload = jsonDecode(utf8.decode(response.bodyBytes));
          final errors = payload['errors'];
          if (errors is Map) {
            final messages = <String>[];
            for (final field in [
              'email',
              'name',
              'password',
              'current_password',
            ]) {
              final values = errors[field];
              if (values is List) messages.addAll(values.whereType<String>());
            }
            if (messages.isNotEmpty) validationMessage = messages.join('\n');
          }
        } catch (_) {}
      }
      throw AccountFailure(response.statusCode, validationMessage);
    }
    if (response.statusCode == 204) return {};
    return jsonDecode(utf8.decode(response.bodyBytes)) as Map<String, dynamic>;
  }
}

abstract interface class AccountTokenStore {
  Future<String?> read();
  Future<void> write(String token);
  Future<void> clear();
}

class SecureAccountTokenStore implements AccountTokenStore {
  static const _key = 'shelf.reader.token';
  final FlutterSecureStorage _storage = const FlutterSecureStorage();
  @override
  Future<String?> read() => _storage.read(key: _key);
  @override
  Future<void> write(String token) => _storage.write(key: _key, value: token);
  @override
  Future<void> clear() => _storage.delete(key: _key);
}

abstract interface class GoogleAccountProvider {
  Future<String> idToken(String webClientId);
  Future<void> signOut();
}

abstract interface class AccountIdentityStore {
  Future<ReaderAccount?> read(String token);
  Future<void> write(String token, ReaderAccount account);
  Future<void> clear();
}

class SecureAccountIdentityStore implements AccountIdentityStore {
  final _storage = const FlutterSecureStorage();
  static const _key = 'shelf.reader.offline-identity';
  @override
  Future<ReaderAccount?> read(String token) async {
    final raw = await _storage.read(key: _key);
    if (raw == null) return null;
    try {
      final json = jsonDecode(raw) as Map<String, dynamic>;
      if (json['token_hash'] != sha256.convert(utf8.encode(token)).toString())
        return null;
      return ReaderAccount.fromJson(json['account'] as Map<String, dynamic>);
    } catch (_) {
      return null;
    }
  }

  @override
  Future<void> write(String token, ReaderAccount account) => _storage.write(
    key: _key,
    value: jsonEncode({
      'token_hash': sha256.convert(utf8.encode(token)).toString(),
      'account': {
        'id': account.id,
        'email': account.email,
        'name': account.name,
        'email_verified': account.verified,
        'sign_in_method': account.method,
      },
    }),
  );
  @override
  Future<void> clear() => _storage.delete(key: _key);
}

class PlatformGoogleAccountProvider implements GoogleAccountProvider {
  bool _initialized = false;
  @override
  Future<String> idToken(String webClientId) async {
    if (!_initialized) {
      await GoogleSignIn.instance.initialize(serverClientId: webClientId);
      _initialized = true;
    }
    final account = await GoogleSignIn.instance.authenticate();
    final token = account.authentication.idToken;
    if (token == null) throw const AccountFailure(401);
    return token;
  }

  @override
  Future<void> signOut() async {
    if (_initialized) await GoogleSignIn.instance.signOut();
  }
}
