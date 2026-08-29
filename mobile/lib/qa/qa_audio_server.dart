import 'dart:io';
import 'dart:typed_data';

/// Debug-only loopback stream for a generated non-voice QA tone.
class QaAudioServer {
  QaAudioServer._();

  static final instance = QaAudioServer._();
  HttpServer? _server;

  Future<Uri> get url async {
    _server ??= await _start();
    return Uri.parse(
      'http://${InternetAddress.loopbackIPv4.address}:${_server!.port}/synthetic-test-tone.wav',
    );
  }

  Future<HttpServer> _start() async {
    final bytes = _toneWav();
    final server = await HttpServer.bind(InternetAddress.loopbackIPv4, 0);
    server.listen((request) async {
      request.response.headers.contentType = ContentType('audio', 'wav');
      request.response.contentLength = bytes.length;
      request.response.add(bytes);
      await request.response.close();
    });
    return server;
  }

  Uint8List _toneWav() {
    const sampleRate = 16000;
    const seconds = 8;
    const samples = sampleRate * seconds;
    final dataLength = samples * 2;
    final bytes = ByteData(44 + dataLength);

    void ascii(int offset, String value) {
      for (var index = 0; index < value.length; index++) {
        bytes.setUint8(offset + index, value.codeUnitAt(index));
      }
    }

    ascii(0, 'RIFF');
    bytes.setUint32(4, 36 + dataLength, Endian.little);
    ascii(8, 'WAVE');
    ascii(12, 'fmt ');
    bytes.setUint32(16, 16, Endian.little);
    bytes.setUint16(20, 1, Endian.little);
    bytes.setUint16(22, 1, Endian.little);
    bytes.setUint32(24, sampleRate, Endian.little);
    bytes.setUint32(28, sampleRate * 2, Endian.little);
    bytes.setUint16(32, 2, Endian.little);
    bytes.setUint16(34, 16, Endian.little);
    ascii(36, 'data');
    bytes.setUint32(40, dataLength, Endian.little);

    // A quiet deterministic 440 Hz sine tone, never speech or owner content.
    for (var sample = 0; sample < samples; sample++) {
      final phase = (sample * 440 * 2 * 3.141592653589793) / sampleRate;
      bytes.setInt16(
        44 + sample * 2,
        (1200 * _sin(phase)).round(),
        Endian.little,
      );
    }
    return bytes.buffer.asUint8List();
  }

  double _sin(double value) {
    // Fast bounded approximation is sufficient for a synthetic QA tone.
    final turns = value / (2 * 3.141592653589793);
    final normalized = turns - turns.floorToDouble();
    final x = normalized < 0.5 ? normalized * 4 - 1 : 3 - normalized * 4;
    return x * (1.27323954 - 0.405284735 * x.abs());
  }
}
