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
    if (layoutMode == PoemLayoutMode.source) {
      return SelectableText(
        text,
        key: const Key('poem-body'),
        textDirection: TextDirection.rtl,
        textAlign: TextAlign.start,
        style: style,
      );
    }

    if (layoutMode == PoemLayoutMode.couplet) {
      final blocks = coupletDisplayBlocks(text);
      final lineExtent = (style.fontSize ?? 24) * (style.height ?? 1);
      return Column(
        key: const Key('poem-body'),
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          for (final block in blocks) ...[
            if (block.text.isNotEmpty)
              SelectableText(
                block.text,
                textDirection: TextDirection.rtl,
                textAlign: TextAlign.start,
                style: style,
              ),
            if (block.gapAfterLines > 0)
              SizedBox(
                key: ValueKey<double>(block.gapAfterLines),
                height: lineExtent * block.gapAfterLines,
              ),
          ],
        ],
      );
    }

    final groups = poetryDisplayGroups(text, 4);
    return Column(
      key: const Key('poem-body'),
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (var index = 0; index < groups.length; index++) ...[
          SelectableText(
            groups[index],
            textDirection: TextDirection.rtl,
            textAlign: TextAlign.start,
            style: style,
          ),
          if (index < groups.length - 1)
            SizedBox(height: (style.fontSize ?? 24) * (style.height ?? 1) * .5),
        ],
      ],
    );
  }
}

class PoetryDisplayBlock {
  const PoetryDisplayBlock({required this.text, required this.gapAfterLines});

  final String text;
  final double gapAfterLines;
}

/// Builds Ghazal bayts without changing [text]. A single authored blank line
/// after a complete two-line bayt represents the same half-line visual gap;
/// each additional blank line remains as a full typographic line of space.
List<PoetryDisplayBlock> coupletDisplayBlocks(String text) {
  final segments = text.split(RegExp(r'(\n(?:[\t ]*\n)+)'));
  final separators = RegExp(r'\n(?:[\t ]*\n)+').allMatches(text).toList();
  final blocks = <PoetryDisplayBlock>[];

  void addTextSegment(String segment) {
    final lines = segment.split('\n');
    for (var start = 0; start < lines.length; start += 2) {
      if (blocks.isNotEmpty && blocks.last.gapAfterLines == 0) {
        final previous = blocks.removeLast();
        blocks.add(PoetryDisplayBlock(text: previous.text, gapAfterLines: 0.5));
      }
      blocks.add(
        PoetryDisplayBlock(
          text: lines
              .sublist(start, (start + 2).clamp(0, lines.length))
              .join('\n'),
          gapAfterLines: 0,
        ),
      );
    }
  }

  for (var index = 0; index < segments.length; index++) {
    addTextSegment(segments[index]);
    if (index >= separators.length) continue;

    final blankCount = '\n'.allMatches(separators[index].group(0)!).length - 1;
    if (blocks.isEmpty) {
      blocks.add(
        PoetryDisplayBlock(text: '', gapAfterLines: blankCount.toDouble()),
      );
      continue;
    }

    final previous = blocks.removeLast();
    final isCompleteBayt = '\n'.allMatches(previous.text).length == 1;
    blocks.add(
      PoetryDisplayBlock(
        text: previous.text,
        gapAfterLines: isCompleteBayt
            ? 0.5 + (blankCount - 1).clamp(0, blankCount).toDouble()
            : blankCount.toDouble(),
      ),
    );
  }

  return blocks;
}

List<String> poetryDisplayGroups(String text, int groupSize) {
  final groups = <String>[];
  var current = <String>[];
  for (final line in text.split('\n')) {
    if (line.isEmpty) {
      if (current.isNotEmpty) {
        groups.add(current.join('\n'));
        current = [];
      }
      groups.add('');
      continue;
    }
    current.add(line);
    if (current.length == groupSize) {
      groups.add(current.join('\n'));
      current = [];
    }
  }
  if (current.isNotEmpty) groups.add(current.join('\n'));
  return groups;
}
