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
          decoration: InputDecoration(
            labelText: AppStrings.of(context).searchHint,
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
              AppStrings.of(context).language,
              _language,
              {
                for (final l in languages)
                  l: AppStrings.of(context).languageName(l),
              },
              (v) {
                _language = v;
                _schedule();
              },
            ),
            _filter(
              'category-filter',
              AppStrings.of(context).category,
              _category,
              {for (final c in widget.categories) c.slug: c.name},
              (v) {
                _category = v;
                _schedule();
              },
            ),
            _filter(
              'type-filter',
              AppStrings.of(context).bookType,
              _type,
              {
                'poetry': AppStrings.of(context).poetry,
                'prose': AppStrings.of(context).prose,
              },
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
              child: Text(AppStrings.of(context).clearFilters),
            ),
          ),
        const SizedBox(height: 24),
        if (_hasQuery && !_loading && !_failed && _results.isNotEmpty)
          SectionTitle(
            AppStrings.of(context).isEnglish
                ? 'Search results (${_results.length})'
                : 'د لټون پایلې (${_results.length})',
          ),
        if (_loading)
          const Center(child: CircularProgressIndicator())
        else if (_failed)
          BookstoreError(onRetry: _search)
        else if (!_hasQuery)
          Text(AppStrings.of(context).searchEmpty)
        else if (_results.isEmpty)
          Text(AppStrings.of(context).noResults)
        else
          BookList(
            books: _results,
            fontFamily: widget.navigation.settings.fontFamily,
            ownerPreview: widget.navigation.ownerPreview,
            onTap: (b) => widget.navigation.openBook(context, b.slug),
          ),
        if (!_loading && !_failed) ...[
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Theme.of(context).colorScheme.primaryContainer,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(AppStrings.of(context).searchHint),
          ),
        ],
      ],
    );
  }

  Widget _filter(
    String key,
    String label,
    String? value,
    Map<String, String> options,
    ValueChanged<String?> change,
  ) => PopupMenuButton<String>(
    key: ValueKey('$key-${value ?? "all"}'),
    tooltip: label,
    onSelected: (choice) => change(choice == '__all__' ? null : choice),
    itemBuilder: (context) => [
      PopupMenuItem(value: '__all__', child: Text(AppStrings.of(context).all)),
      for (final entry in options.entries)
        PopupMenuItem(value: entry.key, child: Text(entry.value)),
    ],
    child: Container(
      constraints: const BoxConstraints(
        minHeight: 44,
        minWidth: 44,
        maxWidth: 220,
      ),
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.primaryContainer,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Flexible(
            child: Text(
              value == null ? label : options[value] ?? label,
              style: TextStyle(
                fontFamily: 'Vazirmatn',
                fontSize: 13,
                color: Theme.of(context).colorScheme.primary,
              ),
            ),
          ),
          const SizedBox(width: 8),
          Icon(
            Icons.expand_more,
            size: 18,
            color: Theme.of(context).colorScheme.primary,
          ),
        ],
      ),
    ),
  );
}
