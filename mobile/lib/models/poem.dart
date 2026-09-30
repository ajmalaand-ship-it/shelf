class PoemSummary {
  const PoemSummary({
    required this.id,
    this.title,
    required this.workType,
    this.originalAuthor,
    this.translator,
    this.excerpt,
    required this.locked,
    required this.hasAudio,
    this.audioDurationSeconds,
    required this.sortOrder,
    this.isActive = true,
    this.isFreeSample = true,
    this.sampleMode = 'full',
    this.hasMore = false,
  });

  factory PoemSummary.fromJson(Map<String, dynamic> json) => PoemSummary(
    id: _int(json, 'id', required: true)!,
    title: _string(json, 'title'),
    workType: _string(json, 'work_type') ?? 'ORIGINAL',
    originalAuthor: _string(json, 'original_author'),
    translator: _string(json, 'translator'),
    excerpt: _string(json, 'excerpt'),
    locked: _bool(json, 'locked'),
    hasAudio: _bool(json, 'has_audio'),
    audioDurationSeconds: _int(json, 'audio_duration_seconds'),
    sortOrder: _int(json, 'sort_order') ?? 0,
    isActive: json['is_active'] == null ? true : _bool(json, 'is_active'),
    sampleMode: _string(json, 'sample_mode') ?? 'none',
    hasMore: json['has_more'] == null
        ? _bool(json, 'locked')
        : _bool(json, 'has_more'),
    isFreeSample: json['is_free_sample'] == null
        ? !(_bool(json, 'locked'))
        : _bool(json, 'is_free_sample'),
  );

  final int id;
  final String? title;
  final String workType;
  final String? originalAuthor;
  final String? translator;
  final String? excerpt;
  final bool locked;
  final bool hasAudio;
  final int? audioDurationSeconds;
  final int sortOrder;
  final bool isActive;
  final bool isFreeSample;
  final String sampleMode;
  final bool hasMore;

  bool get isTranslation => workType == 'TRANSLATION';
  bool get isUntitled => title == null || title!.trim().isEmpty;
  String get displayTitle =>
      isUntitled ? firstNonEmptyLine(excerpt) : title!.trim();

  Map<String, dynamic> toJson() => {
    'id': id,
    'title': title,
    'work_type': workType,
    'original_author': originalAuthor,
    'translator': translator,
    'excerpt': excerpt,
    'locked': locked,
    'has_audio': hasAudio,
    'audio_duration_seconds': audioDurationSeconds,
    'sort_order': sortOrder,
    'is_active': isActive,
    'is_free_sample': isFreeSample,
    'sample_mode': sampleMode,
    'has_more': hasMore,
  };
}

enum PoemLayoutMode { source, couplet, fourLines }

PoemLayoutMode _layoutMode(String? value) => switch (value) {
  'COUPLET' => PoemLayoutMode.couplet,
  'FOUR_LINES' => PoemLayoutMode.fourLines,
  _ => PoemLayoutMode.source,
};

class PoemDetail {
  const PoemDetail({
    required this.id,
    required this.collectionSlug,
    this.title,
    required this.workType,
    this.originalAuthor,
    this.translator,
    this.sourceDatePlace,
    this.sourceNote,
    this.artworkAvailable = false,
    this.artworkLocked = false,
    this.artworkUrl,
    this.artworkCacheKey,
    this.layoutMode = PoemLayoutMode.source,
    required this.locked,
    this.requiresEntitlement = false,
    this.excerpt,
    this.body,
    required this.audioAvailable,
    this.audioLocked = false,
    this.audioDurationSeconds,
    this.audioMetadataUrl,
    this.audioCacheKey,
    this.audioFormat,
    this.audioLabel,
    this.shareAuthorLabel,
    this.isActive = true,
    this.isFreeSample = true,
    this.sampleMode = 'full',
    this.hasMore = false,
  });

