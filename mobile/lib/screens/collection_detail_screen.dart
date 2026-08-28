import 'package:flutter/material.dart';

import '../models/poem.dart';
import '../repository/poetry_repository.dart';
import '../settings/reader_settings.dart';
import 'poem_reader_screen.dart';

class CollectionDetailScreen extends StatefulWidget {
  const CollectionDetailScreen({
    required this.slug,
    required this.contentVersion,
    required this.repository,
    required this.readerSettings,
    super.key,
  });

  final String slug;
  final int contentVersion;
  final PoetryRepository repository;
  final ReaderSettings readerSettings;

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
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(),
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
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.headlineLarge,
                  ),
                  if (bundle.collection.author case final author?) ...[
                    const SizedBox(height: 12),
                    Text(author, textAlign: TextAlign.center),
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
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute<void>(
                            builder: (_) => PoemReaderScreen(
                              poemId: poem.id,
                              initialTitle: poem.displayTitle,
                              contentVersion: widget.contentVersion,
                              repository: widget.repository,
                              settings: widget.readerSettings,
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
  const _PoemTile({required this.poem, required this.onTap});
  final PoemSummary poem;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => Semantics(
    button: true,
    label: poem.isUntitled
        ? 'بې سرليکه: ${poem.displayTitle}'
        : poem.displayTitle,
    child: ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 4, vertical: 7),
      onTap: onTap,
      leading: CircleAvatar(child: Text('${poem.sortOrder}')),
      title: Text(poem.displayTitle),
      subtitle: poem.isTranslation
          ? Text(
              'اصلي شاعر: ${poem.originalAuthor ?? '—'}\n'
              'پښتو ژباړه: ${poem.translator ?? '—'}',
            )
          : poem.isUntitled
          ? const Text('بې سرليکه')
          : null,
      trailing: Icon(
        poem.locked ? Icons.lock_outline_rounded : Icons.menu_book_rounded,
        semanticLabel: poem.locked ? 'تړلی' : 'وړيا',
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
