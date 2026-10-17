import 'package:flutter/services.dart';

abstract interface class AvatarPicker {
  Future<Uint8List?> pick();
}

class PlatformAvatarPicker implements AvatarPicker {
  const PlatformAvatarPicker();
  static const _channel = MethodChannel('services.shelf.app/avatar');
  @override
  Future<Uint8List?> pick() => _channel.invokeMethod<Uint8List>('pick');
}
