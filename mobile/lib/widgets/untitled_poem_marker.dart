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
        painter: ManuscriptPageQuillPainter(color),
      ),
    ),
  );
}

class ManuscriptPageQuillPainter extends CustomPainter {
  const ManuscriptPageQuillPainter(this.color);

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
      ..moveTo(7, 4)
      ..quadraticBezierTo(17, 2, 27, 4)
      ..quadraticBezierTo(29, 16, 27, 29)
      ..quadraticBezierTo(17, 31, 7, 28)
      ..quadraticBezierTo(5, 16, 7, 4)
      ..close()
      ..moveTo(11, 10)
      ..quadraticBezierTo(18, 8.5, 24, 10)
      ..moveTo(11, 15)
      ..quadraticBezierTo(17, 13.5, 22, 15)
      ..moveTo(11, 20)
      ..quadraticBezierTo(15, 19, 18, 20);
    canvas.drawPath(page, ink);

    final quill = Path()
      ..moveTo(17, 31)
      ..quadraticBezierTo(27, 20, 36, 8)
      ..quadraticBezierTo(35, 17, 29, 22)
      ..quadraticBezierTo(23, 27, 17, 31)
      ..moveTo(19, 29)
      ..lineTo(34, 10);
    canvas.drawPath(quill, ink..strokeWidth = 1.15);
  }

  @override
  bool shouldRepaint(covariant ManuscriptPageQuillPainter oldDelegate) =>
      oldDelegate.color != color;
}
