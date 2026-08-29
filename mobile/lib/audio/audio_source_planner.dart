import 'dart:io';

import '../models/poem.dart';
import '../repository/poetry_repository.dart';
import 'audio_cache_store.dart';

sealed class AudioLoadPlan {
  const AudioLoadPlan(this.file);
  final File file;
}

class CachedAudioPlan extends AudioLoadPlan {
  const CachedAudioPlan(super.file);
}

class RemoteAudioPlan extends AudioLoadPlan {
  const RemoteAudioPlan(super.file, this.access);
  final AudioAccess access;
}

class AudioSourcePlanner {
  const AudioSourcePlanner(this.repository, this.cache);

  final PoetryDataSource repository;
  final AudioCacheStore cache;

  Future<AudioLoadPlan> resolve(PoemDetail poem) async {
    if (!poem.hasPlayableAudio) {
      throw StateError('Poem has no accessible audio');
    }
    final target = await cache.fileFor(
      poemId: poem.id,
      cacheKey: poem.audioCacheKey!,
      format: poem.audioFormat,
    );
    await cache.removeOtherVersions(poemId: poem.id, keep: target);
    if (await target.exists()) {
      await target.setLastModified(DateTime.now());
      return CachedAudioPlan(target);
    }

    await cache.clearPartial(target);
    final access = await repository.loadAudio(poem.id);
    if (access.cacheKey != poem.audioCacheKey) {
      throw const FormatException('Audio changed; refresh required');
    }
    return RemoteAudioPlan(target, access);
  }
}
