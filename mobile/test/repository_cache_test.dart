import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:pitswal/repository/poetry_repository.dart';
import 'package:pitswal/services/api_client.dart';

import 'test_support.dart';

void main() {
  test(
    'content version refresh replaces catalogue only after valid responses',
    () async {
      final cache = MemoryCacheStore();
      final oldConfig = {...appConfigJson, 'content_version': 6};
      cache.values['pitswal.catalogue.v1'] = jsonEncode({
        'config': oldConfig,
        'collections': [
          {...collectionJson, 'title': 'پخوانۍ ټولګه'},
        ],
      });
      cache.values['pitswal.content.v1.6.poem.1'] = '{}';
      final repository = PoetryRepository(
        api: ApiClient(
          client: MockClient((request) async {
            final payload = request.url.path.endsWith('app-config')
                ? appConfigJson
                : {
                    'data': [collectionJson],
                  };
            return http.Response.bytes(
              utf8.encode(jsonEncode(payload)),
              200,
              headers: {'content-type': 'application/json; charset=utf-8'},
            );
          }),
          baseUri: Uri.parse('https://poetry.ajmalaand.com/api/'),
        ),
        cache: cache,
      );

      final cached = await repository.loadCachedCatalogue();
      final refreshed = await repository.refreshCatalogue(cached);

      expect(refreshed.config.contentVersion, 7);
      expect(refreshed.collections.single.title, 'هېندارې او چینې');
      expect(cache.values, isNot(contains('pitswal.content.v1.6.poem.1')));
    },
  );

  test('audio metadata parses signed URL and stable cache identity', () async {
    final repository = PoetryRepository(
      api: ApiClient(
        client: MockClient(
          (_) async => http.Response.bytes(
            utf8.encode(
              jsonEncode({
                'locked': false,
                'url': 'https://poetry.ajmalaand.com/media/audio/301?signature=test',
                'duration_seconds': 75,
                'cache_key': 'stable-recording-v1',
                'format': 'm4a',
              }),
            ),
            200,
            headers: {'content-type': 'application/json; charset=utf-8'},
          ),
        ),
      ),
      cache: MemoryCacheStore(),
    );

    final audio = await repository.loadAudio(301);

    expect(audio.url.isScheme('https'), isTrue);
    expect(audio.cacheKey, 'stable-recording-v1');
    expect(audio.durationSeconds, 75);
  });

  test('failed refresh preserves a valid offline catalogue', () async {
    final cache = MemoryCacheStore();
    cache.values['pitswal.catalogue.v1'] = jsonEncode({
      'config': appConfigJson,
      'collections': [collectionJson],
    });
    final repository = PoetryRepository(
      api: ApiClient(
        client: MockClient((_) async => throw http.ClientException('offline')),
      ),
      cache: cache,
    );

    final cached = await repository.loadCachedCatalogue();
    final result = await repository.refreshCatalogue(cached);

    expect(result.fromCache, isTrue);
    expect(result.refreshError, isA<http.ClientException>());
    expect(result.collections.single.title, 'هېندارې او چینې');
  });

  test(
    'collection and poem content fall back to versioned cache offline',
    () async {
      final cache = MemoryCacheStore();
      final online = PoetryRepository(
        api: ApiClient(
          client: MockClient((request) async {
            final payload = switch (request.url.path) {
              '/api/collections/hendaray-aw-chine' => {'data': collectionJson},
              '/api/collections/hendaray-aw-chine/poems' => {
                'data': [poemSummaryJson],
              },
              '/api/poems/301' => {'data': poemDetailJson()},
              _ => throw StateError('unexpected'),
            };
            return http.Response.bytes(
              utf8.encode(jsonEncode(payload)),
              200,
              headers: {'content-type': 'application/json; charset=utf-8'},
            );
          }),
        ),
        cache: cache,
      );
      await online.loadCollection('hendaray-aw-chine', 7);
      await online.loadPoem(301, 7);
      final offline = PoetryRepository(
        api: ApiClient(
          client: MockClient(
            (_) async => throw http.ClientException('offline'),
          ),
        ),
        cache: cache,
      );

      final bundle = await offline.loadCollection('hendaray-aw-chine', 7);
      final poem = await offline.loadPoem(301, 7);

      expect(bundle.poems.single.displayTitle, 'د لومړۍ کرښې پېژندنه');
      expect(poem.body, contains('ټ ډ ړ ږ ښ ڼ ې ۍ'));
    },
  );

  test(
    'authoritative 404 never falls back to previously cached content',
    () async {
      final cache = MemoryCacheStore();
      cache.values['pitswal.content.v1.7.poem.301'] = jsonEncode(
        poemDetailJson(),
      );
      final repository = PoetryRepository(
        api: ApiClient(
          client: MockClient((_) async => http.Response('{}', 404)),
        ),
        cache: cache,
      );

      await expectLater(
        repository.loadPoem(301, 7),
        throwsA(isA<ContentNotFoundException>()),
      );
      expect(cache.values, isNot(contains('pitswal.content.v1.7.poem.301')));
    },
  );
}
