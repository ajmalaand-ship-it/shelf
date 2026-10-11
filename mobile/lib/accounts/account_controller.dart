import 'dart:convert';
import 'dart:typed_data';

import 'package:flutter/widgets.dart';

import 'account_service.dart';

class AccountController extends ChangeNotifier {
  AccountController({
    required this.service,
    required this.store,
    required this.google,
    this.identityStore,
  });
  final AccountService service;
  final AccountTokenStore store;
  final GoogleAccountProvider google;
  final AccountIdentityStore? identityStore;
  AccountConfiguration configuration = const AccountConfiguration();
  ReaderAccount? user;
  Uint8List? avatar;

  Future<void> loadAvatar() async {
    final generation = _generation, token = _token;
    final id = user?.id;
    avatar = null;
    if (token == null || user?.hasAvatar != true) return;
    try {
      final response = await service.request(
        "avatar",
        method: "GET",
        token: token,
      );
      if (generation == _generation && token == _token && id == user?.id) {
        final photo = response["photo"];
        if (photo is String && photo.length <= 2796204)
          avatar = base64Decode(photo);
        notifyListeners();
      }
    } catch (_) {
      /* Account remains usable when a photo cannot load. */
    }
  }

  Future<void> replaceAvatar(Uint8List? bytes) => _run(() async {
    if (_token == null) throw const AccountFailure(401);
    if (bytes != null && bytes.length > 2 * 1024 * 1024)
      throw const AccountFailure(422);
    final generation = _generation, token = _token;
    final response = await service.request(
      "avatar",
      method: bytes == null ? "DELETE" : "POST",
      token: token,
      data: bytes == null ? const {} : {"photo": base64Encode(bytes)},
    );
    if (generation != _generation || token != _token) return;
    user = ReaderAccount.fromJson(response["user"] as Map<String, dynamic>);
    await loadAvatar();
  });
  String? _token;
  String? get readerToken => _token;
  bool busy = false, initialized = false;
  int _generation = 0;

  Future<void> initialize() async {
    if (busy) return;
    final generation = ++_generation;
    final savedToken = await store.read();
    final offlineIdentity = savedToken == null
        ? null
        : await identityStore?.read(savedToken);
    AccountConfiguration config;
    try {
      config = await service.configuration();
    } catch (_) {
      if (offlineIdentity == null) rethrow;
      config = const AccountConfiguration(enabled: true);
    }
    if (generation != _generation) return;
    configuration = config;
    _token = savedToken;
    user = null;
    if (savedToken != null && config.enabled) {
      try {
        final data = await service.request(
          'me',
          method: 'GET',
          token: savedToken,
        );
        if (generation == _generation)
          user = ReaderAccount.fromJson(data['user'] as Map<String, dynamic>);
        if (generation == _generation && user != null)
          await identityStore?.write(savedToken, user!);
      } on AccountFailure catch (error) {
        if (generation != _generation) return;
        if (error.status == 401 || error.status == 403)
          await _clear();
        else if (offlineIdentity != null)
          user = offlineIdentity;
        else
          rethrow;
      } catch (_) {
        if (generation != _generation) return;
        if (offlineIdentity == null) rethrow;
        user = offlineIdentity;
      }
    }
    if (generation == _generation) {
      await loadAvatar();
      initialized = true;
      notifyListeners();
    }
  }

  Future<void> _clear() async {
    user = null;
    avatar = null;
    _token = null;
    await store.clear();
    await identityStore?.clear();
    notifyListeners();
  }

  Future<void> _run(Future<void> Function() action) async {
    if (busy) return;
    busy = true;
    notifyListeners();
    try {
      await action();
    } on AccountFailure catch (error) {
      if (error.status == 401 && _token != null) await _clear();
      rethrow;
    } finally {
      busy = false;
      notifyListeners();
    }
  }

  Future<void> refresh() => _run(() async {
    final generation = _generation;
    final token = _token;
    if (token == null) return;
    final data = await service.request('me', method: 'GET', token: token);
    if (generation == _generation)
      user = ReaderAccount.fromJson(data['user'] as Map<String, dynamic>);
    await loadAvatar();
  });
  Future<void> _authenticate(String path, Map<String, dynamic> data) =>
      _run(() async {
        ++_generation;
        // Explicitly revoke the old identity before accepting a replacement.
        if (_token != null) await service.request('logout', token: _token);
        await _clear();
        final response = await service.request(path, data: data);
        if (response['email_confirmation_required'] == true)
          throw const AccountFailure(202);
        final token = response['token'] as String;
        final profile = ReaderAccount.fromJson(
          response['user'] as Map<String, dynamic>,
        );
        try {
          await store.write(token);
          await identityStore?.write(token, profile);
        } catch (_) {
          await service.request('logout', token: token);
          rethrow;
        }
        _token = token;
        user = profile;
        await loadAvatar();
      });
  Future<void> signIn(String email, String password) =>
      _authenticate('login', {'email': email, 'password': password});
  Future<void> googleSignIn() async {
    if (!configuration.googleEnabled || configuration.webClientId == null)
      throw const AccountFailure(404);
    // Remove a previous Google picker session so account switching is explicit.
    await google.signOut();
    final idToken = await google.idToken(configuration.webClientId!);
    await _authenticate('google', {'id_token': idToken});
  }

  Future<void> register(
    String email,
    String password,
    String confirmation,
    String name,
  ) => _run(() async {
    await service.request(
      'register',
      data: {
        'email': email,
        'password': password,
        'password_confirmation': confirmation,
        if (name.trim().isNotEmpty) 'name': name.trim(),
      },
    );
  });
  Future<void> forgotPassword(String email) => _run(() async {
    await service.request('forgot-password', data: {'email': email});
  });
  Future<void> resendVerification() => _run(() async {
    await service.request('verify/resend', token: _token);
  });
  Future<void> requestDeletion() => _run(() async {
    await service.request('delete-request', token: _token);
  });
  Future<void> changePassword(
    String current,
    String password,
    String confirmation,
  ) => _run(() async {
    await service.request(
      'password',
      token: _token,
      data: {
        'current_password': current,
        'password': password,
        'password_confirmation': confirmation,
      },
    );
    ++_generation;
    await _clear();
    await google.signOut();
  });
  Future<void> signOut() => _run(() async {
    if (_token != null) await service.request('logout', token: _token);
    ++_generation;
    await _clear();
    await google.signOut();
  });
}

class AccountScope extends InheritedNotifier<AccountController> {
  const AccountScope({
    required AccountController? controller,
    required super.child,
    super.key,
  }) : super(notifier: controller);
  static AccountController? of(BuildContext context) =>
      context.dependOnInheritedWidgetOfExactType<AccountScope>()?.notifier;
}
