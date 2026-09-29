import 'dart:async';

import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../models/book_category.dart';
import '../models/poetry_collection.dart';
import '../repository/poetry_repository.dart';
import '../widgets/bookstore_widgets.dart';
import 'bookstore_navigation.dart';

class SearchScreen extends StatefulWidget {
  const SearchScreen({
    required this.books,
    required this.categories,
    required this.navigation,
    super.key,
  });
  final List<PoetryCollection> books;
  final List<BookCategory> categories;
  final BookstoreNavigation navigation;
  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  final _text = TextEditingController();
  Timer? _debounce;
  String? _language, _category, _type;
  List<PoetryCollection> _results = [];
  bool _loading = false, _failed = false;
  int _request = 0;
  bool get _hasQuery =>
      _text.text.trim().isNotEmpty ||
      _language != null ||
      _category != null ||
      _type != null;
  @override
  void dispose() {
    _debounce?.cancel();
    _text.dispose();
    super.dispose();
  }

  void _schedule() {
    _debounce?.cancel();
    _request++;
    setState(() {
      _loading = _hasQuery;
      _failed = false;
      _results = [];
    });
    _debounce = Timer(const Duration(milliseconds: 300), _search);
  }

  Future<void> _search() async {
    final request = ++_request;
    if (!_hasQuery) {
      setState(() {
        _loading = false;
        _results = [];
      });
      return;
    }
    setState(() {
      _loading = true;
      _failed = false;
    });
    try {
      final source = widget.navigation.repository;
      final List<PoetryCollection> results;
      if (source is BookstoreDataSource) {
        results = await (source as BookstoreDataSource).searchBooks(
          query: _text.text,
          language: _language,
          category: _category,
          bookType: _type,
        );
      } else {
        final query = _text.text.trim().toLowerCase();
        results = widget.books
            .where(
              (book) =>
                  (query.isEmpty ||
                      [
                        book.title,
                        book.subtitle ?? '',
                        book.creditedAuthors ?? '',
                        ...book.authors.map((a) => a.name),
                      ].any((s) => s.toLowerCase().contains(query))) &&
                  (_language == null || book.language == _language) &&
                  (_type == null || book.bookType == _type) &&
                  (_category == null ||
                      book.categories.any((c) => c.slug == _category)),
            )
            .toList();
      }
      if (mounted && request == _request)
        setState(() {
          _results = results;
          _loading = false;
        });
    } catch (_) {
      if (mounted && request == _request)
        setState(() {
          _failed = true;
          _loading = false;
        });
    }
  }

  @override
  Widget build(BuildContext context) {
    final languages = {
      'ps',
      'fa',
      ...widget.books.map((b) => b.language).whereType<String>(),
    }.toList()..sort();
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        TextField(
          key: const Key('book-search'),
          controller: _text,
          onChanged: (_) => _schedule(),
          decoration: const InputDecoration(
            labelText: AppStrings.searchHint,
            prefixIcon: Icon(Icons.search),
          ),
        ),
        const SizedBox(height: 16),
        Wrap(
          spacing: 12,
          runSpacing: 12,
          children: [
            _filter(
              'language-filter',
              AppStrings.language,
              _language,
              {for (final l in languages) l: AppStrings.languageName(l)},
              (v) {
                _language = v;
                _schedule();
              },
            ),
            _filter(
              'category-filter',
              AppStrings.category,
              _category,
              {for (final c in widget.categories) c.slug: c.name},
              (v) {
                _category = v;
                _schedule();
              },
            ),
            _filter(
              'type-filter',
              AppStrings.bookType,
              _type,
              {'poetry': AppStrings.poetry, 'prose': AppStrings.prose},
              (v) {
                _type = v;
                _schedule();
              },
            ),
          ],
        ),
        if (_hasQuery)
          Align(
            alignment: AlignmentDirectional.centerStart,
            child: TextButton(
              onPressed: () {
                _text.clear();
                _language = _category = _type = null;
                _schedule();
              },
              child: const Text(AppStrings.clearFilters),
            ),
          ),
        const SizedBox(height: 24),
        if (_loading)
          const Center(child: CircularProgressIndicator())
        else if (_failed)
          BookstoreError(onRetry: _search)
        else if (!_hasQuery)
          const Text(AppStrings.searchEmpty)
        else if (_results.isEmpty)
          const Text(AppStrings.noResults)
        else
          BookGrid(
            books: _results,
            ownerPreview: widget.navigation.ownerPreview,
            fontFamily: widget.navigation.settings.fontFamily,
            onTap: (b) => widget.navigation.openBook(context, b.slug),
          ),
      ],
    );
  }

  Widget _filter(
    String key,
    String label,
    String? value,
    Map<String, String> options,
    ValueChanged<String?> change,
  ) => SizedBox(
    width: 180,
    child: DropdownButtonFormField<String>(
      key: ValueKey('$key-${value ?? "all"}'),
      initialValue: value,
      isExpanded: true,
      decoration: InputDecoration(labelText: label),
      items: [
        const DropdownMenuItem<String>(
          value: null,
          child: Text(AppStrings.all),
        ),
        ...options.entries.map(
          (e) => DropdownMenuItem(
            value: e.key,
            child: Text(e.value, overflow: TextOverflow.ellipsis),
          ),
        ),
      ],
      onChanged: change,
    ),
  );
}
