import 'dart:async';
import 'dart:io';
import 'dart:typed_data';

import 'package:flutter/material.dart';

import '../accounts/account_screen.dart';
import '../audio/just_audio_controller.dart';
import '../l10n/app_strings.dart';
import '../models/poetry_collection.dart';
import '../screens/collection_detail_screen.dart';
import '../settings/reader_settings.dart';
import 'library_controller.dart';
import 'owned_book_repository.dart';
import '../widgets/bookstore_widgets.dart';

Future<void> openOwnedBook(
  BuildContext context,
  LibraryController library,
  PoetryCollection book,
  ReaderSettings settings,
) => Navigator.of(context).push(
  MaterialPageRoute<void>(
    builder: (_) =>
        _OwnedSession(library: library, book: book, settings: settings),
  ),
);

class PurchasedLibraryScreen extends StatefulWidget {
  const PurchasedLibraryScreen({required this.settings, super.key});
  final ReaderSettings settings;
  @override
  State<PurchasedLibraryScreen> createState() => _PurchasedLibraryScreenState();
}

class _PurchasedLibraryScreenState extends State<PurchasedLibraryScreen> {
  String? message;
  bool busy = false;
  bool downloadedOnly = false;
  Future<void> _perform(Future<void> Function() action, String success) async {
    if (busy) return;
    setState(() {
      busy = true;
      message = null;
    });
    try {
      await action();
      if (mounted) setState(() => message = success);
    } catch (_) {
      if (mounted)
        setState(
          () => message = _t(
            'Could not finish. Go online and try again.',
            'کار بشپړ نه شو. انټرنېټ ته وصل شئ او بیا هڅه وکړئ.',
          ),
        );
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  String _t(String en, String ps) => AppStrings.of(context).isEnglish ? en : ps;
  @override
  Widget build(BuildContext context) {
    final c = LibraryScope.of(context)!;
    return Directionality(
      textDirection: AppStrings.of(context).isEnglish
          ? TextDirection.ltr
          : TextDirection.rtl,
      child: RefreshIndicator(
        onRefresh: () =>
            _perform(c.refresh, _t('Library refreshed.', 'کتابتون تازه شو.')),
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20),
          children: [
            if (c.readerId == null) ...[
              const Icon(Icons.local_library_outlined, size: 56),
              Text(
                AppStrings.of(context).libraryAccountMessage,
                textAlign: TextAlign.center,
              ),
              FilledButton(
                key: const Key('library-account'),
                style: FilledButton.styleFrom(
                  minimumSize: const Size.fromHeight(52),
                ),
                onPressed: () => openAccount(context),
                child: const Text('Sign in / Create account'),
              ),
            ] else ...[
              Row(
                children: [
                  Expanded(
                    child: Align(
                      alignment: AlignmentDirectional.centerStart,
                      child: TextButton(
                        onPressed: busy
                            ? null
                            : () => _perform(
                                c.restore,
                                _t(
                                  'Purchases checked for this account.',
                                  'د دې حساب پېرودنې وکتل شوې.',
                                ),
                              ),
                        child: Text(
                          _t('Restore purchases', 'پېرودنې بېرته راوړئ'),
                        ),
                      ),
                    ),
                  ),
                  IconButton(
                    key: const Key('library-refresh'),
                    tooltip: _t('Refresh Library', 'کتابتون تازه کړئ'),
                    onPressed: busy
                        ? null
                        : () => _perform(
                            c.refresh,
                            _t('Library refreshed.', 'کتابتون تازه شو.'),
                          ),
                    icon: const Icon(Icons.refresh),
                  ),
                ],
              ),
              if (busy || !c.initialized) const LinearProgressIndicator(),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  ChoiceChip(
                    label: Text(AppStrings.of(context).all),
                    labelStyle: TextStyle(
                      fontFamily: 'Vazirmatn',
                      color: !downloadedOnly
                          ? Colors.white
                          : Theme.of(context).colorScheme.primary,
                    ),
                    selected: !downloadedOnly,
                    onSelected: (_) => setState(() => downloadedOnly = false),
                  ),
                  ChoiceChip(
                    label: Text(_t('Downloads', 'کښته شوي کتابونه')),
                    labelStyle: TextStyle(
                      fontFamily: 'Vazirmatn',
                      color: downloadedOnly
                          ? Colors.white
                          : Theme.of(context).colorScheme.primary,
                    ),
                    selected: downloadedOnly,
                    onSelected: (_) => setState(() => downloadedOnly = true),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              if (message != null && !c.refreshFailed) Text(message!),
              if (c.message != null)
                Text(
                  _t(
                    c.message!,
                    c.refreshFailed
                        ? 'کتابتون تازه نه شو. انټرنېټ ته وصل شئ او بیا هڅه وکړئ. کتاب بیا مه پېرئ.'
                        : 'د کتابونو د مالکیت کتلو لپاره انټرنېټ ته وصل شئ. لغوه شوي کتابونه او کاپۍ لرې کېږي.',
                  ),
                ),
              if (c.books.isEmpty && c.initialized)
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 28),
                  child: Text(
                    _t(
                      'Books you buy will appear here. Browse the Store and read a free sample first.',
                      'ستاسو پېرودل شوي کتابونه دلته ښکاري. لومړی په پلورنځي کې وړیا نمونه ولولئ.',
                    ),
                  ),
                ),
              if (downloadedOnly &&
                  c.books.isNotEmpty &&
                  !c.books.any((b) => c.downloaded.contains(b.id)))
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 24),
                  child: Text(
                    _t(
                      'No downloaded books. Download a book from All to read offline.',
                      'کښته شوي کتابونه نشته. له ټولو کتابونو څخه یو کتاب کښته کړئ.',
                    ),
                  ),
                ),
              for (final book in c.books.where(
                (b) => !downloadedOnly || c.downloaded.contains(b.id),
              ))
                Padding(
                  padding: const EdgeInsets.only(bottom: 16),
                  child: Card(
                    child: Padding(
                      padding: const EdgeInsets.all(12),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          BookPresentation(
                            book: book,
                            fontFamily: widget.settings.fontFamily,
                            cover: OwnedBookCover(
                              key: ValueKey(
                                '${c.readerId}/${book.id}/${c.downloaded.contains(book.id)}',
                              ),
                              book: book,
                              library: c,
                            ),
                          ),
                          const SizedBox(height: 12),
                          if (c.downloaded.contains(book.id))
                            Row(
                              key: const Key('library-downloaded'),
                              mainAxisAlignment: MainAxisAlignment.start,
                              children: [
                                Text(_t('Downloaded', 'کښته شوی')),
                                const SizedBox(width: 4),
                                const Icon(Icons.check, size: 18),
                              ],
                            )
                          else
                            Text(_t('Owned', 'پېرودل شوی')),
                          FilledButton(
                            onPressed: busy
                                ? null
                                : () => openOwnedBook(
                                    context,
                                    c,
                                    book,
                                    widget.settings,
                                  ),
                            child: Text(_t('Read book', 'کتاب ولولئ')),
                          ),
                          if (!c.downloaded.contains(book.id))
                            OutlinedButton.icon(
                              icon: const Icon(Icons.download_outlined),
                              onPressed: busy
                                  ? null
                                  : () => _perform(
                                      () async {
                                        await c.download(book);
                                      },
                                      _t(
                                        'Downloaded. Go online at least once every 30 days.',
                                        'کښته شو. لږ تر لږه هر ۳۰ ورځې یو ځل انټرنېټ ته وصل شئ.',
                                      ),
                                    ),
                              label: Text(
                                _t(
                                  'Download for offline reading',
                                  'له انټرنېټ پرته لوستلو لپاره کښته کړئ',
                                ),
                              ),
                            ),
                          if (c.downloaded.contains(book.id))
                            TextButton.icon(
                              icon: const Icon(Icons.delete_outline),
                              onPressed: busy
                                  ? null
                                  : () => _perform(
                                      () => c.removeDownload(book.id!),
                                      _t(
                                        'Download removed. You still own the book.',
                                        'کاپي لرې شوه. کتاب لا هم ستاسو دی.',
                                      ),
                                    ),
                              label: Text(
                                _t('Remove download', 'کښته شوې کاپي لرې کړئ'),
                              ),
                            ),
                        ],
                      ),
                    ),
                  ),
                ),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.primaryContainer,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  _t(
                    'Download your books to read offline. Go online at least once every 30 days.',
                    'خپل کتابونه له انټرنېټ پرته لوستلو لپاره کښته کړئ. لږ تر لږه هر ۳۰ ورځې یو ځل انټرنېټ ته وصل شئ.',
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

/// Load private covers through the same host-checked client as owned text.
class OwnedBookCover extends StatefulWidget {
  const OwnedBookCover({required this.book, required this.library, super.key});
  final PoetryCollection book;
  final LibraryController library;
  @override
  State<OwnedBookCover> createState() => _OwnedBookCoverState();
}

class _OwnedBookCoverState extends State<OwnedBookCover> {
  late Future<Uint8List?> bytes = _load();
  @override
  void didUpdateWidget(OwnedBookCover oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.book.coverUrl != widget.book.coverUrl ||
        oldWidget.library != widget.library) {
      bytes = _load();
    }
  }

  Future<Uint8List?> _load() async {
    final c = widget.library;
    final reader = c.readerId, token = c.accounts.readerToken;
    if (reader == null || token == null) return null;
    try {
      if (c.downloaded.contains(widget.book.id)) {
        final copy = await c.downloads.readBook(reader, widget.book.id!);
        final local = copy?['collection']?['cover_url'] as String?;
        if (local != null && local.startsWith('file:')) {
          final data = await File.fromUri(Uri.parse(local)).readAsBytes();
          return c.readerId == reader && c.accounts.readerToken == token
              ? data
              : null;
        }
      }
      final url = widget.book.coverUrl;
      if (url == null || url.startsWith('qa-cover:')) return null;
      final data = await c.service.media(url, token);
      return c.readerId == reader && c.accounts.readerToken == token
          ? data
          : null;
    } catch (_) {
      return null;
    }
  }

  @override
  Widget build(BuildContext context) => FutureBuilder<Uint8List?>(
    future: bytes,
    builder: (context, snapshot) => snapshot.data == null
        ? const BookCover(width: 112, height: 156)
        : ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: Image.memory(
              snapshot.data!,
              width: 112,
              height: 156,
              fit: BoxFit.contain,
              errorBuilder: (_, _, _) =>
                  const BookCover(width: 112, height: 156),
            ),
          ),
  );
}

class _OwnedSession extends StatefulWidget {
  const _OwnedSession({
    required this.library,
    required this.book,
    required this.settings,
  });
  final LibraryController library;
  final PoetryCollection book;
  final ReaderSettings settings;
  @override
  State<_OwnedSession> createState() => _OwnedSessionState();
}

class _OwnedSessionState extends State<_OwnedSession> {
  late final OwnedBookRepository repository = OwnedBookRepository(
    widget.library,
    widget.book,
  );
  JustAudioController? audio;
  bool denied = false;
  Timer? leaseTimer;
  late final Future<void> ready = _prepare();
  @override
  void initState() {
    super.initState();
    widget.library.stopReading.add(_stop);
  }

