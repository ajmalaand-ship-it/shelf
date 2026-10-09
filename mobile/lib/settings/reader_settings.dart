import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../reading/reading_position.dart';

enum ReadingMode { scroll, pages }

enum ReaderPalette { light, sepia, dark }

enum ReaderFont { vazirmatn, naskh, literary }

class ReaderSettings extends ChangeNotifier {
  ReaderSettings._(
    this._preferences,
    this._palette,
    this._font,
    this._fontSize,
    this._readingMode,
  );

  static const _readingModeKey = 'reader.mode';
  static const _paletteKey = 'reader.palette';
  static const _fontKey = 'reader.font';
  static const _fontSizeKey = 'reader.font_size';
  static const defaultFontSize = 16.0;
  static const minimumFontSize = 16.0;
  static const maximumFontSize = 38.0;

  final SharedPreferences _preferences;
  ReaderPalette _palette;
  ReaderFont _font;
  double _fontSize;
  ReadingMode _readingMode;
  ReadingMode get readingMode => _readingMode;
  ReadingPositions get positions => ReadingPositions(_preferences);

  Future<void> setReadingMode(ReadingMode mode) async {
    _readingMode = mode;
    notifyListeners();
    await _preferences.setString(_readingModeKey, mode.name);
  }

  ReaderPalette get palette => _palette;
  ReaderFont get font => _font;
  double get fontSize => _fontSize;
  String get fontFamily => switch (_font) {
    ReaderFont.vazirmatn => 'Vazirmatn',
    ReaderFont.naskh => 'ScheherazadeNew',
    ReaderFont.literary => 'NotoNastaliqUrdu',
  };

  static Future<ReaderSettings> load(SharedPreferences preferences) async {
    final paletteName = preferences.getString(_paletteKey);
    final fontName = preferences.getString(_fontKey);
    final savedFontSize = preferences.getDouble(_fontSizeKey);
    final font = switch (fontName) {
      'naskh' => ReaderFont.naskh,
      'nastaliq' => ReaderFont.literary,
      'literary' => ReaderFont.literary,
      'vazirmatn' => ReaderFont.vazirmatn,
      _ => ReaderFont.vazirmatn,
    };
    if (fontName != null && fontName != font.name) {
      await preferences.setString(_fontKey, font.name);
    }
    return ReaderSettings._(
      preferences,
      ReaderPalette.values.where((v) => v.name == paletteName).firstOrNull ??
          ReaderPalette.light,
      font,
      savedFontSize == null
          ? defaultFontSize
          : savedFontSize.clamp(minimumFontSize, maximumFontSize),
      preferences.getString(_readingModeKey) == 'pages'
          ? ReadingMode.pages
          : ReadingMode.scroll,
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
    _fontSize = value.clamp(minimumFontSize, maximumFontSize);
    notifyListeners();
    await _preferences.setDouble(_fontSizeKey, _fontSize);
  }
}

extension<T> on Iterable<T> {
  T? get firstOrNull => isEmpty ? null : first;
}
