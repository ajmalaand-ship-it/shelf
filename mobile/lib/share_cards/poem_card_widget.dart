import 'dart:math' as math;

import 'package:flutter/material.dart';

import 'share_card_models.dart';

class PoemCardWidget extends StatelessWidget {
  const PoemCardWidget({required this.request, required this.page, super.key});

  static const logicalSize = Size(360, 450);

  final ShareCardRequest request;
  final ShareCardPage page;

  @override
  Widget build(BuildContext context) {
    final palette = _CardPalette.forTheme(request.theme);
    return Directionality(
      textDirection: TextDirection.rtl,
      child: SizedBox.fromSize(
        size: logicalSize,
        child: DecoratedBox(
          decoration: BoxDecoration(
            color: palette.background,
            border: Border.all(color: palette.border, width: 1.2),
          ),
          child: Stack(
            fit: StackFit.expand,
            children: [
              CustomPaint(painter: _CardMotifPainter(request.theme, palette)),
              Positioned(
                left: 20,
                bottom: 62,
                child: Transform.rotate(
                  angle: -math.pi / 2,
                  child: Text(
                    'Shelf',
                    style: TextStyle(
                      color: palette.watermark,
                      fontFamily: 'Vazirmatn',
                      fontSize: 11,
                      letterSpacing: 1.1,
                    ),
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(34, 28, 34, 24),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            'Shelf',
                            style: TextStyle(
                              color: palette.accent,
                              fontFamily: 'Vazirmatn',
                              fontWeight: FontWeight.w700,
                              fontSize: 20,
                              height: 1.4,
                            ),
                          ),
                        ),
                        Text(
                          'Shelf',
                          textDirection: TextDirection.ltr,
                          style: TextStyle(
                            color: palette.muted,
                            fontFamily: 'Vazirmatn',
                            fontSize: 9,
                            letterSpacing: 1.4,
                          ),
                        ),
                      ],
                    ),
                    if (!request.poem.isUntitled) ...[
                      const SizedBox(height: 4),
                      Text(
                        request.poem.title!,
                        textAlign: TextAlign.right,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          color: palette.muted,
                          fontFamily: request.fontFamily,
                          fontWeight: request.fontFamily == 'Vazirmatn'
                              ? FontWeight.w700
                              : FontWeight.w400,
                          fontSize: 13,
                          height: 1.7,
                        ),
                      ),
                    ],
                    const SizedBox(height: 8),
                    Expanded(
                      child: Align(
                        alignment: Alignment.centerRight,
                        child: Text(
                          page.text,
                          key: const Key('card-poem-text'),
                          textDirection: TextDirection.rtl,
                          textAlign: TextAlign.start,
                          style: TextStyle(
                            color: palette.foreground,
                            fontFamily: request.fontFamily,
                            fontSize: request.fontSize,
                            height: 2,
                          ),
                        ),
                      ),
                    ),
                    if (request.poem.isTranslation)
                      Text(
                        'اصلي شاعر: ${request.poem.originalAuthor ?? '—'}  •  '
                        'پښتو ژباړه: ${request.poem.translator ?? '—'}',
                        textAlign: TextAlign.right,
                        style: TextStyle(
                          color: palette.muted,
                          fontFamily: 'Vazirmatn',
                          fontSize: 11,
                          height: 1.4,
                        ),
                      )
                    else
                      Text(
                        request.poem.cardAuthor,
                        textAlign: TextAlign.right,
                        style: TextStyle(
                          color: palette.muted,
                          fontFamily: request.fontFamily,
                          fontSize: 13,
                          height: 1.5,
                        ),
                      ),
                    const SizedBox(height: 5),
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            request.collectionTitle ?? '',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              color: palette.muted,
                              fontFamily: 'Vazirmatn',
                              fontSize: 9,
                            ),
                          ),
                        ),
                        if (page.pageCount > 1)
                          Text(
                            '${page.pageNumber}/${page.pageCount}',
                            textDirection: TextDirection.ltr,
                            style: TextStyle(
                              color: palette.muted,
                              fontFamily: 'Vazirmatn',
                              fontSize: 9,
                            ),
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _CardPalette {
  const _CardPalette({
    required this.background,
    required this.foreground,
    required this.muted,
    required this.accent,
    required this.border,
    required this.watermark,
  });

  final Color background;
  final Color foreground;
  final Color muted;
  final Color accent;
  final Color border;
  final Color watermark;

  static _CardPalette forTheme(ShareCardTheme theme) => switch (theme) {
    ShareCardTheme.parchment => const _CardPalette(
      background: Color(0xfff3ead7),
      foreground: Color(0xff2f2419),
      muted: Color(0xff725e46),
      accent: Color(0xff855d31),
      border: Color(0xffcdbb9d),
      watermark: Color(0x22725e46),
    ),
    ShareCardTheme.dark => const _CardPalette(
      background: Color(0xff171816),
      foreground: Color(0xfff4eddf),
      muted: Color(0xffc2b59e),
      accent: Color(0xffd2a566),
      border: Color(0xff4e493f),
      watermark: Color(0x24f4eddf),
    ),
    ShareCardTheme.light => const _CardPalette(
      background: Color(0xfff7f6f1),
      foreground: Color(0xff27302f),
      muted: Color(0xff687371),
      accent: Color(0xff506f6c),
      border: Color(0xffc9d4d0),
      watermark: Color(0x24506f6c),
    ),
  };
}

class _CardMotifPainter extends CustomPainter {
  const _CardMotifPainter(this.theme, this.palette);

  final ShareCardTheme theme;
  final _CardPalette palette;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = palette.accent.withAlpha(theme == ShareCardTheme.dark ? 24 : 18)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.2;
    if (theme == ShareCardTheme.parchment) {
      canvas.drawRect(
        Rect.fromLTWH(10, 10, size.width - 20, size.height - 20),
        paint,
      );
    } else if (theme == ShareCardTheme.dark) {
      canvas.drawCircle(Offset(size.width - 25, 42), 74, paint);
      canvas.drawCircle(Offset(size.width - 25, 42), 58, paint);
    } else {
      final path = Path()
        ..moveTo(-20, size.height * .78)
        ..quadraticBezierTo(
          size.width * .35,
          size.height * .67,
          size.width * .62,
          size.height * .82,
        )
        ..quadraticBezierTo(
          size.width * .82,
          size.height * .92,
          size.width + 20,
          size.height * .74,
        );
      canvas.drawPath(path, paint..strokeWidth = 2);
    }
  }

  @override
  bool shouldRepaint(_CardMotifPainter oldDelegate) =>
      theme != oldDelegate.theme;
}
