import 'package:flutter/foundation.dart';

import '../models/poem.dart';

enum AudioControlState {
  idle,
  loading,
  buffering,
  playing,
  paused,
  completed,
  error,
}

abstract class AudioPlaybackController extends ChangeNotifier {
  int? get activePoemId;
  AudioControlState get state;
  Duration get position;
  Duration? get duration;
  Duration get bufferedPosition;
  double? get cacheProgress;
  String? get errorMessage;

  Future<void> toggle(PoemDetail poem);
  Future<void> retry(PoemDetail poem);
  Future<void> seek(Duration position);
}

class InactiveAudioController extends AudioPlaybackController {
  @override
  int? get activePoemId => null;
  @override
  AudioControlState get state => AudioControlState.idle;
  @override
  Duration get position => Duration.zero;
  @override
  Duration? get duration => null;
  @override
  Duration get bufferedPosition => Duration.zero;
  @override
  double? get cacheProgress => null;
  @override
  String? get errorMessage => null;

  @override
  Future<void> retry(PoemDetail poem) async {}
  @override
  Future<void> seek(Duration position) async {}
  @override
  Future<void> toggle(PoemDetail poem) async {}
}
