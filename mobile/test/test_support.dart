import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shelf/audio/audio_playback_controller.dart';
import 'package:shelf/models/poem.dart';
import 'package:shelf/repository/poetry_repository.dart';
import 'package:shelf/services/api_client.dart';
import 'package:shelf/services/cache_store.dart';

const appConfigJson = {
  'app_name': 'Shelf',
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
  'cover_url': 'https://shelf.services/storage/covers/test.webp',
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
  bool audioAvailable = false,
  bool audioLocked = false,
  int? audioDurationSeconds,
  String? audioCacheKey,
  String? audioFormat,
  String? audioLabel,
  String? artworkUrl,
  String layoutMode = 'SOURCE',
  String? sourceDatePlace = 'کابل — ۱۳۸۵',
}) => {
  'id': 301,
  'collection_slug': 'hendaray-aw-chine',
  'title': title,
  'work_type': workType,
  'original_author': workType == 'TRANSLATION' ? 'پروین پژواک' : null,
  'translator': workType == 'TRANSLATION' ? 'اجمل اند' : null,
  'source_date_place': sourceDatePlace,
  'source_note': null,
  'layout_mode': layoutMode,
  'locked': locked,
  'requires_entitlement': locked,
  'excerpt': 'لنډه برخه\nدويمه کرښه',
  'body': locked ? null : body,
  'artwork': {
    'available': artworkUrl != null,
    'locked': locked,
    'url': locked ? null : artworkUrl,
    'cache_key': artworkUrl == null ? null : 'artwork-v1',
  },
  'audio': {
    'available': audioAvailable,
    'locked': audioLocked || locked,
    'duration_seconds': audioDurationSeconds,
    'metadata_url': audioAvailable ? '/api/poems/301/audio' : null,
    'cache_key': audioCacheKey,
    'format': audioFormat,
    'label': audioLabel,
  },
};

PoetryRepository fixtureRepository({bool failNetwork = false}) {
  final client = MockClient((request) async {
    if (failNetwork) throw http.ClientException('offline');
    final path = request.url.path;
    final Object payload = switch (path) {
      '/api/app-config' => appConfigJson,
      '/api/authors' || '/api/categories' => {'data': []},
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
      baseUri: Uri.parse('https://shelf.services/api/'),
    ),
    cache: MemoryCacheStore(),
  );
}

class FakeAudioController extends AudioPlaybackController {
  int? poemId;
  AudioControlState controlState = AudioControlState.idle;
  Duration currentPosition = Duration.zero;
  Duration? currentDuration;
  Duration currentBufferedPosition = Duration.zero;
  double? currentCacheProgress;
  String? currentError;
  int toggleCount = 0;
  int retryCount = 0;
  Duration? lastSeek;

  @override
  int? get activePoemId => poemId;
  @override
  AudioControlState get state => controlState;
  @override
  Duration get position => currentPosition;
  @override
  Duration? get duration => currentDuration;
  @override
  Duration get bufferedPosition => currentBufferedPosition;
  @override
  double? get cacheProgress => currentCacheProgress;
  @override
  String? get errorMessage => currentError;

  @override
  Future<void> toggle(PoemDetail poem) async {
    toggleCount++;
    poemId = poem.id;
    controlState = controlState == AudioControlState.playing
        ? AudioControlState.paused
        : AudioControlState.playing;
    currentDuration ??= poem.audioDurationSeconds == null
        ? null
        : Duration(seconds: poem.audioDurationSeconds!);
    notifyListeners();
  }

  @override
  Future<void> retry(PoemDetail poem) async {
    retryCount++;
    await toggle(poem);
  }

  @override
  Future<void> seek(Duration position) async {
    lastSeek = position;
    currentPosition = position;
    notifyListeners();
  }

  void emit({
    required int activePoemId,
    required AudioControlState state,
    Duration position = Duration.zero,
    Duration? duration,
    Duration buffered = Duration.zero,
    double? cacheProgress,
    String? error,
  }) {
    poemId = activePoemId;
    controlState = state;
    currentPosition = position;
    currentDuration = duration;
    currentBufferedPosition = buffered;
    currentCacheProgress = cacheProgress;
    currentError = error;
    notifyListeners();
  }
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
