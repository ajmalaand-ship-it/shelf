import 'package:flutter/material.dart';

abstract final class AppTypography {
  static const uiFont = 'Vazirmatn';
  static const naskhFont = 'ScheherazadeNew';
  static const literaryFont = 'NotoNastaliqUrdu';

  static const majorTitle = TextStyle(fontWeight: FontWeight.w700);
  static const poemTitle = TextStyle(fontWeight: FontWeight.w700);
  static const sectionHeading = TextStyle(fontWeight: FontWeight.w600);
  static const primaryLabel = TextStyle(fontWeight: FontWeight.w500);
  static const body = TextStyle(fontWeight: FontWeight.w400);
  static const metadata = TextStyle(fontWeight: FontWeight.w400, fontSize: 13);
}
