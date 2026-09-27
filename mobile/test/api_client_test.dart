import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shelf/services/api_client.dart';

void main() {
  test('API client parses UTF-8 data envelopes', () async {
    final client = ApiClient(
      client: MockClient(
        (_) async => http.Response.bytes(
          utf8.encode(
            jsonEncode({
              'data': [
                {'title': 'پېڅوَل'},
              ],
            }),
          ),
          200,
          headers: {'content-type': 'application/json; charset=utf-8'},
        ),
      ),
    );

    expect((await client.getDataList('collections')).single['title'], 'پېڅوَل');
  });

  test('API client distinguishes 404 and rejects malformed JSON', () async {
    final missing = ApiClient(
      client: MockClient((_) async => http.Response('{}', 404)),
    );
    final malformed = ApiClient(
      client: MockClient((_) async => http.Response('{broken', 200)),
    );

    expect(
      () => missing.getDataObject('poems/404'),
      throwsA(isA<ContentNotFoundException>()),
    );
    expect(() => malformed.getObject('app-config'), throwsFormatException);
  });
}
