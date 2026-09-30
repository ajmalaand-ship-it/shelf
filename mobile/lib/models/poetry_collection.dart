import 'book_credit.dart';
import 'book_category.dart';
import '../l10n/app_strings.dart';

class PoetryCollection {
  const PoetryCollection({
    required this.title,
    required this.slug,
    this.id,
    this.productId,
    this.status,
    this.createdAt,
    this.categories = const [],
    this.bookType = 'poetry',
    this.author,
    this.authors = const [],
    this.language,
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
    this.isActive = true,
  });

  factory PoetryCollection.fromJson(Map<String, dynamic> json) =>
      PoetryCollection(
        title: _string(json, 'title', required: true)!,
        id: _integer(json, 'id'),
        productId: _string(json, 'product_id'),
        status: _string(json, 'status'),
        createdAt: DateTime.tryParse(_string(json, 'created_at') ?? ''),
        categories: (json['categories'] as List? ?? [])
            .map((item) => BookCategory.fromJson(item as Map<String, dynamic>))
            .toList(),
        bookType: _string(json, 'book_type') ?? 'poetry',
        slug: _string(json, 'slug', required: true)!,
        author: _string(json, 'author'),
        authors: _credits(json['authors']),
        language: _string(json, 'language'),
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
        isActive: json['is_active'] == null
            ? true
            : _boolean(json, 'is_active'),
      );

  final String? status;
  final DateTime? createdAt;
  final List<BookCategory> categories;
  String get displayStatus => status ?? (isActive ? 'published' : 'draft');
  final int? id;
  final String? productId;
  final String bookType;
  final String title;
  final String slug;
  final String? author;
  final List<BookCredit> authors;
  final String? language;

  String? get creditedAuthors {
    final names = authors
        .where((credit) => credit.role == 'author')
        .map((credit) => credit.name)
        .join('، ');
    // Old cached/server responses can still contain only the legacy field.
    return names.isNotEmpty ? names : (authors.isEmpty ? author : null);
  }

  String? get creditedTranslators {
    final names = authors
        .where((credit) => credit.role == 'translator')
        .map((credit) => credit.name)
        .join('، ');
    return names.isEmpty ? null : AppStrings.translation(names);
  }

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
  final bool isActive;

  Map<String, dynamic> toJson() => {
    'id': id,
    'product_id': productId,
    'status': status,
    'created_at': createdAt?.toIso8601String(),
    'categories': categories.map((category) => category.toJson()).toList(),
    'book_type': bookType,
    'title': title,
    'slug': slug,
    'author': author,
    'authors': authors.map((credit) => credit.toJson()).toList(growable: false),
    'language': language,
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
    'is_active': isActive,
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

bool _boolean(Map<String, dynamic> json, String key) {
  final value = json[key];
  if (value is! bool) throw FormatException('$key must be a boolean');
  return value;
}

List<BookCredit> _credits(dynamic value) {
  if (value == null) return const [];
  if (value is! List) throw const FormatException('authors must be a list');
  return value
      .map((entry) {
        if (entry is! Map<String, dynamic>) {
          throw const FormatException('Invalid book credit');
        }
        return BookCredit.fromJson(entry);
      })
      .toList(growable: false);
}
