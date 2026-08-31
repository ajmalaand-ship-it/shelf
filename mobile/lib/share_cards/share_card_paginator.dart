import 'package:flutter/painting.dart';

import 'share_card_models.dart';

class ShareCardPaginator {
  const ShareCardPaginator({
    this.textWidth = 288,
    this.textHeight = 244,
    this.fontFamily = 'Vazirmatn',
    this.fontSize = 20,
    this.lineHeight = 2,
  });

  final double textWidth;
  final double textHeight;
  final String fontFamily;
  final double fontSize;
  final double lineHeight;

  List<ShareCardPage> paginate(String source) {
    final normalized = source.replaceAll('\r\n', '\n').replaceAll('\r', '\n');
    if (normalized.trim().isEmpty) throw ArgumentError('Card text is empty');

    final blocks = normalized.split(RegExp(r'\n[ \t]*\n'));
    final pageTexts = <String>[];
    var current = '';
    for (final block in blocks) {
      if (block.isEmpty) continue;
      final candidate = current.isEmpty ? block : '$current\n\n$block';
      if (_fits(candidate)) {
        current = candidate;
        continue;
      }
      if (current.isNotEmpty) {
        pageTexts.add(current);
        current = '';
      }
      if (_fits(block)) {
        current = block;
      } else {
        final pieces = _splitOversizedBlock(block);
        pageTexts.addAll(pieces.take(pieces.length - 1));
        current = pieces.last;
      }
    }
    if (current.isNotEmpty) pageTexts.add(current);

    return List.generate(
      pageTexts.length,
      (index) => ShareCardPage(
        text: pageTexts[index],
        pageNumber: index + 1,
        pageCount: pageTexts.length,
      ),
    );
  }

  List<String> _splitOversizedBlock(String block) {
    final pieces = <String>[];
    var current = '';
    for (final line in block.split('\n')) {
      final candidate = current.isEmpty ? line : '$current\n$line';
      if (_fits(candidate)) {
        current = candidate;
        continue;
      }
      if (current.isNotEmpty) {
        pieces.add(current);
        current = '';
      }
      if (_fits(line)) {
        current = line;
      } else {
        final wrapped = _splitOversizedLine(line);
        pieces.addAll(wrapped.take(wrapped.length - 1));
        current = wrapped.last;
      }
    }
    if (current.isNotEmpty) pieces.add(current);
    return pieces;
  }

  List<String> _splitOversizedLine(String line) {
    final words = line.split(RegExp(r'(?<=\s)|(?=\s)'));
    final pieces = <String>[];
    var current = '';
    for (final word in words) {
      final candidate = '$current$word';
      if (current.isEmpty || _fits(candidate)) {
        current = candidate;
      } else {
        pieces.add(current.trimRight());
        current = word.trimLeft();
      }
    }
    if (current.isNotEmpty) pieces.add(current);
    if (pieces.any((piece) => !_fits(piece))) {
      throw StateError('A single word is too large for a poem card');
    }
    return pieces;
  }

  bool _fits(String text) {
    final painter = TextPainter(
      text: TextSpan(
        text: text,
        style: TextStyle(
          fontFamily: fontFamily,
          fontSize: fontSize,
          height: lineHeight,
        ),
      ),
      textDirection: TextDirection.rtl,
      textAlign: TextAlign.start,
      maxLines: null,
    )..layout(maxWidth: textWidth);
    return painter.height <= textHeight;
  }
}
