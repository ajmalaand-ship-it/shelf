import 'package:shared_preferences/shared_preferences.dart';

abstract interface class CacheStore {
  Future<String?> read(String key);
  Future<void> write(String key, String value);
  Future<void> remove(String key);
  Future<Set<String>> keys();
}

class PreferencesCacheStore implements CacheStore {
  PreferencesCacheStore(this.preferences);

  final SharedPreferences preferences;

  @override
  Future<Set<String>> keys() async => preferences.getKeys();

  @override
  Future<String?> read(String key) async => preferences.getString(key);

  @override
  Future<void> remove(String key) async {
    await preferences.remove(key);
  }

  @override
  Future<void> write(String key, String value) async {
    final written = await preferences.setString(key, value);
    if (!written) throw StateError('Unable to persist content cache');
  }
}
