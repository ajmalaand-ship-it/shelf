import 'package:flutter/material.dart';

import 'typography.dart';

class AppTheme {
  static ThemeData get light {
    const seed = Color(0xff765430);
    return ThemeData(
      colorScheme: ColorScheme.fromSeed(
        seedColor: seed,
        surface: const Color(0xfffffbf4),
      ),
      useMaterial3: true,
      fontFamily: AppTypography.uiFont,
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
        headlineLarge: TextStyle(fontSize: 32, fontWeight: FontWeight.w700),
        headlineMedium: TextStyle(fontSize: 26, fontWeight: FontWeight.w700),
        titleLarge: TextStyle(fontSize: 22, fontWeight: FontWeight.w600),
        titleMedium: TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
        bodyLarge: TextStyle(fontSize: 16, fontWeight: FontWeight.w400),
        bodyMedium: TextStyle(fontSize: 14, fontWeight: FontWeight.w400),
        labelLarge: TextStyle(fontWeight: FontWeight.w600),
        labelMedium: TextStyle(fontWeight: FontWeight.w500),
      ),
    );
  }
}
