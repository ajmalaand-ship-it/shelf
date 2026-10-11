import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';

/// Original owner-supplied bitmap assets; keep their intrinsic proportions.
class ShelfLogo extends StatelessWidget {
  const ShelfLogo({super.key, this.dark});
  final bool? dark;
  @override
  Widget build(BuildContext context) => Image.asset(
    'assets/brand/shelf_header_${(dark ?? Theme.of(context).brightness == Brightness.dark) ? "light" : "dark"}.png',
    height: 32,
    width: 142,
    fit: BoxFit.contain,
    semanticLabel: 'Shelf / شیلف',
  );
}

class ShelfActionIcon extends StatelessWidget {
  const ShelfActionIcon(
    this.name, {
    super.key,
    this.inactive = false,
    this.dark,
    this.size = 24,
    this.directional = false,
    this.reverse = false,
    this.color,
  });
  final String name;
  final Color? color;
  final bool inactive, directional, reverse;
  final bool? dark;
  final double size;
  @override
  Widget build(BuildContext context) {
    final backgroundDark =
        dark ?? Theme.of(context).brightness == Brightness.dark;
    final variant = inactive
        ? 'inactive'
        : backgroundDark
        ? 'dark'
        : 'light';
    final icon = Image.asset(
      'assets/actions/$variant/shelf-$name.png',
      width: size,
      height: size,
      fit: BoxFit.contain,
      excludeFromSemantics: true,
    );
    // Supplied back arrow points right. Directionality controls navigation only.
    return Transform.flip(
      flipX:
          directional &&
          ((Directionality.of(context) == TextDirection.ltr) != reverse),
      child: icon,
    );
  }
}

/// Icon-only toolbar control with localized accessibility and tooltip.
class ShelfFontButton extends StatelessWidget {
  const ShelfFontButton({super.key, required this.onPressed, this.dark});
  final VoidCallback onPressed;
  final bool? dark;
  @override
  Widget build(BuildContext context) => IconButton(
    tooltip: AppStrings.of(context).readingPreferences,
    onPressed: onPressed,
    icon: ShelfActionIcon('font', dark: dark),
  );
}
