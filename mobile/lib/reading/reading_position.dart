import 'dart:convert';

import 'package:crypto/crypto.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ReadingPosition {
  const ReadingPosition({
    required this.item,
    required this.offset,
    required this.fingerprint,
    this.details = false,
  });
  final int item, offset;
  final String fingerprint;
  final bool details;
  static String fingerprintOf(String source) =>
      sha256.convert(utf8.encode(source)).toString();
  Map<String, Object> toJson() => {
    'item': item,
    'offset': offset,
    'fingerprint': fingerprint,
    'details': details,
  };
  static ReadingPosition? parse(String? raw) {
    try {
      if (raw == null) return null;
      final data = jsonDecode(raw) as Map<String, dynamic>;
      if (data['item'] is! int ||
          data['offset'] is! int ||
          data['offset'] < 0 ||
          data['fingerprint'] is! String ||
          data['details'] is! bool) {
        return null;
      }
      return ReadingPosition(
        item: data['item'],
        offset: data['offset'],
        fingerprint: data['fingerprint'],
        details: data['details'],
      );
    } catch (_) {
      return null;
    }
  }
}

class ReadingPositions {
  ReadingPositions(this.preferences);
  final SharedPreferences preferences;
  String key({
    required String environment,
    required String account,
    required String book,
  }) =>
      'shelf.reading-position.v1.${Uri.encodeComponent(environment)}.${Uri.encodeComponent(account)}.${Uri.encodeComponent(book)}';
  ReadingPosition? read(String key) =>
      ReadingPosition.parse(preferences.getString(key));
  Future<bool> save(String key, ReadingPosition position) =>
      preferences.setString(key, jsonEncode(position.toJson()));
}
