import 'package:flutter/material.dart';

import 'reader_settings.dart';

/// The reader and its live preview always use the same persisted palette.
class ReaderPaletteColors {
  const ReaderPaletteColors(this.background, this.foreground, this.muted);
  final Color background, foreground, muted;
  static ReaderPaletteColors forPalette(ReaderPalette palette) =>
      switch (palette) {
        ReaderPalette.light => const ReaderPaletteColors(
          Color(0xfffaf6ef),
          Color(0xff29231d),
          Color(0xff796f62),
        ),
        ReaderPalette.sepia => const ReaderPaletteColors(
          Color(0xfff0e5d3),
          Color(0xff3e2f20),
          Color(0xff765f47),
        ),
        ReaderPalette.dark => const ReaderPaletteColors(
          Color(0xff28251f),
          Color(0xfffaf6ef),
          Color(0xffbcb0a1),
        ),
      };
}
