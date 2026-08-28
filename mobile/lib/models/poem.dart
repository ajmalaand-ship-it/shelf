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
  };
}

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
    required this.locked,
    this.excerpt,
    this.body,
    required this.audioAvailable,
  });

  factory PoemDetail.fromJson(Map<String, dynamic> json) {
    final audio = json['audio'];
    if (audio is! Map<String, dynamic>) {
      throw const FormatException('audio must be an object');
    }
    return PoemDetail(
      id: _int(json, 'id', required: true)!,
      collectionSlug: _string(json, 'collection_slug', required: true)!,
      title: _string(json, 'title'),
      workType: _string(json, 'work_type') ?? 'ORIGINAL',
      originalAuthor: _string(json, 'original_author'),
      translator: _string(json, 'translator'),
      sourceDatePlace: _string(json, 'source_date_place'),
      sourceNote: _string(json, 'source_note'),
      locked: _bool(json, 'locked'),
      excerpt: _string(json, 'excerpt'),
      body: _string(json, 'body'),
      audioAvailable: _bool(audio, 'available'),
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
  final bool locked;
  final String? excerpt;
  final String? body;
  final bool audioAvailable;

  bool get isTranslation => workType == 'TRANSLATION';
  bool get isUntitled => title == null || title!.trim().isEmpty;
  String get displayTitle =>
      isUntitled ? firstNonEmptyLine(body ?? excerpt) : title!.trim();
  String get readableText => locked ? (excerpt ?? '') : (body ?? excerpt ?? '');
}

String firstNonEmptyLine(String? text) {
  if (text == null) return 'بې سرليکه';
  return text
      .split('\n')
      .map((line) => line.trim())
      .firstWhere((line) => line.isNotEmpty, orElse: () => 'بې سرليکه');
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
