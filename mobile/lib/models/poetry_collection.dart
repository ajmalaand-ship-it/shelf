class PoetryCollection {
  const PoetryCollection({
    required this.title,
    required this.slug,
    this.author,
    this.subtitle,
    this.description,
    this.dedication,
    this.introduction,
    this.forewordAuthor,
    this.foreword,
    this.publicationInfo,
    this.coverUrl,
    this.poemCount,
    required this.sortOrder,
  });

  factory PoetryCollection.fromJson(Map<String, dynamic> json) =>
      PoetryCollection(
        title: _string(json, 'title', required: true)!,
        slug: _string(json, 'slug', required: true)!,
        author: _string(json, 'author'),
        subtitle: _string(json, 'subtitle'),
        description: _string(json, 'description'),
        dedication: _string(json, 'dedication'),
        introduction: _string(json, 'introduction'),
        forewordAuthor: _string(json, 'foreword_author'),
        foreword: _string(json, 'foreword'),
        publicationInfo: _string(json, 'publication_info'),
        coverUrl: _string(json, 'cover_url'),
        poemCount: _integer(json, 'poem_count'),
        sortOrder: _integer(json, 'sort_order') ?? 0,
      );

  final String title;
  final String slug;
  final String? author;
  final String? subtitle;
  final String? description;
  final String? dedication;
  final String? introduction;
  final String? forewordAuthor;
  final String? foreword;
  final String? publicationInfo;
  final String? coverUrl;
  final int? poemCount;
  final int sortOrder;

  Map<String, dynamic> toJson() => {
    'title': title,
    'slug': slug,
    'author': author,
    'subtitle': subtitle,
    'description': description,
    'dedication': dedication,
    'introduction': introduction,
    'foreword_author': forewordAuthor,
    'foreword': foreword,
    'publication_info': publicationInfo,
    'cover_url': coverUrl,
    'poem_count': poemCount,
    'sort_order': sortOrder,
  };
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

int? _integer(Map<String, dynamic> json, String key) {
  final value = json[key];
  if (value == null) return null;
  if (value is! int) throw FormatException('$key must be an integer');
  return value;
}