  Future<void> _prepare() async {
    await widget.library.copy(widget.book);
    if (!mounted || denied) return;
    final player = widget.library.audioController;
    await player?.useRepository(repository);
    if (!mounted || denied) {
      await player?.restoreRepository(repository);
      return;
    }
    audio = player;
    final remaining = widget.library.validUntil!.difference(DateTime.now());
    leaseTimer = Timer(remaining.isNegative ? Duration.zero : remaining, _stop);
  }

  void _stop() {
    if (audio != null) unawaited(audio!.restoreRepository(repository));
    audio = null;
    if (!mounted) return;
    final route = ModalRoute.of(context);
    Navigator.of(context).popUntil((r) => r == route || r.isFirst);
    setState(() => denied = true);
  }

  @override
  void dispose() {
    widget.library.stopReading.remove(_stop);
    leaseTimer?.cancel();
    if (audio != null) unawaited(audio!.restoreRepository(repository));
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (denied)
      return Scaffold(
        appBar: AppBar(),
        body: const Center(
          child: Text('Go online and check My Library to read this book.'),
        ),
      );
    return FutureBuilder<void>(
      future: ready,
      builder: (context, snapshot) {
        if (snapshot.hasError)
          return Scaffold(
            appBar: AppBar(),
            body: const Center(
              child: Text(
                'Could not open this book. Go online and check My Library.',
              ),
            ),
          );
        if (snapshot.connectionState != ConnectionState.done)
          return Scaffold(
            appBar: AppBar(),
            body: const Center(child: CircularProgressIndicator()),
          );
        return CollectionDetailScreen(
          slug: widget.book.slug,
          contentVersion: 0,
          repository: repository,
          readerSettings: widget.settings,
          audioController: audio,
          ownedMode: true,
        );
      },
    );
  }
}
