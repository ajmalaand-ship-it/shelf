import 'dart:async';
import 'dart:convert';
import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shelf/accounts/account_controller.dart';
import 'package:shelf/accounts/account_screen.dart';
import 'package:shelf/accounts/account_service.dart';
import 'package:shelf/accounts/avatar_picker.dart';

import 'accounts_test.dart' show MemoryTokens, FakeGoogle;

class Photos implements AccountService {
  String? photo;
  Completer<Map<String, dynamic>>? pending;
  Map<String, dynamic> profile() => {
    'id': 1,
    'email': 'reader@example.test',
    'email_verified': true,
    'sign_in_method': 'email',
    'has_avatar': photo != null,
  };
  @override
  Future<AccountConfiguration> configuration() async =>
      const AccountConfiguration(enabled: true);
  @override
  Future<Map<String, dynamic>> request(
    String path, {
    String method = 'POST',
    Map<String, dynamic> data = const {},
    String? token,
  }) async {
    if (path == 'login')
      return {'token': 'private-test-token', 'user': profile()};
    if (path == 'avatar') {
      if (method == 'GET')
        return pending?.future ?? Future.value({'photo': photo});
      photo = method == 'DELETE' ? null : data['photo'] as String;
      return {'user': profile()};
    }
    return {'user': profile()};
  }
}

class Picker implements AvatarPicker {
  @override
  Future<Uint8List?> pick() async => null;
}

void main() {
  test('photo upload/removal uses authenticated account and no local photo persists after sign out', () async {
    final service = Photos();
    final controller = AccountController(
      service: service,
      store: MemoryTokens(),
      google: FakeGoogle(),
    );
    await controller.signIn('reader@example.test', 'synthetic');
    await controller.replaceAvatar(Uint8List.fromList([1, 2, 3]));
    expect(controller.avatar, [1, 2, 3]);
    expect(controller.user!.hasAvatar, true);
    await controller.replaceAvatar(null);
    expect(service.photo, isNull);
    expect(controller.avatar, isNull);
    await controller.replaceAvatar(Uint8List.fromList([4]));
    await controller.signOut();
    expect(controller.avatar, isNull);
    expect(controller.user, isNull);
  });
  test('in-flight photo read cannot repopulate a signed-out session', () async {
    final service = Photos();
    final controller = AccountController(
      service: service,
      store: MemoryTokens(),
      google: FakeGoogle(),
    );
    await controller.signIn('reader@example.test', 'synthetic');
    controller.user = ReaderAccount(
      id: 1,
      email: 'reader@example.test',
      verified: true,
      method: 'email',
      hasAvatar: true,
    );
    service.pending = Completer();
    final load = controller.loadAvatar();
    await controller.signOut();
    service.pending!.complete({
      'photo': base64Encode([1, 2, 3]),
    });
    await load;
    expect(controller.avatar, isNull);
  });
  testWidgets(
    'picker cancellation changes nothing and has visible private photo actions',
    (t) async {
      final service = Photos();
      final controller = AccountController(
        service: service,
        store: MemoryTokens(),
        google: FakeGoogle(),
      );
      await controller.initialize();
      await controller.signIn('reader@example.test', 'synthetic');
      await t.pumpWidget(
        MaterialApp(
          home: AccountScope(
            controller: controller,
            child: AccountScreen(avatarPicker: Picker()),
          ),
        ),
      );
      await t.pumpAndSettle();
      await t.tap(find.byKey(const Key('avatar-choose')));
      await t.pumpAndSettle();
      expect(service.photo, isNull);
      expect(controller.user!.hasAvatar, false);
      expect(find.textContaining('Private account photo'), findsOneWidget);
      expect(t.takeException(), isNull);
    },
  );
}
