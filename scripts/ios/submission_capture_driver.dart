import 'dart:io';
import 'dart:typed_data';

import 'package:integration_test/integration_test_driver_extended.dart';

Future<void> main() async {
  final output = Directory(Platform.environment['SHELF_CAPTURE_OUTPUT']!);
  await output.create(recursive: true);
  await integrationDriver(
    onScreenshot: (name, bytes, [args]) async {
      if (!RegExp(r'^[a-z0-9-]+$').hasMatch(name)) return false;
      final data = Uint8List.fromList(bytes);
      if (data.length < 24 || data[0] != 137 || data[1] != 80) return false;
      final header = ByteData.sublistView(data);
      final width = header.getUint32(16), height = header.getUint32(20);
      final expected = Platform.environment['SHELF_CAPTURE_DEVICE'] == 'iphone'
          ? {'1320x2868', '1290x2796'}
          : {'2064x2752', '2048x2732'};
      if (!expected.contains('${width}x$height')) {
        throw StateError('Unsupported native capture size ${width}x$height');
      }
      await File('${output.path}/$name.png').writeAsBytes(data, flush: true);
      return true;
    },
    responseDataCallback: null,
  );
}
