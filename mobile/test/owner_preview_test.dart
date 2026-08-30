import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:pitswal/app.dart';
import 'package:pitswal/models/app_config.dart';
import 'package:pitswal/models/poem.dart';
import 'package:pitswal/models/poetry_collection.dart';
import 'package:pitswal/repository/poetry_repository.dart';
import 'package:pitswal/services/api_client.dart';
import 'package:pitswal/settings/reader_settings.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'test_support.dart';

void main() {
  test(
    'preview API sends bearer token only to configured preview host',
    () async {
      late http.Request seen;
      final client = MockClient((request) async {
        seen = request;
        return http.Response('{}', 200);
      });
      final api = ApiClient(
        client: client,
        baseUri: Uri.parse('https://poetry.ajmalaand.com/api/owner-preview/'),
        authorizationToken: 'temporary-preview-token',
      );

      await api.getObject('app-config');
      expect(seen.url.host, 'poetry.ajmalaand.com');
      expect(seen.headers['Authorization'], 'Bearer temporary-preview-token');
    },
  );

  test(
    'owner preview and public repositories use disjoint cache keys',
    () async {
      final cache = MemoryCacheStore();
      MockClient client() => MockClient((request) async {
        final payload = request.url.path.endsWith('/app-config')
            ? appConfigJson
            : {
                'data': [collectionJson],
              };
        return http.Response.bytes(
          utf8.encode(jsonEncode(payload)),
          200,
          headers: {'content-type': 'application/json; charset=utf-8'},
        );
      });
      final public = PoetryRepository(
        api: ApiClient(client: client()),
        cache: cache,
      );
      final preview = OwnerPreviewRepository(
        api: ApiClient(client: client()),
        cache: cache,
      );

      await public.refreshCatalogue(null);
      await preview.refreshCatalogue(null);

      expect(cache.values, contains('pitswal.catalogue.v1'));
      expect(cache.values, contains('pitswal.owner-preview.catalogue.v1'));
    },
  );

  testWidgets(
    'owner preview banner and draft/free/locked metadata are visible',
    (tester) async {
      SharedPreferences.setMockInitialValues({});
      final settings = await ReaderSettings.load(
        await SharedPreferences.getInstance(),
      );
      await tester.pumpWidget(
        PitswalApp(
          repository: _PreviewDataSource(),
          readerSettings: settings,
          ownerPreviewMode: true,
        ),
      );
      await tester.pumpAndSettle();

      expect(find.byKey(const Key('owner-preview-banner')), findsOneWidget);
      expect(find.text('څپو کې انځورونه'), findsOneWidget);
      expect(find.text('مسوده'), findsOneWidget);

      await tester.tap(find.text('څپو کې انځورونه'));
      await tester.pumpAndSettle();
      expect(
        find.byKey(const Key('owner-preview-poem-status')),
        findsOneWidget,
      );
      expect(find.textContaining('مسوده • تړلی'), findsOneWidget);

      await tester.tap(find.text('ژمى'));
      await tester.pumpAndSettle();
      expect(
        find.byKey(const Key('owner-preview-reader-status')),
        findsOneWidget,
      );
      expect(find.text('ټ ډ ړ ږ ښ ڼ ې ۍ'), findsOneWidget);
      expect(find.byKey(const Key('reader-share')), findsOneWidget);
    },
  );
}

class _PreviewDataSource implements PoetryDataSource {
  static const collection = PoetryCollection(
    title: 'څپو کې انځورونه',
    slug: 'draft-real-review',
    author: 'اجمل اند',
    poemCount: 1,
    sortOrder: 1,
    isActive: false,
  );

  @override
  Future<CatalogueSnapshot?> loadCachedCatalogue() async => null;

  @override
  Future<CatalogueSnapshot> refreshCatalogue(CatalogueSnapshot? cached) async =>
      const CatalogueSnapshot(
        config: AppConfig(
          appName: 'پېڅوَل',
          slogan: 'اجمل اند بشپړه شاعري',
          contentVersion: 17,
          minAppVersion: '1.0.0',
        ),
        collections: [collection],
        fromCache: false,
      );

  @override
  Future<CollectionBundle> loadCollection(
    String slug,
    int contentVersion,
  ) async => const CollectionBundle(
    collection: collection,
    poems: [
      PoemSummary(
        id: 1,
        title: 'ژمى',
        workType: 'TRANSLATION',
        originalAuthor: 'پروین پژواک',
        translator: 'اجمل اند',
        excerpt: 'ټ ډ ړ',
        locked: false,
        hasAudio: false,
        sortOrder: 1,
        isActive: false,
        isFreeSample: false,
      ),
    ],
  );

  @override
  Future<PoemDetail> loadPoem(
    int id,
    int contentVersion, {
    bool refreshEntitlement = false,
  }) async => const PoemDetail(
    id: 1,
    collectionSlug: 'draft-real-review',
    title: 'ژمى',
    workType: 'TRANSLATION',
    originalAuthor: 'پروین پژواک',
    translator: 'اجمل اند',
    locked: false,
    requiresEntitlement: true,
    excerpt: 'ټ ډ ړ',
    body: 'ټ ډ ړ ږ ښ ڼ ې ۍ',
    audioAvailable: false,
    isActive: false,
    isFreeSample: false,
  );

  @override
  Future<AudioAccess> loadAudio(int poemId) => throw UnimplementedError();
}
