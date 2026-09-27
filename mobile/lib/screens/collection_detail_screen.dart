import 'package:flutter/material.dart';

import '../audio/audio_playback_controller.dart';
import '../models/poem.dart';
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
    super.key,
  });

  final String slug;
  final int contentVersion;
  final PoetryDataSource repository;
  final ReaderSettings readerSettings;
  final AudioPlaybackController? audioController;
  final EntitlementController? entitlements;
  final bool ownerPreviewMode;

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
        actions: [
          TextButton.icon(
            key: const Key('collection-font-chooser'),
            onPressed: () =>
                showReadingPreferences(context, widget.readerSettings),
            icon: const Icon(Icons.text_fields_rounded),
            label: const Text('لیکبڼه'),
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
          return CustomScrollView(
            slivers: [
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 8, 20, 28),
                sliver: SliverList.list(
                  children: [
                    Text(
                      bundle.collection.title,
                      key: const Key('collection-detail-title'),
                      textAlign: TextAlign.center,
                      style: Theme.of(context).textTheme.headlineLarge
                          ?.copyWith(
                            fontFamily: widget.readerSettings.fontFamily,
                          ),
                    ),
                    if (bundle.collection.creditedAuthors
                        case final author?) ...[
                      const SizedBox(height: 12),
                      Text(author, textAlign: TextAlign.center),
                    ],
                    if (bundle.collection.creditedTranslators
                        case final translators?) ...[
                      const SizedBox(height: 8),
                      Text(translators, textAlign: TextAlign.center),
                    ],
                    if (widget.ownerPreviewMode) ...[
                      const SizedBox(height: 10),
                      Center(
                        child: Chip(
                          key: const Key(
                            'owner-preview-collection-detail-status',
                          ),
                          label: Text(
                            bundle.collection.isActive ? 'خپره' : 'مسوده',
                          ),
                        ),
                      ),
                    ],
                    if (bundle.collection.publicationInfo case final info?)
                      _FrontMatter(title: 'د چاپ معلومات', body: info),
                    if (bundle.collection.dedication case final dedication?)
                      _FrontMatter(title: 'ډالۍ', body: dedication),
                    if (bundle.collection.introduction case final introduction?)
                      _FrontMatter(title: 'سريزه', body: introduction),
                    if (bundle.collection.foreword case final foreword?)
                      _FrontMatter(
                        title: bundle.collection.forewordAuthor ?? 'مخکنۍ خبرې',
                        body: foreword,
                      ),
                    const SizedBox(height: 28),
                    Text(
                      'شعرونه',
                      style: Theme.of(context).textTheme.headlineMedium,
                    ),
                    const SizedBox(height: 10),
                    if (bundle.poems.isEmpty)
                      const Padding(
                        padding: EdgeInsets.symmetric(vertical: 30),
                        child: Text('خپاره شوي شعرونه نشته.'),
                      )
                    else
                      ...bundle.poems.map(
                        (poem) => _PoemTile(
                          poem: poem,
                          fontFamily: widget.readerSettings.fontFamily,
                          ownerPreviewMode: widget.ownerPreviewMode,
                          onTap: () => Navigator.of(context).push(
                            MaterialPageRoute<void>(
                              builder: (_) => PoemReaderScreen(
                                poemId: poem.id,
                                contentVersion: widget.contentVersion,
                                repository: widget.repository,
                                settings: widget.readerSettings,
                                collectionTitle: bundle.collection.title,
                                audioController:
                                    widget.audioController ??
                                    InactiveAudioController(),
                                entitlements: widget.entitlements,
                                ownerPreviewMode: widget.ownerPreviewMode,
                              ),
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
              ),
            ],
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
    title: Text(title),
    children: [
      Padding(
        padding: const EdgeInsets.only(bottom: 18),
        child: SelectableText(
          body,
          textDirection: TextDirection.rtl,
          textAlign: TextAlign.start,
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
  final VoidCallback onTap;
  final bool ownerPreviewMode;
  final String fontFamily;

  @override
  Widget build(BuildContext context) => Semantics(
    button: true,
    label: poem.isUntitled
        ? 'بې نومه شعر: ${poem.displayTitle}'
        : poem.displayTitle,
    child: ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 4, vertical: 7),
      onTap: onTap,
      leading: CircleAvatar(child: Text('${poem.sortOrder}')),
      title: Text(
        poem.displayTitle,
        key: Key('poem-list-title-${poem.id}'),
        style: TextStyle(fontFamily: fontFamily),
      ),
      subtitle:
          [
            if (poem.isTranslation)
              'اصلي شاعر: ${poem.originalAuthor ?? '—'}\nپښتو ژباړه: ${poem.translator ?? '—'}',
            if (ownerPreviewMode)
              '${poem.isActive ? 'خپور' : 'مسوده'} • ${poem.isFreeSample ? 'وړيا' : 'تړلی'}'
                  '${poem.isTranslation ? ' • ژباړه' : ''}',
          ].isEmpty
          ? null
          : Text(
              [
                if (poem.isTranslation)
                  'اصلي شاعر: ${poem.originalAuthor ?? '—'}\nپښتو ژباړه: ${poem.translator ?? '—'}',
                if (ownerPreviewMode)
                  '${poem.isActive ? 'خپور' : 'مسوده'} • ${poem.isFreeSample ? 'وړيا' : 'تړلی'}'
                      '${poem.isTranslation ? ' • ژباړه' : ''}',
              ].join('\n'),
              key: ownerPreviewMode
                  ? const Key('owner-preview-poem-status')
                  : null,
            ),
      trailing: Icon(
        (ownerPreviewMode ? !poem.isFreeSample : poem.locked)
            ? Icons.lock_outline_rounded
            : Icons.menu_book_rounded,
        semanticLabel: (ownerPreviewMode ? !poem.isFreeSample : poem.locked)
            ? 'تړلی'
            : 'وړيا',
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
        const Text('ټولګه ترلاسه نه شوه.'),
        const SizedBox(height: 12),
        FilledButton(onPressed: onRetry, child: const Text('بيا هڅه وکړئ')),
      ],
    ),
  );
}
