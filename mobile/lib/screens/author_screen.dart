import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../models/book_author.dart';
import '../repository/poetry_repository.dart';
import '../widgets/bookstore_widgets.dart';
import 'bookstore_navigation.dart';

class AuthorScreen extends StatefulWidget {
  const AuthorScreen({required this.slug, required this.navigation, super.key});
  final String slug;
  final BookstoreNavigation navigation;
  @override
  State<AuthorScreen> createState() => _AuthorScreenState();
}

class _AuthorScreenState extends State<AuthorScreen> {
  late Future<BookAuthor> _future;
  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<BookAuthor> _load() async {
    final repository = widget.navigation.repository;
    if (repository is BookstoreDataSource)
      return (repository as BookstoreDataSource).loadAuthor(widget.slug);
    final snapshot = await repository.refreshCatalogue(null);
    final books = snapshot.collections
        .where((b) => b.authors.any((a) => a.slug == widget.slug))
        .toList();
    final person = books
        .expand((b) => b.authors)
        .where((a) => a.slug == widget.slug)
        .first;
    return BookAuthor(
      id: person.id,
      slug: person.slug,
      name: person.name,
      books: books,
    );
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: Text(AppStrings.of(context).authors)),
    body: FutureBuilder<BookAuthor>(
      future: _future,
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done)
          return const Center(child: CircularProgressIndicator());
        if (!snapshot.hasData)
          return BookstoreError(
            onRetry: () => setState(() => _future = _load()),
          );
        final author = snapshot.requireData;
        return ListView(
          padding: const EdgeInsets.all(20),
          children: [
            Center(child: AuthorPortrait(author: author, radius: 52)),
            const SizedBox(height: 16),
            Text(
              author.name,
              textDirection: TextDirection.rtl,
              textAlign: TextAlign.right,
              style: Theme.of(context).textTheme.headlineMedium,
            ),
            SectionTitle(AppStrings.of(context).biography),
            Text(
              textDirection: TextDirection.rtl,
              textAlign: TextAlign.right,
              author.biography?.isNotEmpty == true
                  ? author.biography!
                  : AppStrings.of(context).noBiography,
            ),
            SectionTitle(AppStrings.of(context).allBooks),
            if (author.books.isEmpty)
              Text(AppStrings.of(context).noBooks)
            else
              BookGrid(
                books: author.books,
                ownerPreview: widget.navigation.ownerPreview,
                fontFamily: widget.navigation.settings.fontFamily,
                onTap: (book) => widget.navigation.openBook(context, book.slug),
              ),
          ],
        );
      },
    ),
  );
}
