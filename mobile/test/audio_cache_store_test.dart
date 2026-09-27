import 'dart:io';

import 'package:flutter_test/flutter_test.dart';
import 'package:shelf/audio/audio_cache_store.dart';

void main() {
  late Directory directory;

  setUp(() async {
    directory = await Directory.systemTemp.createTemp('shelf-audio-test-');
  });

  tearDown(() async {
    if (await directory.exists()) await directory.delete(recursive: true);
  });

  test('cache miss becomes a persistent cache hit', () async {
    final cache = AudioCacheStore(directory);
    final file = await cache.fileFor(
      poemId: 1,
      cacheKey: 'recording-v1',
      format: 'm4a',
    );
    expect(
      await cache.contains(poemId: 1, cacheKey: 'recording-v1', format: 'm4a'),
      isFalse,
    );

    await file.writeAsBytes([1, 2, 3], flush: true);

    expect(
      await cache.contains(poemId: 1, cacheKey: 'recording-v1', format: 'm4a'),
      isTrue,
    );
  });

  test(
    'replacement identity removes stale poem cache and partial files',
    () async {
      final cache = AudioCacheStore(directory);
      final old = await cache.fileFor(
        poemId: 8,
        cacheKey: 'old',
        format: 'mp3',
      );
      final replacement = await cache.fileFor(
        poemId: 8,
        cacheKey: 'replacement',
        format: 'mp3',
      );
      await old.writeAsBytes([1]);
      await File('${replacement.path}.part').writeAsBytes([2]);

      await cache.removeOtherVersions(poemId: 8, keep: replacement);
      await cache.clearPartial(replacement);

      expect(await old.exists(), isFalse);
      expect(await File('${replacement.path}.part').exists(), isFalse);
    },
  );

  test('bounded pruning keeps only the newest allowed cache files', () async {
    final cache = AudioCacheStore(directory, maxFiles: 2, maxBytes: 10);
    for (var index = 0; index < 3; index++) {
      final file = await cache.fileFor(
        poemId: index,
        cacheKey: 'key-$index',
        format: 'wav',
      );
      await file.writeAsBytes([index, index]);
      await file.setLastModified(DateTime(2026, 1, index + 1));
    }

    await cache.prune();

    final retained = await directory
        .list()
        .where((item) => item is File)
        .length;
    expect(retained, 2);
  });
}
