import '../models/poem.dart';
import '../models/poetry_collection.dart';
import '../repository/poetry_repository.dart';
import 'library_controller.dart';

class OwnedBookRepository implements PoetryDataSource {
  OwnedBookRepository(this.library, this.book) : reader = library.readerId;
  final LibraryController library;
  final PoetryCollection book;
  final int? reader;
  Future<Map<String, dynamic>> _copy() async {
    if (reader == null || library.readerId != reader)
      throw StateError('This account is signed out');
    return library.copy(book);
  }

  @override
  Future<CollectionBundle> loadCollection(
    String slug,
    int contentVersion,
  ) async {
    if (slug != book.slug) throw StateError('Wrong book');
    final data = await _copy();
    return CollectionBundle(
      collection: PoetryCollection.fromJson(
        data['collection'] as Map<String, dynamic>,
      ),
      poems: (data['summaries'] as List)
          .map((p) => PoemSummary.fromJson(p as Map<String, dynamic>))
          .toList(),
    );
  }

  @override
  Future<PoemDetail> loadPoem(
    int id,
    int contentVersion, {
    bool refreshEntitlement = false,
  }) async {
    final data = await _copy();
    return PoemDetail.fromJson(
      (data['poems'] as List).cast<Map<String, dynamic>>().firstWhere(
        (p) => p['id'] == id,
      ),
      allowLocalMedia: true,
    );
  }

  @override
  Future<AudioAccess> loadAudio(int poemId) async {
    final data = await _copy();
    final poem = (data['poems'] as List)
        .cast<Map<String, dynamic>>()
        .firstWhere((p) => p['id'] == poemId);
    final audio = poem['audio'] as Map<String, dynamic>;
    final uri = Uri.parse(audio['local_url'] as String);
    if (uri.scheme != 'file')
      throw const FormatException('Expected a local download');
    return AudioAccess(
      url: uri,
      cacheKey: audio['cache_key'] as String,
      format: audio['format'] as String?,
      durationSeconds: audio['duration_seconds'] as int?,
    );
  }

  @override
  Future<CatalogueSnapshot?> loadCachedCatalogue() async => null;
  @override
  Future<CatalogueSnapshot> refreshCatalogue(CatalogueSnapshot? cached) =>
      throw UnsupportedError('Library is separate from Store');
}
