import 'package:flutter/material.dart';

class TextPage {
  const TextPage(this.start, this.end, {this.oversized = false});
  final int start, end;
  // When even a single shaped grapheme cannot fit, allow vertical scrolling
  // on this page rather than shrink, clip, or discard that grapheme.
  final bool oversized;
  String text(String source) => source.substring(start, end);
}

class TextPages {
  static List<TextPage> layout({
    required String source,
    required TextStyle style,
    required TextScaler textScaler,
    required TextDirection direction,
    required double width,
    required double height,
  }) {
    if (source.isEmpty) return const [];
    final boundaries = <int>[0];
    for (final cluster in source.characters) {
      boundaries.add(boundaries.last + cluster.length);
    }
    final painter = TextPainter(
      textDirection: direction,
      textScaler: textScaler,
    );
    bool fits(int start, int end) {
      painter.text = TextSpan(text: source.substring(start, end), style: style);
      painter.layout(maxWidth: width.clamp(1, double.infinity));
      return painter.height <= height;
    }

    final pages = <TextPage>[];
    var cursor = 0;
    while (cursor < boundaries.length - 1) {
      // Bound the search near this page rather than shaping half of a long
      // chapter for every page.
      var span = 1;
      final last = boundaries.length - 1;
      while (cursor + span < last &&
          fits(boundaries[cursor], boundaries[cursor + span])) {
        span *= 2;
      }
      var low = cursor + 1,
          high = (cursor + span).clamp(cursor + 1, last),
          best = cursor;
      while (low <= high) {
        final mid = (low + high) ~/ 2;
        if (fits(boundaries[cursor], boundaries[mid])) {
          best = mid;
          low = mid + 1;
        } else {
          high = mid - 1;
        }
      }
      final oversized = best == cursor;
      if (oversized) {
        // A line taller than the viewport remains one scrollable page.
        final newline = RegExp(r'\r\n|\n|\r')
            .firstMatch(source.substring(boundaries[cursor]));
        final end = newline == null
            ? source.length
            : boundaries[cursor] + newline.end;
        best = boundaries.indexOf(end, cursor + 1);
        if (best <= cursor) best = cursor + 1;
      }
      // Prefer an explicit stanza/line boundary over a mid-line cut. Keep
      // every separator in the source range; never trim or normalize it.
      if (!oversized && best < boundaries.length - 1) {
        final part = source.substring(boundaries[cursor], boundaries[best]);
        final stanza = RegExp(r'(?:\r\n|\n|\r)[ \t]*(?:\r\n|\n|\r)')
            .allMatches(part)
            .lastOrNull;
        final newline = RegExp(r'\r\n|\n|\r').allMatches(part).lastOrNull;
        final preferred = stanza?.end ?? newline?.end;
        if (preferred != null && preferred > 0) {
          final index = boundaries.indexOf(
            boundaries[cursor] + preferred,
            cursor + 1,
          );
          if (index > cursor && fits(boundaries[cursor], boundaries[index])) {
            best = index;
          }
        } else {
          // Avoid breaking a word if a fitting whitespace boundary exists.
          final spaces = RegExp(r'\s+').allMatches(part).lastOrNull;
          if (spaces != null && spaces.start > 0) {
            final index = boundaries.indexOf(
              boundaries[cursor] + spaces.end,
              cursor + 1,
            );
            if (index > cursor && fits(boundaries[cursor], boundaries[index])) {
              best = index;
            }
          }
        }
      }
      pages.add(
        TextPage(boundaries[cursor], boundaries[best], oversized: oversized),
      );
      cursor = best;
    }
    painter.dispose();
    return pages;
  }

  static int containing(List<TextPage> pages, int offset) {
    for (var i = 0; i < pages.length; i++) {
      if (offset < pages[i].end) return i;
    }
    return pages.isEmpty ? 0 : pages.length - 1;
  }
}
