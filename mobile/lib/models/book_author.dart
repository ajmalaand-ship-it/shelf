import 'poetry_collection.dart';

class BookAuthor {
  const BookAuthor({
    required this.id,
    required this.slug,
    required this.name,
    this.biography,
    this.imageUrl,
    this.books = const [],
  });
  factory BookAuthor.fromJson(
    Map<String, dynamic> json, {
    List<PoetryCollection> books = const [],
  }) => BookAuthor(
    id: json['id'] as int,
    slug: json['slug'] as String,
    name: json['name'] as String,
    biography: json['biography'] as String?,
    imageUrl: json['image_url'] as String?,
    books: books,
  );
  final int id;
  final String slug;
  final String name;
  final String? biography;
  final String? imageUrl;
  final List<PoetryCollection> books;
}
