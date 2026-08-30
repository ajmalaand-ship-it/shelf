import 'dart:io';

import 'package:flutter_test/flutter_test.dart';
import 'package:pitswal/audio/audio_cache_store.dart';
import 'package:pitswal/audio/audio_source_planner.dart';
import 'package:pitswal/models/poem.dart';
import 'package:pitswal/repository/poetry_repository.dart';

import 'test_support.dart';

void main() {
  late Directory directory;

  setUp(() async {
    directory = await Directory.systemTemp.createTemp('pitswal-plan-test-');
  });

  tearDown(() async {
    if (await directory.exists()) await directory.delete(recursive: true);
  });

  test(
    'uncached source fetches metadata while cached replay stays offline',
    () async {
      final repository = _AudioRepository();
      final cache = AudioCacheStore(directory);
      final planner = AudioSourcePlanner(repository, cache);
      final poem = PoemDetail.fromJson(
        poemDetailJson(
          audioAvailable: true,
          audioDurationSeconds: 8,
          audioCacheKey: 'recording-v1',
          audioFormat: 'm4a',
        ),
      );

      final miss = await planner.resolve(poem);
      expect(miss, isA<RemoteAudioPlan>());
      expect(repository.requests, 1);
      await miss.file.writeAsBytes([1, 2, 3]);
      repository.offline = true;

      final hit = await planner.resolve(poem);
      expect(hit, isA<CachedAudioPlan>());
      expect(repository.requests, 1);
    },
  );

  test(
    'stale metadata identity is rejected instead of poisoning cache',
    () async {
      final repository = _AudioRepository(cacheKey: 'recording-v2');
      final planner = AudioSourcePlanner(
        repository,
        AudioCacheStore(directory),
      );
      final poem = PoemDetail.fromJson(
        poemDetailJson(
          audioAvailable: true,
          audioCacheKey: 'recording-v1',
          audioFormat: 'm4a',
        ),
      );

      await expectLater(planner.resolve(poem), throwsFormatException);
      expect(directory.listSync(), isEmpty);
    },
  );
}

class _AudioRepository implements PoetryDataSource {
  _AudioRepository({this.cacheKey = 'recording-v1'});

  final String cacheKey;
  int requests = 0;
  bool offline = false;

  @override
  Future<AudioAccess> loadAudio(int poemId) async {
    requests++;
    if (offline) throw const SocketException('offline');
    return AudioAccess(
      url: Uri.parse('https://example.test/audio.m4a'),
      cacheKey: cacheKey,
      durationSeconds: 8,
      format: 'm4a',
    );
  }

  @override
  Future<CatalogueSnapshot?> loadCachedCatalogue() async => null;
  @override
  Future<CollectionBundle> loadCollection(String slug, int contentVersion) =>
      throw UnimplementedError();
  @override
  Future<PoemDetail> loadPoem(
    int id,
    int contentVersion, {
    bool refreshEntitlement = false,
  }) => throw UnimplementedError();
  @override
  Future<CatalogueSnapshot> refreshCatalogue(CatalogueSnapshot? cached) =>
      throw UnimplementedError();
}
