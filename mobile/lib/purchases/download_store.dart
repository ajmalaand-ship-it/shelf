import 'dart:convert';
import 'dart:io';
import 'dart:typed_data';

import 'package:cryptography/cryptography.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

abstract interface class BookDownloadStore {
  Future<Map<String, dynamic>?> readManifest(int reader);
  Future<void> writeManifest(int reader, Map<String, dynamic> value);
  Future<Map<String, dynamic>?> readBook(int reader, int book);
  Future<void> writeBook(int reader, int book, Map<String, dynamic> value);
  Future<void> removeBook(int reader, int book);
  Future<void> clear(int reader);
  Future<String> writeMedia(int reader, int book, String name, Uint8List bytes);
}

// Paid text/manifest are authenticated-encrypted with a per-reader keystore key.
// Media stays in private app storage and is removed with its account or book.
class PrivateBookDownloadStore implements BookDownloadStore {
  PrivateBookDownloadStore(this.root);
  final Directory root;
  final _secure = const FlutterSecureStorage();
  final _cipher = AesGcm.with256bits();
  Future<SecretKey> _key(int reader) async {
    final name = 'shelf.library.key.$reader';
    var raw = await _secure.read(key: name);
    if (raw == null) {
      raw = base64Encode(await (await _cipher.newSecretKey()).extractBytes());
      await _secure.write(key: name, value: raw);
    }
    return SecretKey(base64Decode(raw));
  }

  File _file(int reader, String name) => File('${root.path}/$reader/$name');
  Future<Map<String, dynamic>?> _read(int reader, String name) async {
    final f = _file(reader, name);
    if (!await f.exists()) return null;
    try {
      final box = SecretBox.fromConcatenation(
        await f.readAsBytes(),
        nonceLength: 12,
        macLength: 16,
      );
      return jsonDecode(
        utf8.decode(await _cipher.decrypt(box, secretKey: await _key(reader))),
      ) as Map<String, dynamic>;
    } catch (_) {
      if (await f.exists()) await f.delete();
      return null;
    }
  }

  Future<void> _write(
    int reader,
    String name,
    Map<String, dynamic> value,
  ) async {
    final f = _file(reader, name);
    await f.parent.create(recursive: true);
    final box = await _cipher.encrypt(
      utf8.encode(jsonEncode(value)),
      secretKey: await _key(reader),
    );
    final tmp = File('${f.path}.new-${box.nonce.join('-')}');
    await tmp.writeAsBytes(box.concatenation(), flush: true);
    await tmp.rename(f.path);
  }

  @override
  Future<Map<String, dynamic>?> readManifest(int reader) =>
      _read(reader, 'manifest.enc');
  @override
  Future<void> writeManifest(int reader, Map<String, dynamic> value) =>
      _write(reader, 'manifest.enc', value);
  @override
  Future<Map<String, dynamic>?> readBook(int reader, int book) =>
      _read(reader, '$book/book.enc');
  @override
  Future<void> writeBook(int reader, int book, Map<String, dynamic> value) =>
      _write(reader, '$book/book.enc', value);
  @override
  Future<void> removeBook(int reader, int book) async {
    final dir = Directory('${root.path}/$reader/$book');
    if (await dir.exists()) await dir.delete(recursive: true);
  }

  @override
  Future<void> clear(int reader) async {
    final dir = Directory('${root.path}/$reader');
    if (await dir.exists()) await dir.delete(recursive: true);
    await _secure.delete(key: 'shelf.library.key.$reader');
  }

  @override
  Future<String> writeMedia(
    int reader,
    int book,
    String name,
    Uint8List bytes,
  ) async {
    if (!RegExp(r'^[a-z0-9_.-]+$').hasMatch(name))
      throw const FormatException('Invalid media name');
    final f = _file(reader, '$book/$name');
    await f.parent.create(recursive: true);
    await f.writeAsBytes(bytes, flush: true);
    return f.uri.toString();
  }
}
