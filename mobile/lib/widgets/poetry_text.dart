import 'package:flutter/material.dart';

import '../models/poem.dart';

class PoetryText extends StatelessWidget {
  const PoetryText({
    required this.text,
    required this.layoutMode,
    required this.style,
    super.key,
  });

  final String text;
  final PoemLayoutMode layoutMode;
  final TextStyle style;

  @override
  Widget build(BuildContext context) {
    final blocks = poetryDisplayBlocks(text, layoutMode);
    final fontEm = style.fontSize ?? 24;

    return Column(
      key: const Key('poem-body'),
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (final block in blocks) ...[
          SelectableText(
            block.text,
            textDirection: TextDirection.rtl,
            textAlign: TextAlign.start,
            style: style,
          ),
          if (block.gapAfterEm > 0)
            SizedBox(
              key: ValueKey<String>('paragraph-gap-${block.gapAfterEm}em'),
              height: fontEm * block.gapAfterEm,
            ),
        ],
      ],
    );
  }
}

class PoetryDisplayBlock {
  const PoetryDisplayBlock({required this.text, required this.gapAfterEm});

  final String text;
  final double gapAfterEm;
}

/// Paragraph boundaries are explicit presentation space, never empty text rows.
/// One newline remains inside a block as a normal poetic line advance. Existing
/// legacy grouping applies only when the body has no authored paragraph break.
List<PoetryDisplayBlock> poetryDisplayBlocks(
  String text,
  PoemLayoutMode layoutMode,
) {
  if (RegExp(r'\n(?:[ \t]*\n)+').hasMatch(text) ||
      layoutMode == PoemLayoutMode.source) {
    return paragraphDisplayBlocks(text);
  }
  return legacyGroupedBlocks(
    text,
    layoutMode == PoemLayoutMode.couplet ? 2 : 4,
    layoutMode == PoemLayoutMode.couplet ? 0.5 : 1.1,
  );
}

List<PoetryDisplayBlock> paragraphDisplayBlocks(String text) {
  final separator = RegExp(r'\n(?:[ \t]*\n)+');
  final matches = separator.allMatches(text).toList();
  final segments = text.split(separator);
  final blocks = <PoetryDisplayBlock>[];

  for (var index = 0; index < segments.length; index++) {
    if (segments[index].isNotEmpty) {
      blocks.add(PoetryDisplayBlock(text: segments[index], gapAfterEm: 0));
    }
    if (index < matches.length && blocks.isNotEmpty) {
      final previous = blocks.removeLast();
      blocks.add(
        PoetryDisplayBlock(
          text: previous.text,
          gapAfterEm:
              0.5 * ('\n'.allMatches(matches[index].group(0)!).length - 1),
        ),
      );
    }
  }
  return blocks;
}

List<PoetryDisplayBlock> legacyGroupedBlocks(
  String text,
  int groupSize,
  double gap,
) {
  final lines = text.split('\n');
  final blocks = <PoetryDisplayBlock>[];
  for (var start = 0; start < lines.length; start += groupSize) {
    blocks.add(
      PoetryDisplayBlock(
        text: lines
            .sublist(start, (start + groupSize).clamp(0, lines.length))
            .join('\n'),
        gapAfterEm: start + groupSize < lines.length ? gap : 0,
      ),
    );
  }
  return blocks;
}
