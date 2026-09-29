import 'package:flutter/material.dart';

import '../audio/audio_playback_controller.dart';
import '../l10n/app_strings.dart';
import '../models/book_author.dart';
import '../models/book_category.dart';
import '../models/poetry_collection.dart';
import '../purchases/entitlement_controller.dart';
import '../repository/poetry_repository.dart';
import '../settings/reader_settings.dart';
import '../settings/reading_preferences_sheet.dart';
import '../widgets/bookstore_widgets.dart';
import 'bookstore_navigation.dart';
import 'search_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({
    required this.repository,
    required this.readerSettings,
    required this.qaMode,
    required this.audioController,
    this.ownerPreviewMode = false,
    this.entitlements,
    super.key,
  });
  final PoetryDataSource repository;
  final ReaderSettings readerSettings;
  final bool qaMode;
  final AudioPlaybackController audioController;
  final bool ownerPreviewMode;
  final EntitlementController? entitlements;
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  CatalogueSnapshot? _snapshot;
  List<BookAuthor> _authors = [];
  List<BookCategory> _categories = [];
  bool _failed = false;
  int _tab = 0;
  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _failed = false;
    });
    try {
      // Always request a fresh catalogue, including refreshed signed cover URLs.
      final snapshot = await widget.repository.refreshCatalogue(null);
      final source = widget.repository;
      List<BookAuthor> authors;
      List<BookCategory> categories;
      if (source is BookstoreDataSource) {
        final extra = await Future.wait([
          (source as BookstoreDataSource).loadAuthors(),
          (source as BookstoreDataSource).loadCategories(),
        ]);
        authors = extra[0] as List<BookAuthor>;
        categories = extra[1] as List<BookCategory>;
      } else {
        final credits = {
          for (final b in snapshot.collections)
            for (final c in b.authors)
              if (c.role == 'author') c.slug: c,
        };
        authors = credits.values
            .map((c) => BookAuthor(id: c.id, slug: c.slug, name: c.name))
            .toList();
        categories = {
          for (final b in snapshot.collections)
            for (final c in b.categories) c.slug: c,
        }.values.toList();
      }
      if (mounted)
        setState(() {
          _snapshot = snapshot;
          _authors = authors;
          _categories = categories;
        });
    } catch (_) {
      // Public discovery never falls back to possibly withdrawn cached books.
      if (mounted)
        setState(() {
          _snapshot = null;
          _failed = true;
        });
    }
  }

  BookstoreNavigation get _navigation => BookstoreNavigation(
    repository: widget.repository,
    settings: widget.readerSettings,
    contentVersion: _snapshot?.config.contentVersion ?? 0,
    audioController: widget.audioController,
    entitlements: widget.entitlements,
    ownerPreview: widget.ownerPreviewMode,
  );

  @override
  Widget build(BuildContext context) => AnimatedBuilder(
    animation: widget.readerSettings,
    builder: (context, _) => Scaffold(
      appBar: AppBar(
        title: Text(
          [
            AppStrings.store,
            AppStrings.search,
            AppStrings.library,
            AppStrings.settings,
          ][_tab],
        ),
        actions: [
          TextButton.icon(
            key: const Key('home-font-chooser'),
            onPressed: () =>
                showReadingPreferences(context, widget.readerSettings),
            icon: const Icon(Icons.text_fields_rounded),
            label: const Text(AppStrings.font),
          ),
        ],
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _tab,
        onDestinationSelected: (index) => setState(() => _tab = index),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.storefront_outlined),
            label: AppStrings.store,
          ),
          NavigationDestination(
            icon: Icon(Icons.search),
            label: AppStrings.search,
          ),
          NavigationDestination(
            icon: Icon(Icons.local_library_outlined),
            label: AppStrings.library,
          ),
          NavigationDestination(
            icon: Icon(Icons.settings_outlined),
            label: AppStrings.settings,
          ),
        ],
      ),
      body: SafeArea(
        child: IndexedStack(
          index: _tab,
          children: [
            _store(),
            SearchScreen(
              books: _snapshot?.collections ?? [],
              categories: _categories,
              navigation: _navigation,
            ),
            const LibraryScreen(),
            SettingsScreen(settings: widget.readerSettings),
          ],
        ),
      ),
    ),
  );

  Widget _store() {
    final snapshot = _snapshot;
    if (snapshot == null)
      return _failed
          ? BookstoreError(onRetry: _load)
          : const Center(child: CircularProgressIndicator());
    final books = snapshot.collections;
    final populated = _categories
        .where(
          (c) => books.any((b) => b.categories.any((bc) => bc.slug == c.slug)),
        )
        .toList();
    final newest = List<PoetryCollection>.of(books)
      ..sort((a, b) {
        final date = (b.createdAt ?? DateTime(1970)).compareTo(
          a.createdAt ?? DateTime(1970),
        );
        return date != 0 ? date : (b.id ?? 0).compareTo(a.id ?? 0);
      });
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(20),
        children: [
          Text(
            AppStrings.appName,
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.headlineLarge,
          ),
          const SizedBox(height: 10),
          Text(
            snapshot.config.slogan,
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.titleLarge,
          ),
          if (widget.qaMode)
            const Padding(
              key: Key('debug-qa-notice'),
              padding: EdgeInsets.all(12),
              child: Text(AppStrings.qaNotice),
            ),
          if (snapshot.refreshError != null) const Text(AppStrings.offline),
          if (books.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 40),
              child: Text(AppStrings.noBooks),
            )
          else if (populated.isEmpty) ...[
            const SectionTitle(AppStrings.allBooks),
            BookGrid(
              books: books,
              ownerPreview: widget.ownerPreviewMode,
              fontFamily: widget.readerSettings.fontFamily,
              onTap: (b) => _navigation.openBook(context, b.slug),
            ),
          ] else ...[
            BookRow(
              title: AppStrings.newBooks,
              books: newest,
              ownerPreview: widget.ownerPreviewMode,
              fontFamily: widget.readerSettings.fontFamily,
              onTap: (b) => _navigation.openBook(context, b.slug),
            ),
            for (final category in populated)
              BookRow(
                title: category.name,
                books: books
                    .where(
                      (b) => b.categories.any((c) => c.slug == category.slug),
                    )
                    .toList(),
                ownerPreview: widget.ownerPreviewMode,
                fontFamily: widget.readerSettings.fontFamily,
                onTap: (b) => _navigation.openBook(context, b.slug),
              ),
          ],
          if (_authors.isNotEmpty) ...[
            const SectionTitle(AppStrings.authors),
            SizedBox(
              height:
                  132 * MediaQuery.textScalerOf(context).scale(1).clamp(1, 2),
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: _authors.length,
                separatorBuilder: (_, _) => const SizedBox(width: 12),
                itemBuilder: (context, index) {
                  final author = _authors[index];
                  return SizedBox(
                    width: 120,
                    child: InkWell(
                      onTap: () => _navigation.openAuthor(context, author.slug),
                      child: Column(
                        children: [
                          AuthorPortrait(author: author),
                          const SizedBox(height: 8),
                          Text(
                            author.name,
                            maxLines: 2,
                            textAlign: TextAlign.center,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class LibraryScreen extends StatelessWidget {
  const LibraryScreen({super.key});
  @override
  Widget build(BuildContext context) => const Center(
    child: Padding(
      padding: EdgeInsets.all(28),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.local_library_outlined, size: 56),
          SizedBox(height: 20),
          Text(AppStrings.librarySoon, textAlign: TextAlign.center),
          SizedBox(height: 12),
          Text(AppStrings.libraryMessage, textAlign: TextAlign.center),
        ],
      ),
    ),
  );
}

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({required this.settings, super.key});
  final ReaderSettings settings;
  @override
  Widget build(BuildContext context) => ListView(
    padding: const EdgeInsets.all(20),
    children: [
      ListTile(
        leading: const Icon(Icons.menu_book_outlined),
        title: const Text(AppStrings.readingPreferences),
        trailing: const Icon(Icons.chevron_left),
        onTap: () => showReadingPreferences(context, settings),
      ),
    ],
  );
}
