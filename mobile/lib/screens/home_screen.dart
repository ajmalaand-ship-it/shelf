import '../widgets/shelf_assets.dart';
import '../settings/privacy_support.dart';
import '../accounts/account_controller.dart';
import '../accounts/account_screen.dart';
import '../purchases/library_controller.dart';
import '../purchases/library_screen.dart';

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
import '../widgets/language_controls.dart';
import '../settings/interface_language.dart';
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
  String? _storeType;
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
        title: _tab == 0
            ? const ShelfLogo()
            : Text(
                [
                  AppStrings.of(context).appName,
                  AppStrings.of(context).search,
                  AppStrings.of(context).library,
                  AppStrings.of(context).settings,
                ][_tab],
              ),
        actions: [
          IconButton(
            key: const Key('store-account'),
            tooltip: AccountScope.of(context)?.user == null
                ? '${AppStrings.of(context).signIn} / ${AppStrings.of(context).createAccount}'
                : AppStrings.of(context).account,
            onPressed: () => openAccount(context),
            icon: const ShelfActionIcon('account'),
          ),
          ShelfFontButton(
            key: const Key('home-font-chooser'),
            onPressed: () =>
                showReadingPreferences(context, widget.readerSettings),
          ),
        ],
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _tab,
        onDestinationSelected: (index) => setState(() => _tab = index),
        destinations: [
          NavigationDestination(
            icon: ShelfActionIcon('store', inactive: true),
            selectedIcon: ShelfActionIcon('store'),
            label: AppStrings.of(context).store,
          ),
          NavigationDestination(
            icon: ShelfActionIcon('search', inactive: true),
            selectedIcon: ShelfActionIcon('search'),
            label: AppStrings.of(context).search,
          ),
          NavigationDestination(
            icon: ShelfActionIcon('library', inactive: true),
            selectedIcon: ShelfActionIcon('library'),
            label: AppStrings.of(context).library,
          ),
          NavigationDestination(
            icon: ShelfActionIcon('settings', inactive: true),
            selectedIcon: ShelfActionIcon('settings'),
            label: AppStrings.of(context).settings,
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
            LibraryScope.of(context) == null
                ? const LibraryScreen()
                : PurchasedLibraryScreen(settings: widget.readerSettings),
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
    final books = snapshot.collections
        .where((book) => _storeType == null || book.bookType == _storeType)
        .toList();
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
          const Align(
            alignment: AlignmentDirectional.centerEnd,
            child: LanguageButton(key: Key('store-language-toggle')),
          ),
          Text(
            snapshot.config.slogan,
            textAlign: TextAlign.start,
            style: Theme.of(context).textTheme.titleLarge,
          ),
          const SizedBox(height: 16),
          OutlinedButton.icon(
            key: const Key('store-search-entry'),
            style: OutlinedButton.styleFrom(
              backgroundColor: Colors.white,
              alignment: AlignmentDirectional.centerStart,
            ),
            onPressed: () => setState(() => _tab = 1),
            icon: const ShelfActionIcon('search'),
            label: Text(AppStrings.of(context).searchHint),
          ),
          const SizedBox(height: 16),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              for (final type in [null, 'poetry', 'prose'])
                ChoiceChip(
                  selected: _storeType == type,
                  label: Text(
                    type == null
                        ? AppStrings.of(context).all
                        : AppStrings.of(context).typeName(type),
                  ),
                  labelStyle: TextStyle(
                    fontFamily: 'Vazirmatn',
                    color: _storeType == type
                        ? Colors.white
                        : Theme.of(context).colorScheme.primary,
                  ),
                  onSelected: (_) => setState(() => _storeType = type),
                ),
            ],
          ),
          if (widget.qaMode)
            Padding(
              key: Key('debug-qa-notice'),
              padding: EdgeInsets.all(12),
              child: Text(AppStrings.of(context).qaNotice),
            ),
          if (snapshot.refreshError != null)
            Text(AppStrings.of(context).offline),
          if (books.isEmpty)
            Padding(
              padding: EdgeInsets.symmetric(vertical: 40),
              child: Text(AppStrings.of(context).noBooks),
            )
          else if (populated.isEmpty) ...[
            SectionTitle(AppStrings.of(context).allBooks),
            BookList(
              books: books,
              fontFamily: widget.readerSettings.fontFamily,
              ownerPreview: widget.ownerPreviewMode,
              onTap: (b) => _navigation.openBook(context, b.slug),
            ),
          ] else ...[
            BookRow(
              title: AppStrings.of(context).newBooks,
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
            SectionTitle(AppStrings.of(context).authors),
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
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Center(child: AuthorPortrait(author: author)),
                          const SizedBox(height: 8),
                          Text(
                            author.name,
                            style: const TextStyle(fontWeight: FontWeight.bold),
                            maxLines: 2,
                            textDirection: TextDirection.rtl,
                            textAlign: TextAlign.right,
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
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: EdgeInsets.all(28),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          ShelfActionIcon('library', size: 56),
          SizedBox(height: 20),
          Text(
            AccountScope.of(context)?.user == null
                ? AppStrings.of(context).signIn
                : AppStrings.of(context).booksWillAppear,
            textAlign: TextAlign.center,
          ),
          SizedBox(height: 12),
          Text(
            AppStrings.of(context).libraryAccountMessage,
            textAlign: TextAlign.center,
          ),
          FilledButton(
            key: const Key('library-account'),
            onPressed: () => openAccount(context),
            style: FilledButton.styleFrom(
              minimumSize: const Size(double.infinity, 52),
            ),
            child: Text(
              AccountScope.of(context)?.user == null
                  ? '${AppStrings.of(context).signIn} / ${AppStrings.of(context).createAccount}'
                  : AppStrings.of(context).account,
            ),
          ),
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
        key: const Key('settings-account'),
        leading: const ShelfActionIcon('account'),
        title: Text(AppStrings.of(context).account),
        subtitle: Text(
          AccountScope.of(context)?.user?.email ??
              AppStrings.of(context).signIn,
        ),
        onTap: () => openAccount(context),
      ),
      ListTile(
        title: Text(AppStrings.of(context).interfaceLanguage),
        subtitle: Text(
          (InterfaceLanguageScope.of(context)?.language ?? InterfaceLanguage.ps)
              .label,
        ),
        trailing: const LanguageButton(key: Key('settings-language-toggle')),
      ),
      const PrivacySupportEntries(),
      ListTile(
        leading: const ShelfActionIcon('read-sample'),
        title: Text(AppStrings.of(context).readingPreferences),
        trailing: const ShelfActionIcon('back', directional: true),
        onTap: () => showReadingPreferences(context, settings),
      ),
    ],
  );
}
