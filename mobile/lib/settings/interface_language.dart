import 'package:flutter/widgets.dart';
import 'package:shared_preferences/shared_preferences.dart';

enum InterfaceLanguage { ps, en }

/// Interface preference only: book language and source text are never changed.
class InterfaceLanguageSettings extends ChangeNotifier {
  InterfaceLanguageSettings.load(this._preferences) {
    final saved = _preferences.getString(preferenceKey);
    _language = InterfaceLanguage.values
        .where((v) => v.name == saved)
        .firstOrNull;
  }
  static const preferenceKey = 'shelf.interface_language';
  final SharedPreferences _preferences;
  InterfaceLanguage? _language;
  bool _saving = false;
  InterfaceLanguage? get language => _language;
  bool get needsChoice => _language == null;
  bool get isEnglish => _language == InterfaceLanguage.en;
  bool get saving => _saving;

  Future<void> select(InterfaceLanguage language) async {
    if (_saving || language == _language) return;
    _saving = true;
    notifyListeners();
    try {
      if (!await _preferences.setString(preferenceKey, language.name)) {
        throw StateError('Language preference was not saved');
      }
      _language = language;
    } finally {
      _saving = false;
      notifyListeners();
    }
  }
}

class InterfaceLanguageScope
    extends InheritedNotifier<InterfaceLanguageSettings> {
  const InterfaceLanguageScope({
    required InterfaceLanguageSettings? settings,
    required super.child,
    super.key,
  }) : super(notifier: settings);
  static InterfaceLanguageSettings? of(BuildContext context) => context
      .dependOnInheritedWidgetOfExactType<InterfaceLanguageScope>()
      ?.notifier;
}
