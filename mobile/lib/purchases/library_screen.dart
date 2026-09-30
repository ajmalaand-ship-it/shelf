import 'dart:async';

import 'package:flutter/material.dart';

import '../accounts/account_screen.dart';
import '../audio/just_audio_controller.dart';
import '../l10n/app_strings.dart';
import '../models/poetry_collection.dart';
import '../screens/collection_detail_screen.dart';
import '../settings/reader_settings.dart';
import 'library_controller.dart';
import 'owned_book_repository.dart';

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
    return ListView(
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
                child: FilledButton.icon(
                  onPressed: busy
                      ? null
                      : () => _perform(
                          () => c.refresh(),
                          _t('Library checked.', 'کتابتون وکتل شو.'),
                        ),
                  icon: const Icon(Icons.refresh),
                  label: Text(_t('Check Library', 'کتابتون وګورئ')),
                ),
              ),
              IconButton(
                onPressed: () => openAccount(context),
                icon: const Icon(Icons.person_outline),
                tooltip: AppStrings.of(context).account,
              ),
            ],
          ),
          OutlinedButton(
            onPressed: busy
                ? null
                : () => _perform(
                    c.restore,
                    _t(
                      'Purchases checked for this account.',
                      'د دې حساب پېرودنې وکتل شوې.',
                    ),
                  ),
            child: Text(_t('Restore purchases', 'پېرودنې بېرته راوړئ')),
          ),
          if (busy || !c.initialized) const LinearProgressIndicator(),
          if (message != null) Text(message!),
          if (c.message != null)
            Text(
              _t(
                c.message!,
                'د کتابونو د مالکیت کتلو لپاره انټرنېټ ته وصل شئ. لغوه شوي کتابونه او کاپۍ لرې کېږي.',
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
          for (final book in c.books)
            Card(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Text(
                      book.title,
                      textDirection: TextDirection.rtl,
                      textAlign: TextAlign.right,
                      style: Theme.of(context).textTheme.titleLarge,
                    ),
                    Text(
                      c.downloaded.contains(book.id)
                          ? _t('Downloaded · owned', 'کښته شوی · پېرودل شوی')
                          : _t('Owned', 'پېرودل شوی'),
                    ),
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
                    OutlinedButton(
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
                      child: Text(
                        _t(
                          'Download for offline reading',
                          'له انټرنېټ پرته لوستلو لپاره کښته کړئ',
                        ),
                      ),
                    ),
                    if (c.downloaded.contains(book.id))
                      TextButton(
                        onPressed: busy
                            ? null
                            : () => _perform(
                                () => c.removeDownload(book.id!),
                                _t(
                                  'Download removed. You still own the book.',
                                  'کاپي لرې شوه. کتاب لا هم ستاسو دی.',
                                ),
                              ),
                        child: Text(
                          _t('Remove download', 'کښته شوې کاپي لرې کړئ'),
                        ),
                      ),
                  ],
                ),
              ),
            ),
        ],
      ],
    );
  }
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
