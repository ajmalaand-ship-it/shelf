import 'dart:async';

import 'package:flutter/widgets.dart';

import '../accounts/account_controller.dart';
import '../accounts/account_service.dart';
import '../audio/just_audio_controller.dart';
import '../models/poetry_collection.dart';
import 'book_purchase_provider.dart';
import 'download_store.dart';
import 'library_service.dart';
import '../services/api_config.dart';

class LibraryController extends ChangeNotifier with WidgetsBindingObserver {
  LibraryController({
    required this.accounts,
    required this.service,
    required this.provider,
    required this.downloads,
    this.audioController,
    DateTime Function()? clock,
  }) : clock = clock ?? DateTime.now {
    accounts.addListener(_identityChanged);
    WidgetsBinding.instance.addObserver(this);
    _timer = Timer.periodic(const Duration(minutes: 5), (_) {
      if (readerId != null && !busy)
        unawaited(refresh().catchError((Object _) {}));
    });
    _identityChanged();
  }
  final AccountController accounts;
  final LibraryService service;
  final BookPurchaseProvider provider;
  final BookDownloadStore downloads;
  final JustAudioController? audioController;
  final DateTime Function() clock;
  Timer? _timer;
  int? readerId;
  String? _identityToken;
  int _generation = 0;
  List<PoetryCollection> books = [];
  Set<int> downloaded = {};
  DateTime? validUntil, _lastObserved;
  bool busy = false, initialized = false, buyingBlocked = false;
  Map<String, dynamic> configuration = {};
  String? message;
  final Set<VoidCallback> stopReading = {};
  Future<void> _identityWork = Future.value();
  Future<void> _checks = Future.value();
  Future<void> _persistence = Future.value();

