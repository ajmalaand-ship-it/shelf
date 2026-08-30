import 'dart:io';
import 'dart:typed_data';

class ShareCardFiles {
  const ShareCardFiles(this.directory);

  final Directory directory;

  String filename({
    required int poemId,
    required int pageNumber,
    required DateTime createdAt,
  }) {
    final timestamp = createdAt.toUtc().toIso8601String().replaceAll(
      RegExp(r'[^0-9]'),
      '',
    );
    return 'pitswal_poem_${poemId}_${timestamp}_'
        '${pageNumber.toString().padLeft(2, '0')}.png';
  }

  Future<File> write({
    required Uint8List bytes,
    required int poemId,
    required int pageNumber,
    required DateTime createdAt,
  }) async {
    await directory.create(recursive: true);
    final file = File(
      '${directory.path}/${filename(poemId: poemId, pageNumber: pageNumber, createdAt: createdAt)}',
    );
    return file.writeAsBytes(bytes, flush: true);
  }

  Future<void> cleanStale({
    required DateTime now,
    Duration maximumAge = const Duration(hours: 24),
  }) async {
    if (!await directory.exists()) return;
    await for (final entity in directory.list()) {
      if (entity is File &&
          now.difference(await entity.lastModified()) > maximumAge) {
        await entity.delete();
      }
    }
  }

  Future<void> remove(List<File> files) async {
    for (final file in files) {
      if (await file.exists()) await file.delete();
    }
  }
}
