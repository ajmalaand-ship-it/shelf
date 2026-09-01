import 'package:flutter/material.dart';

class UntitledPoemMarker extends StatelessWidget {
  const UntitledPoemMarker({required this.color, super.key});

  final Color color;

  @override
  Widget build(BuildContext context) => Semantics(
    label: 'بې نومه شعر؛ اصلي سرليک نه لري',
    image: true,
    child: ExcludeSemantics(
      child: CustomPaint(
        key: const Key('untitled-poem-indicator'),
        size: const Size(44, 30),
        painter: _ManuscriptOrnamentPainter(color),
      ),
    ),
  );
}

class _ManuscriptOrnamentPainter extends CustomPainter {
  const _ManuscriptOrnamentPainter(this.color);

  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final ink = Paint()
      ..color = color.withValues(alpha: 0.72)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.25
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;
    final center = size.width / 2;

    final pages = Path()
      ..moveTo(center, size.height * 0.82)
      ..cubicTo(
        center - 5,
        size.height * 0.66,
        center - 12,
        size.height * 0.62,
        4,
        size.height * 0.7,
      )
      ..lineTo(4, size.height * 0.2)
      ..cubicTo(
        center - 12,
        size.height * 0.12,
        center - 5,
        size.height * 0.22,
        center,
        size.height * 0.36,
      )
      ..cubicTo(
        center + 5,
        size.height * 0.22,
        center + 12,
        size.height * 0.12,
        size.width - 4,
        size.height * 0.2,
      )
      ..lineTo(size.width - 4, size.height * 0.7)
      ..cubicTo(
        center + 12,
        size.height * 0.62,
        center + 5,
        size.height * 0.66,
        center,
        size.height * 0.82,
      );
    canvas.drawPath(pages, ink);

    canvas.drawLine(
      Offset(center, size.height * 0.36),
      Offset(center, size.height * 0.82),
      ink..strokeWidth = 0.9,
    );

    final flourish = Path()
      ..moveTo(center - 7, size.height * 0.93)
      ..quadraticBezierTo(
        center - 2,
        size.height * 0.82,
        center,
        size.height * 0.94,
      )
      ..quadraticBezierTo(
        center + 2,
        size.height * 0.82,
        center + 7,
        size.height * 0.93,
      );
    canvas.drawPath(flourish, ink);
  }

  @override
  bool shouldRepaint(covariant _ManuscriptOrnamentPainter oldDelegate) =>
      oldDelegate.color != color;
}
