import 'package:flutter/material.dart';

import 'typography.dart';

class AppTheme {
  static ThemeData get light {
    const seed = Color(0xff80501d);
    return ThemeData(
      colorScheme: ColorScheme.fromSeed(seedColor: seed, surface: Colors.white)
          .copyWith(
            primary: seed,
            onPrimary: Colors.white,
            primaryContainer: const Color(0xfff0e5d3),
            secondaryContainer: const Color(0xfff0e5d3),
            onSecondaryContainer: seed,
            surfaceContainerHighest: const Color(0xfff0e5d3),
            onPrimaryContainer: seed,
            onSurface: const Color(0xff29231d),
            onSurfaceVariant: const Color(0xff796f62),
            outlineVariant: const Color(0xffe4dacc),
          ),
      useMaterial3: true,
      fontFamily: AppTypography.uiFont,
      scaffoldBackgroundColor: const Color(0xfffaf6ef),
      bottomSheetTheme: const BottomSheetThemeData(
        backgroundColor: Color(0xfffaf6ef),
        surfaceTintColor: Colors.transparent,
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: Color(0xfffaf6ef),
        surfaceTintColor: Colors.transparent,
        centerTitle: false,
        titleTextStyle: TextStyle(
          fontFamily: AppTypography.uiFont,
          fontSize: 24,
          fontWeight: FontWeight.w700,
          color: Color(0xff29231d),
        ),
      ),
      cardTheme: const CardThemeData(
        elevation: 0,
        color: Colors.white,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.all(Radius.circular(12)),
          side: BorderSide(color: Color(0xffe4dacc)),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        contentPadding: const EdgeInsets.all(12),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xffe4dacc)),
        ),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          minimumSize: const Size(44, 52),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          backgroundColor: const Color(0xfff0e5d3),
          minimumSize: const Size(44, 52),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          side: const BorderSide(color: Color(0xffe4dacc)),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      ),
      chipTheme: ChipThemeData(
        backgroundColor: const Color(0xfff0e5d3),
        selectedColor: seed,
        labelStyle: const TextStyle(
          fontFamily: AppTypography.uiFont,
          fontSize: 13,
        ),
        padding: const EdgeInsets.all(8),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        side: BorderSide.none,
      ),
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: Colors.white,
        indicatorColor: const Color(0xfff0e5d3),
        labelTextStyle: WidgetStateProperty.all(
          const TextStyle(
            fontFamily: AppTypography.uiFont,
            fontSize: 11,
            fontWeight: FontWeight.w500,
          ),
        ),
      ),
      textTheme: const TextTheme(
        headlineLarge: TextStyle(fontSize: 32, fontWeight: FontWeight.w700),
        headlineMedium: TextStyle(fontSize: 26, fontWeight: FontWeight.w700),
        headlineSmall: TextStyle(
          fontSize: 20,
          fontWeight: FontWeight.w700,
          height: 1.55,
        ),
        titleLarge: TextStyle(
          fontSize: 25,
          fontWeight: FontWeight.w700,
          height: 1.55,
        ),
        titleMedium: TextStyle(
          fontSize: 17,
          fontWeight: FontWeight.w500,
          height: 1.55,
        ),
        bodyLarge: TextStyle(fontSize: 16, fontWeight: FontWeight.w400),
        bodyMedium: TextStyle(fontSize: 14, fontWeight: FontWeight.w400),
        labelLarge: TextStyle(fontWeight: FontWeight.w600),
        labelMedium: TextStyle(fontWeight: FontWeight.w500),
      ),
    );
  }
}
