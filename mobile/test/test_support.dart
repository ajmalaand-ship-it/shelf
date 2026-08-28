import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:pitswal/repository/poetry_repository.dart';
import 'package:pitswal/services/api_client.dart';
import 'package:pitswal/services/cache_store.dart';

const appConfigJson = {
  'app_name': 'پېڅوَل',
  'slogan': 'اجمل اند بشپړه شاعري',
  'content_version': 7,
  'min_app_version': '1.0.0',
};

const collectionJson = {
  'title': 'هېندارې او چینې',
  'slug': 'hendaray-aw-chine',
  'author': 'اجمل اند',
  'subtitle': null,
  'description': null,
  'dedication': 'مينې ته',
  'introduction': 'لومړۍ کرښه\n\nدويم بند',
  'foreword_author': null,
  'foreword': null,
  'publication_info': '۱۳۸۵ لمريز — ۱۰۰۰ ټوکه',
  'cover_url': 'https://poetry.ajmalaand.com/storage/covers/test.webp',
  'poem_count': 2,
  'sort_order': 3,
};

const poemSummaryJson = {
  'id': 301,
  'title': null,
  'work_type': 'ORIGINAL',
  'original_author': null,
  'translator': null,
  'excerpt': 'د لومړۍ کرښې پېژندنه\nبله کرښه',
  'locked': false,
  'has_audio': false,
  'audio_duration_seconds': null,
  'sort_order': 1,
};

const translationSummaryJson = {
  'id': 302,
  'title': 'ژمى',
  'work_type': 'TRANSLATION',
  'original_author': 'پروین پژواک',
  'translator': 'اجمل اند',
  'excerpt': 'ژباړل شوی متن',
  'locked': true,
  'has_audio': false,
  'audio_duration_seconds': null,
  'sort_order': 2,
};

Map<String, dynamic> poemDetailJson({
  bool locked = false,
  String? title,
  String? body = 'ټ ډ ړ ږ ښ ڼ ې ۍ\nدويمه کرښه\n\nنوی بند',
  String workType = 'ORIGINAL',
}) => {
  'id': 301,
  'collection_slug': 'hendaray-aw-chine',
  'title': title,
  'work_type': workType,
  'original_author': workType == 'TRANSLATION' ? 'پروین پژواک' : null,
  'translator': workType == 'TRANSLATION' ? 'اجمل اند' : null,
  'source_date_place': 'کابل — ۱۳۸۵',
  'source_note': null,
  'locked': locked,
  'excerpt': 'لنډه برخه\nدويمه کرښه',
  'body': locked ? null : body,
  'audio': {
    'available': false,
    'locked': locked,
    'duration_seconds': null,
    'metadata_url': null,
  },
};

PoetryRepository fixtureRepository({bool failNetwork = false}) {
  final client = MockClient((request) async {
    if (failNetwork) throw http.ClientException('offline');
    final path = request.url.path;
    final Object payload = switch (path) {
      '/api/app-config' => appConfigJson,
      '/api/collections' => {
        'data': [collectionJson],
      },
      '/api/collections/hendaray-aw-chine' => {'data': collectionJson},
      '/api/collections/hendaray-aw-chine/poems' => {
        'data': [poemSummaryJson, translationSummaryJson],
      },
      '/api/poems/301' => {'data': poemDetailJson()},
      _ => {'message': 'Not found'},
    };
    final status = path == '/unknown' ? 404 : 200;
    return http.Response.bytes(
      utf8.encode(jsonEncode(payload)),
      status,
      headers: {'content-type': 'application/json; charset=utf-8'},
    );
  });
  return PoetryRepository(
    api: ApiClient(
      client: client,
      baseUri: Uri.parse('https://poetry.ajmalaand.com/api/'),
    ),
    cache: MemoryCacheStore(),
  );
}

class MemoryCacheStore implements CacheStore {
  final Map<String, String> values = {};

  @override
  Future<Set<String>> keys() async => values.keys.toSet();

  @override
  Future<String?> read(String key) async => values[key];

  @override
  Future<void> remove(String key) async => values.remove(key);

  @override
  Future<void> write(String key, String value) async {
    values[key] = value;
  }
}
