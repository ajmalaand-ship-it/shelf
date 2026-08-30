import 'dart:io';
import 'dart:ui' as ui;

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';

import 'poem_card_widget.dart';
import 'share_card_files.dart';
import 'share_card_models.dart';

enum ShareCardExportStage { render, pngEncode, file, share, gallery }

class ShareCardExportException implements Exception {
  const ShareCardExportException(this.stage, this.message, [this.cause]);

  final ShareCardExportStage stage;
  final String message;
  final Object? cause;

  @override
  String toString() => 'ShareCardExportException($stage): $message';
}

typedef PrepareShareCardPage = Future<RenderRepaintBoundary?> Function(
  int pageIndex,
);

class ShareCardRenderer {
  const ShareCardRenderer({this.pixelRatio = 3});

  final double pixelRatio;

  Size get outputSize => Size(
    PoemCardWidget.logicalSize.width * pixelRatio,
    PoemCardWidget.logicalSize.height * pixelRatio,
  );

  Future<List<File>> render({
    required ShareCardRequest request,
    required List<ShareCardPage> pages,
    required ShareCardFiles files,
    required PrepareShareCardPage preparePage,
    DateTime? createdAt,
  }) async {
    final output = <File>[];
    final timestamp = createdAt ?? DateTime.now();
    try {
      for (var index = 0; index < pages.length; index++) {
        final boundary = await preparePage(index);
        if (boundary == null || !boundary.attached) {
          throw const ShareCardExportException(
            ShareCardExportStage.render,
            'Preview boundary is unavailable or detached',
          );
        }
        final bytes = await capturePaintedBoundary(boundary);
        final file = await _writeValidatedFile(
          files: files,
          bytes: bytes,
          poemId: request.poem.id,
          pageNumber: pages[index].pageNumber,
          createdAt: timestamp,
        );
        output.add(file);
        _debugLog(
          stage: ShareCardExportStage.file,
          message: 'page ${index + 1}/${pages.length} ready',
          byteLength: bytes.length,
          path: file.path,
          fileExists: true,
        );
      }
      return output;
    } catch (error, stackTrace) {
      await files.remove(output);
      _debugFailure(error, stackTrace);
      if (error is ShareCardExportException) rethrow;
      throw ShareCardExportException(
        ShareCardExportStage.render,
        'Unexpected card export failure',
        error,
      );
    }
  }

  @visibleForTesting
  Future<Uint8List> capturePaintedBoundary(
    RenderRepaintBoundary boundary,
  ) async {
    try {
      for (
        var attempt = 0;
        attempt < 3 && boundary.debugNeedsPaint;
        attempt++
      ) {
        await WidgetsBinding.instance.endOfFrame;
      }
      if (!boundary.attached || boundary.debugNeedsPaint) {
        throw StateError('Preview did not finish painting');
      }
      final image = await boundary.toImage(pixelRatio: pixelRatio);
      try {
        final expectedWidth = outputSize.width.round();
        final expectedHeight = outputSize.height.round();
        if (image.width != expectedWidth || image.height != expectedHeight) {
          throw StateError(
            'Unexpected image size ${image.width}x${image.height}',
          );
        }
        final data = await image.toByteData(format: ui.ImageByteFormat.png);
        if (data == null) {
          throw const ShareCardExportException(
            ShareCardExportStage.pngEncode,
            'PNG encoder returned no data',
          );
        }
        final bytes = data.buffer.asUint8List(
          data.offsetInBytes,
          data.lengthInBytes,
        );
        validatePng(bytes, expectedWidth, expectedHeight);
        _debugLog(
          stage: ShareCardExportStage.pngEncode,
          message: 'PNG encoded',
          byteLength: bytes.length,
        );
        return bytes;
      } finally {
        image.dispose();
      }
    } on ShareCardExportException {
      rethrow;
    } catch (error) {
      throw ShareCardExportException(
        ShareCardExportStage.render,
        'Painted preview capture failed',
        error,
      );
    }
  }

  @visibleForTesting
  void validatePng(Uint8List bytes, int expectedWidth, int expectedHeight) {
    const signature = <int>[137, 80, 78, 71, 13, 10, 26, 10];
    if (bytes.length < 24 ||
        !listEquals(bytes.sublist(0, signature.length), signature)) {
      throw const ShareCardExportException(
        ShareCardExportStage.pngEncode,
        'Invalid or empty PNG output',
      );
    }
    final data = ByteData.sublistView(bytes);
    final width = data.getUint32(16);
    final height = data.getUint32(20);
    if (width != expectedWidth || height != expectedHeight) {
      throw ShareCardExportException(
        ShareCardExportStage.pngEncode,
        'PNG dimensions are ${width}x$height, expected '
        '${expectedWidth}x$expectedHeight',
      );
    }
  }

  Future<File> _writeValidatedFile({
    required ShareCardFiles files,
    required Uint8List bytes,
    required int poemId,
    required int pageNumber,
    required DateTime createdAt,
  }) async {
    File? file;
    try {
      file = await files.write(
        bytes: bytes,
        poemId: poemId,
        pageNumber: pageNumber,
        createdAt: createdAt,
      );
      if (!await file.exists() || await file.length() != bytes.length) {
        throw StateError('Temporary PNG is missing or incomplete');
      }
      final handle = await file.open();
      try {
        final header = await handle.read(24);
        validatePng(
          header,
          outputSize.width.round(),
          outputSize.height.round(),
        );
      } finally {
        await handle.close();
      }
      return file;
    } catch (error) {
      if (file != null && await file.exists()) await file.delete();
      throw ShareCardExportException(
        ShareCardExportStage.file,
        'Temporary PNG could not be verified',
        error,
      );
    }
  }

  void _debugFailure(Object error, StackTrace stackTrace) {
    if (!kDebugMode) return;
    final stage = error is ShareCardExportException
        ? error.stage
        : ShareCardExportStage.render;
    debugPrint(
      '[P4 export] FAILURE stage=${stage.name} type=${error.runtimeType} '
      'logical=${PoemCardWidget.logicalSize.width.toInt()}x'
      '${PoemCardWidget.logicalSize.height.toInt()} ratio=$pixelRatio '
      'output=${outputSize.width.toInt()}x${outputSize.height.toInt()} '
      'error=$error\n$stackTrace',
    );
  }

  void _debugLog({
    required ShareCardExportStage stage,
    required String message,
    int? byteLength,
    String? path,
    bool? fileExists,
  }) {
    if (!kDebugMode) return;
    debugPrint(
      '[P4 export] stage=${stage.name} $message '
      'output=${outputSize.width.toInt()}x${outputSize.height.toInt()} '
      'ratio=$pixelRatio bytes=${byteLength ?? '-'} '
      'path=${path ?? '-'} exists=${fileExists ?? '-'}',
    );
  }
}
