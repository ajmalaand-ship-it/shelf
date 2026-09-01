import 'package:flutter/material.dart';

class PoetryText extends StatelessWidget {
  const PoetryText({
    required this.text,
    required this.style,
    super.key,
  });

  final String text;
  final TextStyle style;

  @override
  Widget build(BuildContext context) {
    return SelectableText(
      text,
      key: const Key('poem-body'),
      textDirection: TextDirection.rtl,
      textAlign: TextAlign.start,
      style: style,
    );
  }
}
