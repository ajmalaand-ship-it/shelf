import 'dart:io';

import '../models/poem.dart';
import '../purchases/entitlement_controller.dart';
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
  const AudioSourcePlanner(this.repository, this.cache, {this.entitlements});

  final PoetryDataSource repository;
  final AudioCacheStore cache;
  final EntitlementController? entitlements;

  Future<AudioLoadPlan> resolve(PoemDetail poem) async {
    if (!poem.hasPlayableAudio) {
      throw StateError('Poem has no accessible audio');
    }
    final access = await repository.loadAudio(poem.id);
    if (access.url.scheme == 'file') {
      final file = File.fromUri(access.url);
      if (!await file.exists()) throw StateError('Download is unavailable');
      return CachedAudioPlan(file);
    }
    if (access.cacheKey != poem.audioCacheKey) {
      throw const FormatException('Audio changed; refresh required');
    }
    final entitlementScope = repository is OwnerPreviewRepository
        ? 'owner-preview'
        : 'sample-only';
    final target = await cache.fileFor(
      poemId: poem.id,
      cacheKey: '$entitlementScope:${poem.audioCacheKey!}',
      format: poem.audioFormat,
    );
    await cache.removeOtherVersions(poemId: poem.id, keep: target);
    if (await target.exists()) {
      await target.setLastModified(DateTime.now());
      return CachedAudioPlan(target);
    }

    await cache.clearPartial(target);
    return RemoteAudioPlan(target, access);
  }
}
