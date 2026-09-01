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

    final groupSize = layoutMode == PoemLayoutMode.couplet ? 2 : 4;
    final groups = poetryDisplayGroups(text, groupSize);
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
