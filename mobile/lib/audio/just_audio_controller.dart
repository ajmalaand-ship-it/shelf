import 'dart:async';
import 'dart:io';

import 'package:audio_service/audio_service.dart';
import 'package:audio_session/audio_session.dart';
import 'package:just_audio/just_audio.dart';

import '../models/poem.dart';
import '../purchases/entitlement_controller.dart';
import '../repository/poetry_repository.dart';
import 'audio_cache_store.dart';
import 'audio_playback_controller.dart';
import 'audio_source_planner.dart';

class JustAudioController extends AudioPlaybackController {
  JustAudioController._(
    this._repository,
    this._cache,
    this._player,
    this._entitlements,
  );

  PoetryDataSource _repository;
  PoetryDataSource? _defaultRepository;
  int _audioGeneration = 0;
  final AudioCacheStore _cache;
  final AudioPlayer _player;
  final EntitlementController? _entitlements;
  AudioSourcePlanner get _planner =>
      AudioSourcePlanner(_repository, _cache, entitlements: _entitlements);
  final List<StreamSubscription<dynamic>> _subscriptions = [];
  StreamSubscription<double>? _downloadSubscription;

  int? _activePoemId;
  AudioControlState _state = AudioControlState.idle;
  Duration _position = Duration.zero;
  Duration? _duration;
  Duration _bufferedPosition = Duration.zero;
  double? _cacheProgress;
  String? _errorMessage;
  File? _activeCacheFile;

  static Future<JustAudioController> create({
    required PoetryDataSource repository,
    required AudioCacheStore cache,
    EntitlementController? entitlements,
  }) async {
    final player = AudioPlayer(
      handleInterruptions: true,
      handleAudioSessionActivation: true,
    );
    final controller = JustAudioController._(
      repository,
      cache,
      player,
      entitlements,
    );
    final session = await AudioSession.instance;
    await session.configure(AudioSessionConfiguration.speech());
    controller._listen(session);
    await cache.prune();
    return controller;
  }

  @override
  int? get activePoemId => _activePoemId;
  @override
  AudioControlState get state => _state;
  @override
  Duration get position => _position;
  @override
  Duration? get duration => _duration;
  @override
  Duration get bufferedPosition => _bufferedPosition;
  @override
  double? get cacheProgress => _cacheProgress;
  @override
  String? get errorMessage => _errorMessage;

  void _listen(AudioSession session) {
    _subscriptions.addAll([
      _player.positionStream.listen((value) {
        _position = value;
        notifyListeners();
      }),
      _player.durationStream.listen((value) {
        if (value != null) _duration = value;
        notifyListeners();
      }),
      _player.bufferedPositionStream.listen((value) {
        _bufferedPosition = value;
        notifyListeners();
      }),
      _player.playerStateStream.listen((value) {
        _state = switch (value.processingState) {
          ProcessingState.loading => AudioControlState.loading,
          ProcessingState.buffering => AudioControlState.buffering,
          ProcessingState.completed => AudioControlState.completed,
          ProcessingState.idle =>
            _activePoemId == null ? AudioControlState.idle : _state,
          ProcessingState.ready =>
            value.playing
                ? AudioControlState.playing
                : AudioControlState.paused,
        };
        notifyListeners();
      }),
      _player.errorStream.listen((error) {
        _setError('غږ ونه غږېد. بيا هڅه وکړئ.');
      }),
      session.becomingNoisyEventStream.listen(
        (_) => unawaited(_player.pause()),
      ),
    ]);
  }

  @override
  Future<void> toggle(PoemDetail poem) async {
    if (_activePoemId != poem.id ||
        _state == AudioControlState.idle ||
        _state == AudioControlState.error ||
        _state == AudioControlState.completed) {
      await _loadAndPlay(poem);
      return;
    }
    if (_player.playing) {
      await _player.pause();
    } else {
      unawaited(_player.play());
    }
  }

  @override
  Future<void> retry(PoemDetail poem) => _loadAndPlay(poem);

  @override
  Future<void> seek(Duration position) async {
    final limit = _duration;
    final target = limit != null && position > limit ? limit : position;
    await _player.seek(target < Duration.zero ? Duration.zero : target);
  }

  Future<void> useRepository(PoetryDataSource repository) async {
    if (identical(repository, _repository)) return;
    _defaultRepository ??= _repository;
    ++_audioGeneration;
    _repository = repository;
    await _player.stop();
    _activePoemId = null;
    _activeCacheFile = null;
    _state = AudioControlState.idle;
    notifyListeners();
  }

  Future<void> restoreRepository(PoetryDataSource expected) async {
    if (identical(_repository, expected) && _defaultRepository != null) {
      await useRepository(_defaultRepository!);
    }
  }

  Future<void> _loadAndPlay(PoemDetail poem) async {
    if (!poem.hasPlayableAudio) return;
    final generation = ++_audioGeneration;
    await _downloadSubscription?.cancel();
    _downloadSubscription = null;
    _activePoemId = poem.id;
    _state = AudioControlState.loading;
    _position = Duration.zero;
    _bufferedPosition = Duration.zero;
    _duration = poem.audioDurationSeconds == null
        ? null
        : Duration(seconds: poem.audioDurationSeconds!);
    _cacheProgress = null;
    _errorMessage = null;
    notifyListeners();

    try {
      final plan = await _planner.resolve(poem);
      if (generation != _audioGeneration) return;
      final target = plan.file;
      _activeCacheFile = target;

      final mediaItem = MediaItem(
        id: 'poem:${poem.id}:${poem.audioCacheKey}',
        album: 'Shelf',
        title: poem.displayTitle,
        artist: poem.isTranslation && poem.translator != null
            ? 'پښتو ژباړه: ${poem.translator}'
            : null,
        duration: _duration,
      );

      if (plan is CachedAudioPlan) {
        await _player.setAudioSource(
          AudioSource.file(target.path, tag: mediaItem),
        );
        _cacheProgress = 1;
      } else if (plan is RemoteAudioPlan) {
        // Roadmap-approved single-request streaming cache. The package marks
        // this API experimental, so changes are pinned and covered by tests.
        // ignore: experimental_member_use
        final source = LockCachingAudioSource(
          plan.access.url,
          cacheFile: target,
          tag: mediaItem,
        );
        _downloadSubscription = source.downloadProgressStream.listen((value) {
          _cacheProgress = value;
          notifyListeners();
          if (value >= 1) {
            unawaited(_cache.prune(keepPath: target.path));
          }
        });
        await _player.setAudioSource(source);
      }
      if (generation == _audioGeneration) unawaited(_player.play());
    } on AudioLockedException {
      if (generation == _audioGeneration) _setError('دا غږ تړلی دی.');
    } catch (_) {
      if (generation != _audioGeneration) return;
      final activeCacheFile = _activeCacheFile;
      if (activeCacheFile != null) {
        await _cache.clearPartial(activeCacheFile);
      }
      _setError('غږ اوس ترلاسه نه شو. بيا هڅه وکړئ.');
    }
  }

  void _setError(String message) {
    _state = AudioControlState.error;
    _errorMessage = message;
    notifyListeners();
  }

  @override
  void dispose() {
    unawaited(_downloadSubscription?.cancel());
    for (final subscription in _subscriptions) {
      unawaited(subscription.cancel());
    }
    unawaited(_player.dispose());
    super.dispose();
  }
}
