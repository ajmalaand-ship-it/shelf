import 'dart:io';
import 'dart:typed_data';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';

import 'poem_card_widget.dart';
import 'share_card_files.dart';
import 'share_card_models.dart';

class ShareCardRenderer {
  const ShareCardRenderer({this.pixelRatio = 3});

  final double pixelRatio;

  Size get outputSize => Size(
    PoemCardWidget.logicalSize.width * pixelRatio,
    PoemCardWidget.logicalSize.height * pixelRatio,
  );

  Future<List<File>> render({
    required BuildContext context,
    required ShareCardRequest request,
    required List<ShareCardPage> pages,
    required ShareCardFiles files,
    DateTime? createdAt,
  }) async {
    final output = <File>[];
    final timestamp = createdAt ?? DateTime.now();
    final overlay = Overlay.of(context);
    try {
      for (final page in pages) {
        final bytes = await _renderPage(overlay, request, page);
        output.add(
          await files.write(
            bytes: bytes,
            poemId: request.poem.id,
            pageNumber: page.pageNumber,
            createdAt: timestamp,
          ),
        );
      }
      return output;
    } catch (_) {
      await files.remove(output);
      rethrow;
    }
  }

  Future<Uint8List> _renderPage(
    OverlayState overlay,
    ShareCardRequest request,
    ShareCardPage page,
  ) async {
    final key = GlobalKey();
    final entry = OverlayEntry(
      builder: (_) => Positioned(
        left: 0,
        top: 0,
        child: IgnorePointer(
          child: Opacity(
            opacity: 0.001,
            child: Material(
              type: MaterialType.transparency,
              child: RepaintBoundary(
                key: key,
                child: PoemCardWidget(request: request, page: page),
              ),
            ),
          ),
        ),
      ),
    );
    overlay.insert(entry);
    try {
      await WidgetsBinding.instance.endOfFrame;
      final boundary = key.currentContext?.findRenderObject();
      if (boundary is! RenderRepaintBoundary) {
        throw StateError('Poem card could not be prepared');
      }
      final image = await boundary.toImage(pixelRatio: pixelRatio);
      try {
        final data = await image.toByteData(format: ui.ImageByteFormat.png);
        if (data == null) {
          throw StateError('Poem card PNG could not be encoded');
        }
        return data.buffer.asUint8List();
      } finally {
        image.dispose();
      }
    } finally {
      entry.remove();
    }
  }
}