  Future<void> _persist(int reader, int generation) {
    final task = _persistence.catchError((Object _) {}).then((_) async {
      assertIdentity(reader, generation);
      await downloads.writeManifest(reader, _manifest());
      assertIdentity(reader, generation);
    });
    _persistence = task;
    return task;
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed && readerId != null)
      unawaited(refresh().catchError((Object _) {}));
  }

  void _identityChanged() {
    final next = accounts.user?.id;
    if (next == readerId && accounts.readerToken == _identityToken) return;
    final previous = readerId;
    readerId = next;
    _identityToken = accounts.readerToken;
    final generation = ++_generation;
    books = [];
    downloaded = {};
    validUntil = null;
    _lastObserved = null;
    initialized = false;
    busy = false;
    message = null;
    for (final stop in stopReading.toList()) {
      stop();
    }
    notifyListeners();
    _identityWork = _identityWork
        .catchError((Object _) {})
        .then((_) async {
          await provider.signOut();
          await _persistence.catchError((Object _) {});
          // Serialize identity transitions; a former user's late response never appears.
          if (previous != null) await downloads.clear(previous);
          if (generation != _generation || next == null) return;
          final manifest = await downloads.readManifest(next);
          if (generation != _generation) return;
          if (manifest != null) _applyManifest(manifest);
          initialized = true;
          notifyListeners();
          await refresh();
        })
        .catchError((Object _) {
          if (generation == _generation) {
            initialized = true;
            message = 'Go online to check your Library. Downloaded books stay available within their offline limit.';
            notifyListeners();
          }
        });
  }

  void _applyManifest(Map<String, dynamic> manifest) {
    books = (manifest['books'] as List? ?? [])
        .map((b) => PoetryCollection.fromJson(b as Map<String, dynamic>))
        .toList();
    downloaded = (manifest['downloaded'] as List? ?? []).cast<int>().toSet();
    validUntil = DateTime.tryParse(manifest['valid_until'] as String? ?? '');
    _lastObserved = DateTime.tryParse(
      manifest['last_observed'] as String? ?? '',
    );
  }

  Map<String, dynamic> _manifest() => {
    'books': books.map((b) => b.toJson()).toList(),
    'downloaded': downloaded.toList(),
    'valid_until': validUntil?.toIso8601String(),
    'last_observed': _lastObserved?.toIso8601String(),
  };
  bool owns(int? book) => book != null && books.any((b) => b.id == book);
  bool get offlineValid =>
      validUntil != null &&
      clock().isBefore(validUntil!) &&
      (_lastObserved == null || !clock().isBefore(_lastObserved!));
  void assertIdentity(int reader, int generation) {
    if (readerId != reader ||
        generation != _generation ||
        accounts.user?.id != reader)
      throw const AccountFailure(401);
  }

  Future<Map<String, dynamic>> request(
    String path, {
    String method = 'GET',
    Map<String, dynamic>? data,
  }) async {
    final reader = readerId,
        generation = _generation,
        token = accounts.readerToken;
    if (reader == null || token == null) throw const AccountFailure(401);
    final result = await service.request(
      path,
      method: method,
      token: token,
      data: data,
    );
    assertIdentity(reader, generation);
    return result;
  }

  Future<void> refresh({String operation = 'library'}) {
    final task = _checks
        .catchError((Object _) {})
        .then((_) => _refresh(operation: operation));
    _checks = task;
    return task;
  }

  Future<void> _refresh({String operation = 'library'}) async {
    if (readerId == null) return;
    final generation = _generation, reader = readerId!;
    try {
      final data = await request(
        operation,
        method: operation == 'library' ? 'GET' : 'POST',
      );
      final nextBooks = (data['books'] as List)
          .map((b) => PoetryCollection.fromJson(b as Map<String, dynamic>))
          .toList();
      final removed = downloaded
          .where((id) => !nextBooks.any((b) => b.id == id))
          .toList();
      // Stop current reading/audio before deleting revoked content.
      if (removed.isNotEmpty ||
          books.any((b) => !nextBooks.any((n) => n.id == b.id))) {
        for (final stop in stopReading.toList()) {
          stop();
        }
      }
      for (final book in removed) {
        await downloads.removeBook(reader, book);
      }
      assertIdentity(reader, generation);
      books = nextBooks;
      downloaded.removeAll(removed);
      final serverExpiry = DateTime.parse(
        data['offline_valid_until'] as String,
      );
      final maxExpiry = clock().add(const Duration(days: 30));
      validUntil = serverExpiry.isBefore(maxExpiry) ? serverExpiry : maxExpiry;
      _lastObserved = clock();
      buyingBlocked = data['buying_blocked'] == true;
      await _persist(reader, generation);
      assertIdentity(reader, generation);
      message = removed.isEmpty
          ? null
          : 'A refunded or revoked book and its download were removed.';
      initialized = true;
      notifyListeners();
    } on AccountFailure catch (error) {
      if (generation != _generation) rethrow;
      if (error.status == 401 || error.status == 403) {
        for (final stop in stopReading.toList()) {
          stop();
        }
        books = [];
        downloaded = {};
        validUntil = null;
        await downloads.clear(reader);
        notifyListeners();
      }
      rethrow;
    }
  }

  Future<void> configureStore() async {
    final reader = readerId, generation = _generation;
    if (reader == null) throw const AccountFailure(401);
    final config = await service.request('purchases/config');
    assertIdentity(reader, generation);
    configuration = config;
    if (configuration['enabled'] != true || configuration['test_mode'] != true)
      throw const AccountFailure(503);
    await provider.identify(
      configuration['public_sdk_key'] as String,
      shelfPurchaseIdentity(reader, configuration['identity_prefix'] as String? ?? ''),
    );
    assertIdentity(reader, generation);
  }

  Future<StoreBookProduct?> product(PoetryCollection book) async {
    await _identityWork;
    await configureStore();
    if (book.productId == null) return null;
    return provider.product(book.productId!);
  }

  Future<StorePurchaseResult> purchase(
    PoetryCollection book,
    StoreBookProduct product,
    bool agreed,
  ) async {
    if (busy || !agreed || readerId == null || owns(book.id))
      return StorePurchaseResult.failed;
    if (product.id != book.productId || product.id != 'shelf_book_${book.id}')
      return StorePurchaseResult.failed;
    busy = true;
    notifyListeners();
    final reader = readerId!, generation = _generation;
    try {
      await configureStore();
      await request(
        'library/books/${book.id}/consent',
        method: 'POST',
        data: {'agree': true},
      );
      assertIdentity(reader, generation);
      final result = await provider.buy(product);
      assertIdentity(reader, generation);
      if (result == StorePurchaseResult.confirming) {
        message = 'Confirming your purchase with Shelf…';
        notifyListeners();
        // Retries only ask the server. Never launch another store purchase.
        for (var attempt = 0; attempt < 6; attempt++) {
          try {
            await refresh(operation: 'library/confirm');
          } catch (_) {}
          assertIdentity(reader, generation);
          if (owns(book.id)) {
            message = 'Your book is ready in My Library.';
            return result;
          }
          await Future<void>.delayed(const Duration(seconds: 3));
        }
        message = 'Still confirming. You can check again in My Library; do not buy again.';
      }
      return result;
    } finally {
      if (generation == _generation) {
        busy = false;
        notifyListeners();
      }
    }
  }

  Future<void> restore() async {
    if (busy) return;
    busy = true;
    notifyListeners();
    final generation = _generation;
    try {
      await configureStore();
      await provider.restore();
      await refresh(operation: 'library/restore');
      message = 'Library checked. Purchases stay with the Shelf account that bought them.';
    } finally {
      if (generation == _generation) {
        busy = false;
        notifyListeners();
      }
    }
  }

  Future<Map<String, dynamic>> download(PoetryCollection book) async {
    final reader = readerId!, generation = _generation;
    await refresh();
    if (!owns(book.id)) throw const AccountFailure(404);
    try {
      final metadata = await request(
        'library/books/${Uri.encodeComponent(book.slug)}',
      );
      final bookJson = metadata['data'] as Map<String, dynamic>;
      if (bookJson['cover_url'] != null) {
        final bytes = await service.media(
          bookJson['cover_url'] as String,
          accounts.readerToken!,
        );
        assertIdentity(reader, generation);
        bookJson['cover_url'] = await downloads.writeMedia(
          reader,
          book.id!,
          'cover.bin',
          bytes,
        );
      }
      final content = await request(
        'library/books/${Uri.encodeComponent(book.slug)}/content',
      );
      final poems = <Map<String, dynamic>>[];
      for (final summary in content['data'] as List) {
        final json =
            (await request('library/poems/${summary['id']}'))['data']
                as Map<String, dynamic>;
        final art = json['artwork'] as Map<String, dynamic>;
        if (art['url'] != null) {
          final bytes = await service.media(
            art['url'] as String,
            accounts.readerToken!,
          );
          assertIdentity(reader, generation);
          art['url'] = await downloads.writeMedia(
            reader,
            book.id!,
            'art-${json['id']}.bin',
            bytes,
          );
        }
        final audio = json['audio'] as Map<String, dynamic>;
        if (audio['available'] == true) {
          final access = await request('library/poems/${json['id']}/audio');
          final bytes = await service.media(
            access['url'] as String,
            accounts.readerToken!,
          );
          assertIdentity(reader, generation);
          audio['local_url'] = await downloads.writeMedia(
            reader,
            book.id!,
            'audio-${json['id']}.${audio['format'] ?? 'mp3'}',
            bytes,
          );
        }
        poems.add(json);
      }
      // A refund or account switch during download prevents committing the copy.
      await refresh();
      assertIdentity(reader, generation);
      if (!owns(book.id)) throw const AccountFailure(404);
      final copy = {
        'collection': metadata['data'],
        'summaries': content['data'],
        'poems': poems,
      };
      await downloads.writeBook(reader, book.id!, copy);
      assertIdentity(reader, generation);
      downloaded.add(book.id!);
      await _persist(reader, generation);
      notifyListeners();
      return copy;
    } catch (_) {
      await downloads.removeBook(reader, book.id!);
      if (generation == _generation) {
        downloaded.remove(book.id);
        notifyListeners();
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> copy(PoetryCollection book) async {
    await _identityWork;
    final reader = readerId, generation = _generation;
    if (reader == null) throw const AccountFailure(401);
    try {
      await refresh();
    } on AccountFailure catch (e) {
      if (e.status == 401 || e.status == 403 || e.status == 404) rethrow;
    } catch (_) {}
    assertIdentity(reader, generation);
    if (!owns(book.id) || !offlineValid) throw const AccountFailure(403);
    _lastObserved = clock();
    await _persist(reader, generation);
    final cached = downloaded.contains(book.id)
        ? await downloads.readBook(reader, book.id!)
        : null;
    assertIdentity(reader, generation);
    return cached ?? await download(book);
  }

  Future<void> removeDownload(int book) async {
    final reader = readerId;
    final generation = _generation;
    if (reader == null) return;
    for (final stop in stopReading.toList()) {
      stop();
    }
    await downloads.removeBook(reader, book);
    assertIdentity(reader, generation);
    downloaded.remove(book);
    await _persist(reader, generation);
    notifyListeners();
  }

  @override
  void dispose() {
    _timer?.cancel();
    accounts.removeListener(_identityChanged);
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }
}

class LibraryScope extends InheritedNotifier<LibraryController> {
  const LibraryScope({
    required LibraryController? controller,
    required super.child,
    super.key,
  }) : super(notifier: controller);
  static LibraryController? of(BuildContext context) =>
      context.dependOnInheritedWidgetOfExactType<LibraryScope>()?.notifier;
}