  factory PoemDetail.fromJson(
    Map<String, dynamic> json, {
    bool allowLocalMedia = false,
  }) {
    final audio = json['audio'];
    if (audio is! Map<String, dynamic>) {
      throw const FormatException('audio must be an object');
    }
    final artwork = json['artwork'];
    if (artwork != null && artwork is! Map<String, dynamic>) {
      throw const FormatException('artwork must be an object');
    }
    final artworkData = artwork as Map<String, dynamic>?;
    return PoemDetail(
      id: _int(json, 'id', required: true)!,
      collectionSlug: _string(json, 'collection_slug', required: true)!,
      title: _string(json, 'title'),
      workType: _string(json, 'work_type') ?? 'ORIGINAL',
      originalAuthor: _string(json, 'original_author'),
      translator: _string(json, 'translator'),
      sourceDatePlace: _string(json, 'source_date_place'),
      sourceNote: _string(json, 'source_note'),
      artworkAvailable: artworkData == null
          ? false
          : _bool(artworkData, 'available'),
      artworkLocked: artworkData == null ? false : _bool(artworkData, 'locked'),
      artworkUrl: artworkData == null
          ? null
          : _uri(artworkData, 'url', allowLocal: allowLocalMedia),
      artworkCacheKey: artworkData == null
          ? null
          : _string(artworkData, 'cache_key'),
      layoutMode: _layoutMode(_string(json, 'layout_mode')),
      locked: _bool(json, 'locked'),
      requiresEntitlement: json['requires_entitlement'] == null
          ? false
          : _bool(json, 'requires_entitlement'),
      excerpt: _string(json, 'excerpt'),
      body: _string(json, 'body'),
      audioAvailable: _bool(audio, 'available'),
      audioLocked: _bool(audio, 'locked'),
      audioDurationSeconds: _int(audio, 'duration_seconds'),
      audioMetadataUrl: _string(audio, 'metadata_url'),
      audioCacheKey: _string(audio, 'cache_key'),
      audioFormat: _string(audio, 'format'),
      audioLabel: _string(audio, 'label'),
      isActive: json['is_active'] == null ? true : _bool(json, 'is_active'),
      sampleMode: _string(json, 'sample_mode') ?? 'none',
      hasMore: json['has_more'] == null
          ? _bool(json, 'locked')
          : _bool(json, 'has_more'),
      isFreeSample: json['is_free_sample'] == null
          ? !(_bool(json, 'requires_entitlement'))
          : _bool(json, 'is_free_sample'),
    );
  }

  final int id;
  final String collectionSlug;
  final String? title;
  final String workType;
  final String? originalAuthor;
  final String? translator;
  final String? sourceDatePlace;
  final String? sourceNote;
  final bool artworkAvailable;
  final bool artworkLocked;
  final Uri? artworkUrl;
  final String? artworkCacheKey;
  final PoemLayoutMode layoutMode;
  final bool locked;
  final bool requiresEntitlement;
  final String? excerpt;
  final String? body;
  final bool audioAvailable;
  final bool audioLocked;
  final int? audioDurationSeconds;
  final String? audioMetadataUrl;
  final String? audioCacheKey;
  final String? audioFormat;
  final String? audioLabel;
  final String? shareAuthorLabel;
  final bool isActive;
  final bool isFreeSample;
  final String sampleMode;
  final bool hasMore;

  bool get isTranslation => workType == 'TRANSLATION';
  bool get isUntitled => title == null || title!.trim().isEmpty;
  String get displayTitle =>
      isUntitled ? firstNonEmptyLine(body ?? excerpt) : title!.trim();
  String get readableText => locked ? '' : (body ?? excerpt ?? '');
  bool get hasPlayableAudio =>
      audioAvailable && !locked && !audioLocked && audioCacheKey != null;
  String get cardAuthor => shareAuthorLabel ?? 'اجمل اند';
}

class AudioAccess {
  const AudioAccess({
    required this.url,
    required this.cacheKey,
    this.durationSeconds,
    this.format,
  });

  factory AudioAccess.fromJson(Map<String, dynamic> json) {
    if (_bool(json, 'locked')) throw const AudioLockedException();
    final rawUrl = _string(json, 'url', required: true)!;
    final cacheKey = _string(json, 'cache_key', required: true)!;
    final uri = Uri.tryParse(rawUrl);
    if (uri == null || (!uri.isScheme('https') && !uri.isScheme('http'))) {
      throw const FormatException('audio url must be HTTP(S)');
    }
    return AudioAccess(
      url: uri,
      cacheKey: cacheKey,
      durationSeconds: _int(json, 'duration_seconds'),
      format: _string(json, 'format'),
    );
  }

  final Uri url;
  final String cacheKey;
  final int? durationSeconds;
  final String? format;
}

class AudioLockedException implements Exception {
  const AudioLockedException();
}

String firstNonEmptyLine(String? text) {
  if (text == null) return '';
  return text
      .split('\n')
      .map((line) => line.trim())
      .firstWhere((line) => line.isNotEmpty, orElse: () => '');
}

String? _string(
  Map<String, dynamic> json,
  String key, {
  bool required = false,
}) {
  final value = json[key];
  if (value == null && !required) return null;
  if (value is! String) throw FormatException('$key must be a string');
  return value;
}

int? _int(Map<String, dynamic> json, String key, {bool required = false}) {
  final value = json[key];
  if (value == null && !required) return null;
  if (value is! int) throw FormatException('$key must be an integer');
  return value;
}

bool _bool(Map<String, dynamic> json, String key) {
  final value = json[key];
  if (value is! bool) throw FormatException('$key must be a boolean');
  return value;
}

Uri? _uri(Map<String, dynamic> json, String key, {bool allowLocal = false}) {
  final value = _string(json, key);
  if (value == null) return null;
  final uri = Uri.tryParse(value);
  if (uri == null ||
      (!uri.isScheme('https') &&
          !uri.isScheme('http') &&
          !(allowLocal && uri.isScheme('file')))) {
    throw FormatException('$key must be an HTTP(S) URL');
  }
  return uri;
}
