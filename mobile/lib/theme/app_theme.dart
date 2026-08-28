import 'package:flutter/material.dart';

class AppTheme {
  static ThemeData get light {
    const seed = Color(0xff765430);
    return ThemeData(
      colorScheme: ColorScheme.fromSeed(
        seedColor: seed,
        surface: const Color(0xfffffbf4),
      ),
      useMaterial3: true,
      scaffoldBackgroundColor: const Color(0xfff8f2e8),
      appBarTheme: const AppBarTheme(
        backgroundColor: Color(0xfff8f2e8),
        surfaceTintColor: Colors.transparent,
        centerTitle: true,
      ),
      cardTheme: const CardThemeData(
        elevation: 0,
        color: Color(0xfffffbf4),
        margin: EdgeInsets.zero,
      ),
      textTheme: const TextTheme(
        headlineLarge: TextStyle(fontFamily: 'NotoNastaliqUrdu'),
        headlineMedium: TextStyle(fontFamily: 'NotoNastaliqUrdu'),
        titleLarge: TextStyle(fontFamily: 'NotoNastaliqUrdu'),
        titleMedium: TextStyle(fontFamily: 'NotoNastaliqUrdu'),
      ),
    );
  }
}
