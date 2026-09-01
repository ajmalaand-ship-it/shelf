import 'package:flutter/material.dart';

import '../models/poem.dart';

class PoetryText extends StatelessWidget {
  const PoetryText({
    required this.text,
    required this.layoutMode,
    required this.style,
    this.presentationSpacing,
    super.key,
  });

  final String text;
  final PoemLayoutMode layoutMode;
  final TextStyle style;
  final PoemPresentationSpacing? presentationSpacing;

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

    final manualBlocks = manualDisplayBlocks(text, presentationSpacing);
    if (manualBlocks != null || layoutMode == PoemLayoutMode.couplet) {
      final blocks = manualBlocks ?? coupletDisplayBlocks(text);
      final fontEm = style.fontSize ?? 24;
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
            if (block.gapAfterEm > 0)
              SizedBox(
                key: ValueKey<double>(block.gapAfterEm),
                height: fontEm * block.gapAfterEm,
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
  const PoetryDisplayBlock({required this.text, required this.gapAfterEm});

  final String text;
  final double gapAfterEm;
}

List<PoetryDisplayBlock>? manualDisplayBlocks(
  String text,
  PoemPresentationSpacing? spacing,
) {
  if (spacing == null) return null;
  final lines = text
      .split('\n')
      .where((line) => line.trim().isNotEmpty)
      .toList();
  if (spacing.lineCount != lines.length) return null;

  final blocks = <PoetryDisplayBlock>[];
  final current = <String>[];
  for (var index = 0; index < lines.length; index++) {
    current.add(lines[index]);
    final gap = spacing.gaps[index + 1];
    if (gap == null && index < lines.length - 1) continue;
    blocks.add(
      PoetryDisplayBlock(
        text: current.join('\n'),
        gapAfterEm: switch (gap) {
          PoemGap.half => 0.5,
          PoemGap.full => 1,
          null => 0,
        },
      ),
    );
    current.clear();
  }
  return blocks;
}

/// Builds Ghazal bayts without changing [text]. A single authored blank line
/// after a complete two-line bayt represents the same half-em visual gap;
/// each additional blank line remains as a full source line of space.
List<PoetryDisplayBlock> coupletDisplayBlocks(String text) {
  final segments = text.split(RegExp(r'(\n(?:[\t ]*\n)+)'));
  final separators = RegExp(r'\n(?:[\t ]*\n)+').allMatches(text).toList();
  final blocks = <PoetryDisplayBlock>[];

  void addTextSegment(String segment) {
    final lines = segment.split('\n');
    for (var start = 0; start < lines.length; start += 2) {
      if (blocks.isNotEmpty && blocks.last.gapAfterEm == 0) {
        final previous = blocks.removeLast();
        blocks.add(PoetryDisplayBlock(text: previous.text, gapAfterEm: 0.5));
      }
      blocks.add(
        PoetryDisplayBlock(
          text: lines
              .sublist(start, (start + 2).clamp(0, lines.length))
              .join('\n'),
          gapAfterEm: 0,
        ),
      );
    }
  }

  for (var index = 0; index < segments.length; index++) {
    addTextSegment(segments[index]);
    if (index >= separators.length) continue;

    final blankCount = '\n'.allMatches(separators[index].group(0)!).length - 1;
    if (blocks.isEmpty) {
      blocks.add(PoetryDisplayBlock(text: '', gapAfterEm: blankCount * 2.2));
      continue;
    }

    final previous = blocks.removeLast();
    final isCompleteBayt = '\n'.allMatches(previous.text).length == 1;
    blocks.add(
      PoetryDisplayBlock(
        text: previous.text,
        gapAfterEm: isCompleteBayt
            ? 0.5 + (blankCount - 1).clamp(0, blankCount) * 2.2
            : blankCount * 2.2,
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
