import 'dart:convert';
import 'dart:io';

import 'package:crypto/crypto.dart';

class AudioCacheStore {
  AudioCacheStore(
    this.directory, {
    this.maxFiles = 20,
    this.maxBytes = 128 * 1024 * 1024,
  });

  final Directory directory;
  final int maxFiles;
  final int maxBytes;

  Future<File> fileFor({
    required int poemId,
    required String cacheKey,
    String? format,
  }) async {
    await directory.create(recursive: true);
    final digest = sha256.convert(utf8.encode(cacheKey)).toString();
    final extension = _safeFormat(format);
    return File(
      '${directory.path}/p${poemId}_${digest.substring(0, 24)}.$extension',
    );
  }

  Future<bool> contains({
    required int poemId,
    required String cacheKey,
    String? format,
  }) async => (await fileFor(
    poemId: poemId,
    cacheKey: cacheKey,
    format: format,
  )).exists();

  Future<void> removeOtherVersions({
    required int poemId,
    required File keep,
  }) async {
    if (!await directory.exists()) return;
    await for (final entity in directory.list()) {
      if (entity is File &&
          entity.path != keep.path &&
          entity.uri.pathSegments.last.startsWith('p${poemId}_')) {
        await entity.delete();
      }
    }
  }

  Future<void> clearPartial(File target) async {
    for (final suffix in const ['.part', '.mime']) {
      final partial = File('${target.path}$suffix');
      if (await partial.exists()) await partial.delete();
    }
  }

  Future<void> prune({String? keepPath}) async {
    if (!await directory.exists()) return;
    final files = await directory
        .list()
        .where((entity) => entity is File && !entity.path.endsWith('.part'))
        .cast<File>()
        .toList();
    files.sort(
      (left, right) =>
          right.lastModifiedSync().compareTo(left.lastModifiedSync()),
    );
    var retainedBytes = 0;
    var retainedFiles = 0;
    for (final file in files) {
      final size = await file.length();
      final keep = file.path == keepPath;
      if (!keep &&
          (retainedFiles >= maxFiles || retainedBytes + size > maxBytes)) {
        await file.delete();
        continue;
      }
      retainedFiles++;
      retainedBytes += size;
    }
  }

  String _safeFormat(String? format) {
    final normalized = format?.toLowerCase();
    return const {'m4a', 'mp3', 'wav'}.contains(normalized)
        ? normalized!
        : 'audio';
  }
}
