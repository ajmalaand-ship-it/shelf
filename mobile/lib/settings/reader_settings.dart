import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

enum ReaderPalette { light, sepia, dark }

enum ReaderFont { nastaliq, naskh }

class ReaderSettings extends ChangeNotifier {
  ReaderSettings._(
    this._preferences,
    this._palette,
    this._font,
    this._fontSize,
  );

  static const _paletteKey = 'reader.palette';
  static const _fontKey = 'reader.font';
  static const _fontSizeKey = 'reader.font_size';

  final SharedPreferences _preferences;
  ReaderPalette _palette;
  ReaderFont _font;
  double _fontSize;

  ReaderPalette get palette => _palette;
  ReaderFont get font => _font;
  double get fontSize => _fontSize;
  String get fontFamily =>
      _font == ReaderFont.nastaliq ? 'NotoNastaliqUrdu' : 'ScheherazadeNew';

  static Future<ReaderSettings> load(SharedPreferences preferences) async {
    final paletteName = preferences.getString(_paletteKey);
    final fontName = preferences.getString(_fontKey);
    final fontSize = preferences.getDouble(_fontSizeKey) ?? 24;
    return ReaderSettings._(
      preferences,
      ReaderPalette.values.where((v) => v.name == paletteName).firstOrNull ??
          ReaderPalette.light,
      ReaderFont.values.where((v) => v.name == fontName).firstOrNull ??
          ReaderFont.nastaliq,
      fontSize.clamp(18, 38),
    );
  }

  Future<void> setPalette(ReaderPalette value) async {
    _palette = value;
    notifyListeners();
    await _preferences.setString(_paletteKey, value.name);
  }

  Future<void> setFont(ReaderFont value) async {
    _font = value;
    notifyListeners();
    await _preferences.setString(_fontKey, value.name);
  }

  Future<void> setFontSize(double value) async {
    _fontSize = value.clamp(18, 38);
    notifyListeners();
    await _preferences.setDouble(_fontSizeKey, _fontSize);
  }
}

extension<T> on Iterable<T> {
  T? get firstOrNull => isEmpty ? null : first;
}
