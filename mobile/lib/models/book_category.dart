class BookCategory {
  const BookCategory({
    required this.id,
    required this.slug,
    required this.name,
  });
  factory BookCategory.fromJson(Map<String, dynamic> json) => BookCategory(
    id: json['id'] as int,
    slug: json['slug'] as String,
    name: json['name'] as String,
  );
  final int id;
  final String slug;
  final String name;
  Map<String, dynamic> toJson() => {'id': id, 'slug': slug, 'name': name};
}
