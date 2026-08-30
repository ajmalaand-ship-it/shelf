import 'dart:io';
import 'dart:typed_data';

import 'package:flutter_test/flutter_test.dart';
import 'package:pitswal/share_cards/share_card_files.dart';

void main() {
  late Directory directory;
  late ShareCardFiles files;

  setUp(() async {
    directory = await Directory.systemTemp.createTemp('pitswal-card-files-');
    files = ShareCardFiles(directory);
  });

  tearDown(() async {
    if (await directory.exists()) await directory.delete(recursive: true);
  });

  test('filename is safe stable and page numbered', () {
    final name = files.filename(
      poemId: 42,
      pageNumber: 3,
      createdAt: DateTime.utc(2026, 8, 29, 12, 34, 56),
    );
    expect(name, 'pitswal_poem_42_20260829123456000_03.png');
    expect(name, matches(RegExp(r'^[a-z0-9_]+\.png$')));
  });

  test('stale temporary cards are cleaned but current cards remain', () async {
    final old = await files.write(
      bytes: Uint8List.fromList([1]),
      poemId: 1,
      pageNumber: 1,
      createdAt: DateTime.utc(2026),
    );
    final current = await files.write(
      bytes: Uint8List.fromList([2]),
      poemId: 2,
      pageNumber: 1,
      createdAt: DateTime.utc(2026),
    );
    await old.setLastModified(DateTime.utc(2026, 8, 27));
    await current.setLastModified(DateTime.utc(2026, 8, 29, 11));

    await files.cleanStale(now: DateTime.utc(2026, 8, 29, 12));
    expect(await old.exists(), isFalse);
    expect(await current.exists(), isTrue);
  });
}
