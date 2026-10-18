// Regression fixtures are used only for checks, never for submission screenshots.
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/app.dart';
import 'package:shelf/repository/poetry_repository.dart';
import 'package:shelf/screens/collection_detail_screen.dart';
import 'package:shelf/services/api_client.dart';
import 'package:shelf/settings/interface_language.dart';
import 'package:shelf/settings/reader_settings.dart';
import 'package:shelf/widgets/bookstore_widgets.dart';

import 'test_support.dart';
import 'capture_navigation.dart';

void main() {
  testWidgets('Pop only active route with two visible Back buttons', (
    tester,
  ) async {
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: Builder(
            builder: (context) => TextButton(
              onPressed: () => Navigator.of(context).push(
                MaterialPageRoute<void>(
                  builder: (context) => Scaffold(
                    appBar: AppBar(leading: const BackButton()),
                    body: Column(
                      children: [
                        const BackButton(),
                        TextButton(
                          key: const Key('details-marker'),
                          onPressed: () => Navigator.of(context).push(
                            MaterialPageRoute<void>(
                              builder: (_) => Scaffold(
                                appBar: AppBar(leading: const BackButton()),
                                body: const Column(
                                  children: [
                                    BackButton(),
                                    Text('Sample', key: Key('sample-marker')),
                                  ],
                                ),
                              ),
                            ),
                          ),
                          child: const Text('Open sample'),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
              child: const Text('Store'),
            ),
          ),
        ),
      ),
    );
    await tester.tap(find.text('Store'));
    await tester.pumpAndSettle();
    await tester.tap(find.byKey(const Key('details-marker')));
    await tester.pumpAndSettle();
    expect(find.byType(BackButton), findsNWidgets(2));
    await popCaptureRoute(tester, find.byKey(const Key('sample-marker')));
    expect(find.byKey(const Key('details-marker')), findsOneWidget);
    expect(find.byType(BackButton), findsNWidgets(2));
    await popCaptureRoute(tester, find.byKey(const Key('details-marker')));
    expect(find.text('Store'), findsOneWidget);
    expect(find.byType(BackButton), findsNothing);
  });
  for (final size in [const Size(430, 932), const Size(1032, 1376)]) {
    testWidgets('Capture all six visible Store cards at $size without Search', (
      tester,
    ) async {
      tester.view.physicalSize = size;
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);
      SharedPreferences.setMockInitialValues({
        'shelf.interface_language': 'en',
      });
      final prefs = await SharedPreferences.getInstance();
      final books = [
        for (final id in [3, 4, 5, 6, 7, 8])
          {
            ...collectionJson,
            'id': id,
            'slug': 'fixture-book-$id',
            'title': 'Capture fixture book $id',
            'cover_url': null,
          },
      ];
      var searchRequests = 0;
      final repository = PoetryRepository(
        api: ApiClient(
          baseUri: Uri.parse('https://shelf.services/api/'),
          client: MockClient((request) async {
            final path = request.url.path;
            Object payload;
            if (path == '/api/app-config') {
              payload = appConfigJson;
            } else if (path == '/api/authors' || path == '/api/categories') {
              payload = {'data': []};
            } else if (path == '/api/collections') {
              if (request.url.queryParameters.containsKey('q')) {
                searchRequests++;
              }
              payload = {'data': books};
            } else if (path.endsWith('/poems')) {
              payload = {
                'data': [poemSummaryJson],
              };
            } else {
              final book = books.firstWhere(
                (b) => path.endsWith(b['slug'] as String),
              );
              payload = {'data': book};
            }
            return http.Response.bytes(
              utf8.encode(jsonEncode(payload)),
              200,
              headers: {'content-type': 'application/json; charset=utf-8'},
            );
          }),
        ),
        cache: MemoryCacheStore(),
      );
      await tester.pumpWidget(
        ShelfApp(
          repository: repository,
          readerSettings: await ReaderSettings.load(prefs),
          languageSettings: InterfaceLanguageSettings.load(prefs),
        ),
      );
      await tester.pumpAndSettle();
      // Alternate catalogue ends to exercise scrolling and returning to Store.
      for (final id in [8, 3, 7, 4, 6, 5]) {
        final book = await openCaptureBook(tester, id);
        expect(book.id, id);
        expect(find.byType(CollectionDetailScreen), findsOneWidget);
        expect(
          tester
              .widget<CollectionDetailScreen>(
                find.byType(CollectionDetailScreen),
              )
              .slug,
          'fixture-book-$id',
        );
        expect(
          find.byKey(const Key('read-sample')).hitTestable(),
          findsOneWidget,
        );
        await popCaptureRoute(
          tester,
          find.byKey(const Key('collection-detail-title')),
        );
        await tester.pumpAndSettle();
      }
      expect(searchRequests, 0);
      expect(find.byType(BookPresentation), findsWidgets);
    });
  }
}
