class BookCredit {
  const BookCredit({
    required this.id,
    required this.slug,
    required this.name,
    required this.role,
  });

  factory BookCredit.fromJson(Map<String, dynamic> json) {
    if (json['id'] is! int ||
        json['slug'] is! String ||
        json['name'] is! String ||
        json['role'] is! String) {
      throw const FormatException('Invalid book credit');
    }
    return BookCredit(
      id: json['id'] as int,
      slug: json['slug'] as String,
      name: json['name'] as String,
      role: json['role'] as String,
    );
  }

  final int id;
  final String slug;
  final String name;
  final String role;

  Map<String, dynamic> toJson() => {
    'id': id,
    'slug': slug,
    'name': name,
    'role': role,
  };
}
