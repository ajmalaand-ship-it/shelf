import 'dart:io';

import 'package:gal/gal.dart';
import 'package:share_plus/share_plus.dart';

abstract interface class ShareCardOutput {
  Future<void> share(List<File> files);

  Future<void> save(List<File> files);
}

class PlatformShareCardOutput implements ShareCardOutput {
  const PlatformShareCardOutput();

  @override
  Future<void> share(List<File> files) async {
    await SharePlus.instance.share(
      ShareParams(
        files: files
            .map((file) => XFile(file.path, mimeType: 'image/png'))
            .toList(),
        title: 'پېڅوَل — اجمل اند',
      ),
    );
  }

  @override
  Future<void> save(List<File> files) async {
    if (!await Gal.hasAccess() && !await Gal.requestAccess()) {
      throw StateError('Gallery permission denied');
    }
    for (final file in files) {
      await Gal.putImage(file.path);
    }
  }
}
