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
        size: const Size(38, 34),
        painter: LiteraryPageQuillPainter(color),
      ),
    ),
  );
}

class LiteraryPageQuillPainter extends CustomPainter {
  const LiteraryPageQuillPainter(this.color);

  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final ink = Paint()
      ..color = color.withValues(alpha: 0.72)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.25
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;
    final page = Path()
      ..moveTo(5, 3)
      ..lineTo(25, 3)
      ..lineTo(31, 9)
      ..lineTo(31, 30)
      ..lineTo(5, 30)
      ..close()
      ..moveTo(25, 3)
      ..lineTo(25, 9)
      ..lineTo(31, 9);
    canvas.drawPath(page, ink);
    canvas.drawLine(
      const Offset(10, 13),
      const Offset(23, 13),
      ink..strokeWidth = 0.9,
    );
    canvas.drawLine(const Offset(10, 17), const Offset(21, 17), ink);
    canvas.drawLine(const Offset(10, 21), const Offset(18, 21), ink);

    final quill = Path()
      ..moveTo(16, 29)
      ..quadraticBezierTo(25, 17, 36, 7)
      ..quadraticBezierTo(35, 16, 27, 21)
      ..quadraticBezierTo(22, 25, 16, 29)
      ..moveTo(18, 27)
      ..lineTo(34, 9)
      ..moveTo(28, 15)
      ..lineTo(33, 15)
      ..moveTo(24, 20)
      ..lineTo(29, 20);
    canvas.drawPath(quill, ink..strokeWidth = 1.15);
  }

  @override
  bool shouldRepaint(covariant LiteraryPageQuillPainter oldDelegate) =>
      oldDelegate.color != color;
}
