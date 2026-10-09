import '../widgets/untitled_poem_marker.dart';
import '../widgets/shelf_assets.dart';
import '../accounts/account_controller.dart';
import '../accounts/account_screen.dart';
import '../purchases/library_controller.dart';
import '../purchases/library_screen.dart';
import '../purchases/purchase_screen.dart';

import 'package:flutter/material.dart';

import '../audio/audio_playback_controller.dart';
import '../models/poem.dart';
import '../l10n/app_strings.dart';
import '../widgets/bookstore_widgets.dart';
import 'bookstore_navigation.dart';
import '../purchases/entitlement_controller.dart';
import '../repository/poetry_repository.dart';
import '../settings/reader_settings.dart';
import '../settings/reading_preferences_sheet.dart';
import 'poem_reader_screen.dart';

class CollectionDetailScreen extends StatefulWidget {
  const CollectionDetailScreen({
    required this.slug,
    required this.contentVersion,
    required this.repository,
    required this.readerSettings,
    this.audioController,
    this.entitlements,
    this.ownerPreviewMode = false,
    this.ownedMode = false,
    super.key,
  });

  final String slug;
  final int contentVersion;
  final PoetryDataSource repository;
  final ReaderSettings readerSettings;
  final AudioPlaybackController? audioController;
  final EntitlementController? entitlements;
  final bool ownerPreviewMode;
  final bool ownedMode;

  @override
  State<CollectionDetailScreen> createState() => _CollectionDetailScreenState();
}

class _CollectionDetailScreenState extends State<CollectionDetailScreen> {
  late Future<CollectionBundle> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<CollectionBundle> _load() =>
      widget.repository.loadCollection(widget.slug, widget.contentVersion);

  void _retry() => setState(() => _future = _load());

  @override
  Widget build(BuildContext context) => AnimatedBuilder(
    animation: widget.readerSettings,
    builder: (context, _) => Scaffold(
      appBar: AppBar(
        toolbarHeight:
            56 * MediaQuery.textScalerOf(context).scale(1).clamp(1, 2),
        title: Text(
          AppStrings.of(context)
              .choose('Book details', 'د کتاب په اړه', 'دربارهٔ کتاب'),
          maxLines: 2,
        ),
        actions: [
          ShelfFontButton(
            key: const Key('collection-font-chooser'),
            onPressed: () =>
                showReadingPreferences(context, widget.readerSettings),
          ),
        ],
      ),
      body: FutureBuilder<CollectionBundle>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState != ConnectionState.done) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snapshot.hasError || !snapshot.hasData) {
            return _CollectionError(onRetry: _retry);
          }
          final bundle = snapshot.requireData;
          final book = bundle.collection;
          final navigation = BookstoreNavigation(
            repository: widget.repository,
            settings: widget.readerSettings,
            contentVersion: widget.contentVersion,
            audioController: widget.audioController,
            entitlements: widget.entitlements,
            ownerPreview: widget.ownerPreviewMode,
          );
          final samples = bundle.poems.where(
            (p) => p.isFreeSample && !p.locked,
          );
          void openItem(PoemSummary poem, {bool resume = false}) =>
              Navigator.of(context).push(
                MaterialPageRoute<void>(
                  builder: (_) => Directionality(
                    textDirection: TextDirection.rtl,
                    child: PoemReaderScreen(
                      poemId: poem.id,
                      contentVersion: widget.contentVersion,
                      repository: widget.repository,
                      settings: widget.readerSettings,
                      collectionTitle: book.title,
                      collection: book,
                      contents: bundle.poems,
                      ownedMode: widget.ownedMode,
                      resume: resume,
                      audioController:
                          widget.audioController ?? InactiveAudioController(),
                      entitlements: widget.entitlements,
                      ownerPreviewMode: widget.ownerPreviewMode,
                    ),
                  ),
                ),
              );

