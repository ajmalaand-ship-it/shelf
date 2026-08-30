import 'dart:convert';

import 'package:crypto/crypto.dart';

import '../models/app_config.dart';
import '../models/poem.dart';
import '../models/poetry_collection.dart';
import '../purchases/entitlement_controller.dart';
import '../services/api_client.dart';
import '../services/cache_store.dart';

class CatalogueSnapshot {
  const CatalogueSnapshot({
    required this.config,
    required this.collections,
    required this.fromCache,
    this.refreshError,
  });

  final AppConfig config;
  final List<PoetryCollection> collections;
  final bool fromCache;
  final Object? refreshError;

  CatalogueSnapshot withError(Object error) => CatalogueSnapshot(
    config: config,
    collections: collections,
    fromCache: fromCache,
    refreshError: error,
  );
}

class CollectionBundle {
  const CollectionBundle({required this.collection, required this.poems});
  final PoetryCollection collection;
  final List<PoemSummary> poems;
}

abstract interface class PoetryDataSource {
  Future<CatalogueSnapshot?> loadCachedCatalogue();
  Future<CatalogueSnapshot> refreshCatalogue(CatalogueSnapshot? cached);
  Future<CollectionBundle> loadCollection(String slug, int contentVersion);
  Future<PoemDetail> loadPoem(
    int id,
    int contentVersion, {
    bool refreshEntitlement = false,
  });
  Future<AudioAccess> loadAudio(int poemId);
}

class PoetryRepository implements PoetryDataSource {
  PoetryRepository({
    required ApiClient api,
    required CacheStore cache,
    EntitlementController? entitlements,
    String cacheNamespace = 'public',
  }) : this._(api, cache, entitlements, cacheNamespace);

  PoetryRepository._(
    this._api,
    this._cache,
    this._entitlements,
    this._cacheNamespace,
  );

  final ApiClient _api;
  final CacheStore _cache;
  final EntitlementController? _entitlements;
  final String _cacheNamespace;

  String get _catalogueKey => _cacheNamespace == 'public'
      ? 'pitswal.catalogue.v1'
      : 'pitswal.$_cacheNamespace.catalogue.v1';
  String get _contentPrefix => _cacheNamespace == 'public'
      ? 'pitswal.content.v1.'
      : 'pitswal.$_cacheNamespace.content.v1.';

  @override
  Future<CatalogueSnapshot?> loadCachedCatalogue() async {
    final raw = await _cache.read(_catalogueKey);
    if (raw == null) return null;
    try {
      final json = _asMap(jsonDecode(raw));
      return CatalogueSnapshot(
        config: AppConfig.fromJson(_asMap(json['config'])),
        collections: _asList(json['collections'])
            .map((item) => PoetryCollection.fromJson(_asMap(item)))
            .toList(growable: false),
        fromCache: true,
      );
    } on FormatException {
      await _cache.remove(_catalogueKey);
      return null;
    }
  }

  @override
  Future<CatalogueSnapshot> refreshCatalogue(CatalogueSnapshot? cached) async {
    try {
      final config = AppConfig.fromJson(await _api.getObject('app-config'));
      if (cached != null &&
          cached.config.contentVersion == config.contentVersion) {
        return CatalogueSnapshot(
          config: config,
          collections: cached.collections,
          fromCache: false,
        );
      }

      final collectionJson = await _api.getDataList('collections');
      final collections = collectionJson
          .map(PoetryCollection.fromJson)
          .toList(growable: false);
      final encoded = jsonEncode({
        'config': config.toJson(),
        'collections': collectionJson,
      });

      // Commit a complete, validated snapshot only after both requests succeed.
      await _cache.write(_catalogueKey, encoded);
      if (cached != null &&
          cached.config.contentVersion != config.contentVersion) {
        await _clearVersionedContent();
      }
      return CatalogueSnapshot(
        config: config,
        collections: collections,
        fromCache: false,
      );
    } catch (error) {
      if (cached != null) return cached.withError(error);
      rethrow;
    }
  }

  @override
  Future<CollectionBundle> loadCollection(
    String slug,
    int contentVersion,
  ) async {
    final key =
        '$_contentPrefix${_accessScope()}.$contentVersion.collection.$slug';
    try {
      final responses = await Future.wait([
        _api.getDataObject('collections/${Uri.encodeComponent(slug)}'),
        _api.getDataList('collections/${Uri.encodeComponent(slug)}/poems'),
      ]);
      final collectionJson = responses[0] as Map<String, dynamic>;
      final poemsJson = responses[1] as List<Map<String, dynamic>>;
      final bundle = _parseBundle(collectionJson, poemsJson);
      await _cache.write(
        key,
        jsonEncode({'collection': collectionJson, 'poems': poemsJson}),
      );
      return bundle;
    } on ContentNotFoundException {
      await _cache.remove(key);
      rethrow;
    } catch (networkError) {
      final cached = await _cache.read(key);
      if (cached == null) rethrow;
      final json = _asMap(jsonDecode(cached));
      return _parseBundle(
        _asMap(json['collection']),
        _asList(json['poems']).map(_asMap).toList(growable: false),
      );
    }
  }

  @override
  Future<PoemDetail> loadPoem(
    int id,
    int contentVersion, {
    bool refreshEntitlement = false,
  }) async {
    final key = '$_contentPrefix${_accessScope()}.$contentVersion.poem.$id';
    try {
      final json = await _api.getDataObject(
        'poems/$id',
        refreshEntitlement: refreshEntitlement,
      );
      final poem = PoemDetail.fromJson(json);
      await _cache.write(key, jsonEncode(json));
      return poem;
    } on ContentNotFoundException {
      await _cache.remove(key);
      rethrow;
    } catch (networkError) {
      final cached = await _cache.read(key);
      if (cached == null) rethrow;
      return PoemDetail.fromJson(_asMap(jsonDecode(cached)));
    }
  }

  @override
  Future<AudioAccess> loadAudio(int poemId) async =>
      AudioAccess.fromJson(await _api.getObject('poems/$poemId/audio'));

  String _accessScope() {
    final entitlements = _entitlements;
    if (entitlements == null ||
        !entitlements.entitled ||
        entitlements.userId == null) {
      return 'public';
    }
    final digest = sha256.convert(utf8.encode(entitlements.userId!)).toString();
    return 'paid.${digest.substring(0, 16)}';
  }

  CollectionBundle _parseBundle(
    Map<String, dynamic> collectionJson,
    List<Map<String, dynamic>> poemsJson,
  ) => CollectionBundle(
    collection: PoetryCollection.fromJson(collectionJson),
    poems: poemsJson.map(PoemSummary.fromJson).toList(growable: false),
  );

  Future<void> _clearVersionedContent() async {
    final keys = await _cache.keys();
    await Future.wait(
      keys.where((key) => key.startsWith(_contentPrefix)).map(_cache.remove),
    );
  }
}

class OwnerPreviewRepository extends PoetryRepository {
  OwnerPreviewRepository({required super.api, required super.cache})
    : super(cacheNamespace: 'owner-preview');
}

Map<String, dynamic> _asMap(dynamic value) {
  if (value is! Map<String, dynamic>) {
    throw const FormatException('Expected a JSON object');
  }
  return value;
}

List<dynamic> _asList(dynamic value) {
  if (value is! List<dynamic>) {
    throw const FormatException('Expected a JSON list');
  }
  return value;
}
