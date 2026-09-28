import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shelf/models/poetry_collection.dart';
import 'package:shelf/repository/poetry_repository.dart';
import 'package:shelf/services/api_client.dart';
import 'package:shelf/services/cache_store.dart';

import 'test_support.dart';

void main() {
  test('repository loads all book and content pages before caching', () async {
    final calls = <String>[];
    final cache = MemoryCacheStore();
    final repository = PoetryRepository(
      cache: cache,
      api: ApiClient(
        baseUri: Uri.parse('https://example.test/api/'),
        client: MockClient((request) async {
          calls.add(request.url.toString());
          final second = request.url.queryParameters['page'] == '2';
          final path = request.url.path;
          Object response;
          if (path.endsWith('app-config')) {
            response = appConfigJson;
          } else if (path.endsWith('/poems')) {
            response = {
              'data': [second ? translationSummaryJson : poemSummaryJson],
              'links': {'next': second ? null : '?page=2'},
            };
          } else if (path.endsWith('/collections')) {
            response = {
              'data': [
                {
                  ...collectionJson,
                  'slug': second ? 'second-book' : collectionJson['slug'],
                },
              ],
              'links': {
                'next': second
                    ? null
                    : 'https://example.test/api/collections?page=2',
              },
            };
          } else {
            response = {'data': collectionJson};
          }
          return http.Response.bytes(utf8.encode(jsonEncode(response)), 200);
        }),
      ),
    );
    final catalogue = await repository.refreshCatalogue(null);
    expect(catalogue.collections, hasLength(2));
    expect((await repository.loadCachedCatalogue())!.collections, hasLength(2));
    final bundle = await repository.loadCollection('hendaray-aw-chine', 7);
    expect(bundle.poems.map((item) => item.id), [301, 302]);
    expect(calls.where((url) => url.contains('page=2')), hasLength(2));
  });

  test(
    'pagination rejects foreign links, loops and later-page errors',
    () async {
      for (final next in [
        'https://other.test/api/collections?page=2',
        'https://example.test/outside?page=2',
        'https://example.test/api/collections',
      ]) {
        var requests = 0;
        final api = ApiClient(
          baseUri: Uri.parse('https://example.test/api/'),
          authorizationToken: 'test-token',
          client: MockClient((_) async {
            requests++;
            return http.Response(
              jsonEncode({
                'data': [],
                'links': {'next': next},
              }),
              200,
            );
          }),
        );
        await expectLater(
          api.getDataList('collections'),
          throwsFormatException,
        );
        expect(requests, 1);
      }
      final api = ApiClient(
        baseUri: Uri.parse('https://example.test/api/'),
        client: MockClient(
          (request) async => request.url.hasQuery
              ? http.Response('{}', 500)
              : http.Response.bytes(
                  utf8.encode(
                    jsonEncode({
                      'data': [collectionJson],
                      'links': {'next': '?page=2'},
                    }),
                  ),
                  200,
                ),
        ),
      );
      await expectLater(
        api.getDataList('collections'),
        throwsA(isA<ApiException>()),
      );
    },
  );

  test('stable ID and prose type survive cache serialization; old data defaults to poetry', () {
    final prose = PoetryCollection.fromJson({
      ...collectionJson,
      'id': 8,
      'book_type': 'prose',
    });
    final cached = PoetryCollection.fromJson(prose.toJson());
    expect(cached.id, 8);
    expect(cached.bookType, 'prose');
    expect(PoetryCollection.fromJson(collectionJson).bookType, 'poetry');
  });
}
