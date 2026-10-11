import 'package:flutter/material.dart';

class PoetryText extends StatelessWidget {
  const PoetryText({
    required this.text,
    required this.style,
    this.direction = TextDirection.rtl,
    super.key,
  });

  final String text;
  final TextStyle style;
  final TextDirection direction;

  @override
  Widget build(BuildContext context) {
    return SelectableText(
      text,
      key: const Key('poem-body'),
      textDirection: direction,
      textAlign: TextAlign.start,
      style: style,
    );
  }
}