          return Scaffold(
            bottomNavigationBar: SafeArea(
              top: false,
              child: Container(
                color: Theme.of(context).colorScheme.surface,
                padding: const EdgeInsets.fromLTRB(20, 12, 20, 16),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    FilledButton.icon(
                      key: const Key('read-sample'),
                      onPressed:
                          (widget.ownedMode
                              ? bundle.poems.isEmpty
                              : samples.isEmpty)
                          ? null
                          : () => openItem(
                              widget.ownedMode
                                  ? bundle.poems.first
                                  : samples.first,
                              resume: true,
                            ),
                      icon: const ShelfActionIcon('read-sample'),
                      label: Text(
                        widget.ownedMode
                            ? (AppStrings.of(context).choose(
                                'Read book',
                                'کتاب ولولئ',
                                'خواندن کتاب',
                              ))
                            : AppStrings.of(context).readSample,
                      ),
                    ),
                    const SizedBox(height: 8),
                    if (!widget.ownedMode) BookPriceLabel(book: book),
                    if (!widget.ownedMode &&
                        LibraryScope.of(context)?.refreshFailed == true)
                      Text(
                        AppStrings.of(context).isEnglish
                            ? LibraryScope.of(context)!.message!
                            : 'کتابتون تازه نه شو. انټرنېټ ته وصل شئ او بیا هڅه وکړئ. کتاب بیا مه پېرئ.',
                        textDirection: AppStrings.of(context).isEnglish
                            ? TextDirection.ltr
                            : TextDirection.rtl,
                      ),
                    if (!widget.ownedMode)
                      OutlinedButton(
                        key: Key('buy-coming-soon'),
                        onPressed: () {
                          final library = LibraryScope.of(context);
                          if (library != null) {
                            if (library.owns(book.id)) {
                              openOwnedBook(
                                context,
                                library,
                                book,
                                widget.readerSettings,
                              );
                            } else {
                              Navigator.of(context).push(
                                MaterialPageRoute<void>(
                                  builder: (_) => PurchaseScreen(
                                    book: book,
                                    settings: widget.readerSettings,
                                  ),
                                ),
                              );
                            }
                            return;
                          }
                          if (AccountScope.of(context)?.user == null) {
                            openAccount(context);
                          } else {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(AppStrings.of(context).buySoon),
                              ),
                            );
                          }
                        },
                        child: Text(
                          LibraryScope.of(context) == null
                              ? AppStrings.of(context).buySoon
                              : LibraryScope.of(context)!.owns(book.id)
                              ? (AppStrings.of(context).choose(
                                  'Open owned book',
                                  'پېرودل شوی کتاب پرانیزئ',
                                  'باز کردن کتاب خریداری‌شده',
                                ))
                              : LibraryScope.of(context)!.checkoutAvailable !=
                                    true
                              ? (AppStrings.of(context).choose(
                                  'Purchases unavailable',
                                  'پېرودنه اوس نشته',
                                  'خرید در دسترس نیست',
                                ))
                              : (AppStrings.of(context).choose(
                                  'Buy book',
                                  'کتاب وپېرئ',
                                  'خرید کتاب',
                                )),
                        ),
                      ),
                  ],
                ),
              ),
            ),
            body: CustomScrollView(
              slivers: [
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(20, 8, 20, 28),
                  sliver: SliverList.list(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          vertical: 12,
                          horizontal: 16,
                        ),
                        decoration: BoxDecoration(
                          color: Theme.of(context).colorScheme.primaryContainer,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Center(
                          child: BookCover(
                            url: book.coverUrl,
                            width: 140,
                            height: 207,
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        bundle.collection.title,
                        key: const Key('collection-detail-title'),
                        textDirection: TextDirection.rtl,
                        textAlign: TextAlign.right,
                        style: Theme.of(context).textTheme.titleLarge,
                      ),
                      if (book.subtitle?.isNotEmpty == true)
                        Text(
                          book.subtitle!,
                          textDirection: TextDirection.rtl,
                          textAlign: TextAlign.right,
                        ),
                      if (book.authors.any((a) => a.role == 'author'))
                        Wrap(
                          alignment: WrapAlignment.start,
                          textDirection: TextDirection.rtl,
                          children: [
                            for (final author in book.authors.where(
                              (a) => a.role == 'author',
                            ))
                              TextButton(
                                onPressed: () =>
                                    navigation.openAuthor(context, author.slug),
                                child: Text(
                                  author.name,
                                  textDirection: TextDirection.rtl,
                                  textAlign: TextAlign.right,
                                ),
                              ),
                          ],
                        )
                      else if (book.creditedAuthors case final author?)
                        Text(
                          author,
                          textDirection: TextDirection.rtl,
                          textAlign: TextAlign.right,
                        ),
                      if (bundle.collection.creditedTranslators != null) ...[
                        const SizedBox(height: 8),
                        Text(
                          AppStrings.of(context).translation(
                            book.authors
                                .where((a) => a.role == 'translator')
                                .map((a) => a.name)
                                .join('، '),
                          ),
                          textDirection: TextDirection.rtl,
                          textAlign: TextAlign.right,
                        ),
                      ],
                      if (widget.ownerPreviewMode) ...[
                        const SizedBox(height: 10),
                        Center(
                          child: Chip(
                            key: const Key(
                              'owner-preview-collection-detail-status',
                            ),
                            label: Text(
                              AppStrings.of(context).status(book.displayStatus),
                            ),
                          ),
                        ),
                      ],
                      Wrap(
                        alignment: WrapAlignment.start,
                        textDirection: TextDirection.rtl,
                        spacing: 8,
                        children: [
                          if (book.language case final language?)
                            Chip(
                              label: Text(
                                AppStrings.of(context).languageName(language),
                              ),
                            ),
                          Chip(
                            label: Text(
                              AppStrings.of(context).typeName(book.bookType),
                            ),
                          ),
                          for (final category in book.categories)
                            Chip(label: Text(category.name)),
                        ],
                      ),
                      const SizedBox(height: 16),
                      if (book.description?.isNotEmpty == true)
                        _FrontMatter(
                          title: AppStrings.of(context).description,
                          body: book.description!,
                        ),
                      if (bundle.collection.publicationInfo case final info?)
                        _FrontMatter(
                          title: AppStrings.of(context).publicationInfo,
                          body: info,
                        ),
                      if (bundle.collection.dedication case final dedication?)
                        _FrontMatter(
                          title: AppStrings.of(context).dedication,
                          body: dedication,
                        ),
                      if (bundle.collection.introduction
                          case final introduction?)
                        _FrontMatter(
                          title: AppStrings.of(context).introduction,
                          body: introduction,
                        ),
                      if (bundle.collection.foreword case final foreword?)
                        _FrontMatter(
                          title:
                              bundle.collection.forewordAuthor ??
                              AppStrings.of(context).foreword,
                          body: foreword,
                        ),
                      const SizedBox(height: 28),
                      Text(
                        AppStrings.of(context).contents,
                        style: Theme.of(context).textTheme.headlineMedium,
                      ),
                      const SizedBox(height: 10),
                      if (bundle.poems.isEmpty)
                        Padding(
                          padding: EdgeInsets.symmetric(vertical: 30),
                          child: Text(AppStrings.of(context).noContent),
                        )
                      else
                        ...bundle.poems.map(
                          (poem) => _PoemTile(
                            poem: poem,
                            fontFamily: widget.readerSettings.fontFamily,
                            ownerPreviewMode: widget.ownerPreviewMode,
                            onTap:
                                widget.ownedMode ||
                                    widget.ownerPreviewMode ||
                                    (poem.isFreeSample && !poem.locked)
                                ? () => openItem(poem)
                                : null,
                          ),
                        ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    ),
  );
}

class _FrontMatter extends StatelessWidget {
  const _FrontMatter({required this.title, required this.body});
  final String title;
  final String body;

  @override
  Widget build(BuildContext context) => ExpansionTile(
    tilePadding: EdgeInsets.zero,
    expandedCrossAxisAlignment: CrossAxisAlignment.stretch,
    title: Text(title),
    children: [
      Padding(
        padding: const EdgeInsets.only(bottom: 18),
        child: SelectableText(
          body,
          textDirection: TextDirection.rtl,
          textAlign: TextAlign.right,
          style: const TextStyle(height: 1.8),
        ),
      ),
    ],
  );
}

class _PoemTile extends StatelessWidget {
  const _PoemTile({
    required this.poem,
    required this.onTap,
    required this.ownerPreviewMode,
    required this.fontFamily,
  });
  final PoemSummary poem;
  final VoidCallback? onTap;
  final bool ownerPreviewMode;
  final String fontFamily;

  @override
  Widget build(BuildContext context) => Semantics(
    button: onTap != null,
    explicitChildNodes: true,
    label: poem.isUntitled ? null : poem.title,
    child: ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 4, vertical: 7),
      onTap: onTap,
      leading: CircleAvatar(
        child: Text(AppStrings.of(context).number(poem.sortOrder)),
      ),
      title: poem.isUntitled
          ? Align(
              alignment: Alignment.centerRight,
              child: UntitledPoemMarker(
                key: Key('poem-list-title-${poem.id}'),
                color: Theme.of(context).colorScheme.onSurface,
              ),
            )
          : Text(
              poem.title!,
              key: Key('poem-list-title-${poem.id}'),
              textDirection: TextDirection.rtl,
              textAlign: TextAlign.right,
              style: TextStyle(fontFamily: fontFamily),
            ),
      subtitle:
          [
            if (!ownerPreviewMode && poem.isFreeSample && !poem.locked)
              AppStrings.of(context).sample,
            if (poem.isTranslation)
              '${AppStrings.of(context).originalAuthor(poem.originalAuthor ?? '—')}\n${AppStrings.of(context).translation(poem.translator ?? '—')}',
            if (ownerPreviewMode)
              '${poem.isActive ? AppStrings.of(context).published : AppStrings.of(context).draft} • ${poem.isFreeSample ? AppStrings.of(context).free : AppStrings.of(context).locked}',
          ].isEmpty
          ? null
          : Text(
              [
                if (!ownerPreviewMode && poem.isFreeSample && !poem.locked)
                  AppStrings.of(context).sample,
                if (poem.isTranslation)
                  '${AppStrings.of(context).originalAuthor(poem.originalAuthor ?? '—')}\n${AppStrings.of(context).translation(poem.translator ?? '—')}',
                if (ownerPreviewMode)
                  '${poem.isActive ? AppStrings.of(context).published : AppStrings.of(context).draft} • ${poem.isFreeSample ? AppStrings.of(context).free : AppStrings.of(context).locked}',
              ].join('\n'),
              key: ownerPreviewMode
                  ? const Key('owner-preview-poem-status')
                  : null,
            ),
      trailing: Semantics(
        label: (ownerPreviewMode ? !poem.isFreeSample : poem.locked)
            ? AppStrings.of(context).locked
            : AppStrings.of(context).free,
        child: ShelfActionIcon(
          (ownerPreviewMode ? !poem.isFreeSample : poem.locked)
              ? 'locked'
              : 'read-sample',
        ),
      ),
    ),
  );
}

class _CollectionError extends StatelessWidget {
  const _CollectionError({required this.onRetry});
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) => Center(
    child: Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(AppStrings.of(context).error),
        const SizedBox(height: 12),
        FilledButton(
          onPressed: onRetry,
          child: Text(AppStrings.of(context).retry),
        ),
      ],
    ),
  );
}
