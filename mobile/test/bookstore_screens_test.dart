import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shelf/app.dart';
import 'package:shelf/accounts/account_screen.dart';
import 'package:shelf/l10n/app_strings.dart';
import 'package:shelf/repository/poetry_repository.dart';
import 'package:shelf/services/api_client.dart';
import 'package:shelf/settings/reader_settings.dart';
import 'package:shelf/widgets/bookstore_widgets.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'test_support.dart';

void main() {
  final requests = <Uri>[];
  var categorized = true;
  var empty = false;
  var fail = false;
  var minimal = false;
  Map<String, dynamic> book(int id) => {
    if (!minimal) ...collectionJson,
    'id': id,
    'slug': 'book-$id',
    'title': 'Book $id',
    'cover_url': null,
    'subtitle': minimal ? null : 'Subtitle',
    'language': 'ps',
    'book_type': 'prose',
    'status': id == 1 ? 'draft' : 'ready',
    'authors': minimal
        ? []
        : [
            {'id': 1, 'slug': 'writer', 'name': 'Writer', 'role': 'author'},
            {
              'id': 2,
              'slug': 'translator',
              'name': 'Translator',
              'role': 'translator',
            },
          ],
    'categories': categorized
        ? [
            {'id': 1, 'slug': 'stories', 'name': 'Stories'},
          ]
        : [],
  };
  final author = {
    'id': 1,
    'slug': 'writer',
    'name': 'Writer',
    'biography': null,
    'image_url': null,
  };
  PoetryRepository repository() => PoetryRepository(
    api: ApiClient(
      baseUri: Uri.parse('https://shelf.services/api/'),
      client: MockClient((request) async {
        requests.add(request.url);
        if (fail) throw http.ClientException('offline');
        final path = request.url.path;
        final page2 = request.url.queryParameters['page'] == '2';
        final Object data;
        if (path.endsWith('app-config')) {
          data = appConfigJson;
        } else if (path == '/api/collections') {
          final query = request.url.queryParameters['q'];
          data = {
            'data': empty || query == 'missing' ? [] : [book(page2 ? 2 : 1)],
            'links': {
              'next': !page2 && !empty && query != 'missing'
                  ? 'https://shelf.services/api/collections?${Uri(queryParameters: {...request.url.queryParameters, 'page': '2'}).query}'
                  : null,
            },
          };
        } else if (path == '/api/categories') {
          data = {
            'data': categorized && !empty
                ? [
                    {'id': 1, 'slug': 'stories', 'name': 'Stories'},
                  ]
                : [],
          };
        } else if (path == '/api/authors') {
          data = {
            'data': empty ? [] : [author],
          };
        } else if (path == '/api/authors/writer') {
          data = {'data': author};
        } else if (path.endsWith('/poems')) {
          data = {
            'data': minimal ? [] : [poemSummaryJson, translationSummaryJson],
          };
        } else if (path.startsWith('/api/collections/book-')) {
          data = {'data': book(1)};
        } else if (path == '/api/poems/301') {
          data = {'data': poemDetailJson()};
        } else {
          return http.Response('{}', 404);
        }
        return http.Response(
          jsonEncode(data),
          200,
          headers: {'content-type': 'application/json; charset=utf-8'},
        );
      }),
    ),
    cache: MemoryCacheStore(),
  );
  Future<void> launch(WidgetTester tester, {bool preview = false}) async {
    SharedPreferences.setMockInitialValues({});
    await tester.pumpWidget(
      ShelfApp(
        repository: repository(),
        readerSettings: await ReaderSettings.load(
          await SharedPreferences.getInstance(),
        ),
        ownerPreviewMode: preview,
      ),
    );
    await tester.pumpAndSettle();
  }

  setUp(() {
    requests.clear();
    categorized = true;
    empty = false;
    fail = false;
    minimal = false;
  });

  testWidgets(
    'Store loads every page, category rows, RTL, missing covers and refresh',
    (tester) async {
      await launch(tester);
      expect(find.text(AppStrings.newBooks), findsOneWidget);
      expect(find.text('Book 2'), findsWidgets);
      expect(find.byType(BookCover), findsWidgets);
      expect(
        Directionality.of(tester.element(find.byType(NavigationBar))),
        TextDirection.rtl,
      );
      expect(requests.where((u) => u.path == '/api/collections').length, 2);
      await tester.drag(find.byType(RefreshIndicator), const Offset(0, 500));
      await tester.pumpAndSettle();
      expect(requests.where((u) => u.path == '/api/collections').length, 4);
      expect(tester.takeException(), isNull);
    },
  );
  testWidgets(
    'Store fallback grid, Library and existing Settings preferences',
    (tester) async {
      categorized = false;
      await launch(tester);
      expect(find.text(AppStrings.allBooks), findsOneWidget);
      await tester.tap(find.byIcon(Icons.local_library_outlined));
      await tester.pumpAndSettle();
      expect(find.text('ننوتل'), findsOneWidget);
      await tester.tap(find.byIcon(Icons.settings_outlined));
      await tester.pumpAndSettle();
      await tester.tap(find.text(AppStrings.readingPreferences));
      await tester.pumpAndSettle();
      expect(find.byKey(const Key('reading-font-selector')), findsOneWidget);
    },
  );
  testWidgets('empty Store and network failure never invent books', (
    tester,
  ) async {
    empty = true;
    await launch(tester);
    expect(find.text(AppStrings.noBooks), findsOneWidget);
    fail = true;
    await tester.drag(find.byType(RefreshIndicator), const Offset(0, 500));
    await tester.pumpAndSettle();
    expect(find.text(AppStrings.error), findsOneWidget);
    expect(find.byType(BookTile), findsNothing);
  });
  testWidgets(
    'Search has empty and no-results states and forwards text plus all filters',
    (tester) async {
      await launch(tester);
      await tester.tap(find.byType(NavigationDestination).at(1));
      await tester.pumpAndSettle();
      expect(find.text(AppStrings.searchEmpty), findsOneWidget);
      await tester.enterText(find.byKey(const Key('book-search')), 'missing');
      await tester.pump(const Duration(milliseconds: 350));
      await tester.pumpAndSettle();
      expect(find.text(AppStrings.noResults), findsOneWidget);
      await tester.enterText(find.byKey(const Key('book-search')), 'Writer');
      await tester.pump(const Duration(milliseconds: 350));
      await tester.pumpAndSettle();
      for (final pair in [
        ('language-filter-all', AppStrings.pashto),
        ('category-filter-all', 'Stories'),
        ('type-filter-all', AppStrings.prose),
      ]) {
        await tester.tap(find.byKey(ValueKey(pair.$1)));
        await tester.pumpAndSettle();
        await tester.tap(find.text(pair.$2).last);
        await tester.pump(const Duration(milliseconds: 350));
        await tester.pumpAndSettle();
      }
      final last = requests.last.queryParameters;
      expect(last['q'], 'Writer');
      expect(last['language'], 'ps');
      expect(last['category'], 'stories');
      expect(last['book_type'], 'prose');
      expect(find.text('Book 2'), findsOneWidget);
    },
  );
  testWidgets(
    'Book page credits lead to author with initials, missing biography and all books',
    (tester) async {
      categorized = false;
      await launch(tester);
      await tester.tap(find.text('Book 1'));
      await tester.pumpAndSettle();
      expect(find.text('Subtitle'), findsOneWidget);
      expect(find.text('ژباړه: Translator'), findsOneWidget);
      expect(
        tester
            .widget<OutlinedButton>(find.byKey(const Key('buy-coming-soon')))
            .onPressed,
        isNotNull,
      );
      await tester.ensureVisible(find.byKey(const Key('buy-coming-soon')));
      await tester.pumpAndSettle();
      await tester.tap(find.byKey(const Key('buy-coming-soon')));
      await tester.pumpAndSettle();
      expect(find.byType(AccountScreen), findsOneWidget);
      await tester.pageBack();
      await tester.pumpAndSettle();
      await tester.scrollUntilVisible(
        find.widgetWithText(TextButton, 'Writer'),
        -200,
        scrollable: find.byType(Scrollable).first,
      );
      await tester.pumpAndSettle();
      await tester.tap(find.widgetWithText(TextButton, 'Writer'));
      await tester.pumpAndSettle();
      expect(find.text(AppStrings.noBiography), findsOneWidget);
      expect(find.byType(AuthorPortrait), findsOneWidget);
      expect(find.text('Book 2'), findsOneWidget);
      expect(
        requests.any(
          (u) =>
              u.queryParameters['author'] == 'writer' &&
              u.queryParameters['page'] == '2',
        ),
        isTrue,
      );
    },
  );
  testWidgets(
    'public book opens sample in unchanged reader and locked item has no action',
    (tester) async {
      categorized = false;
      await launch(tester);
      await tester.tap(find.text('Book 1'));
      await tester.pumpAndSettle();
      await tester.scrollUntilVisible(find.text('ژمى'), 250);
      final lockedTile = find.ancestor(
        of: find.text('ژمى'),
        matching: find.byType(ListTile),
      );
      expect(tester.widget<ListTile>(lockedTile).onTap, isNull);
      await tester.tap(find.text('ژمى'));
      await tester.pumpAndSettle();
      expect(requests.any((u) => u.path == '/api/poems/302'), isFalse);
      await tester.scrollUntilVisible(
        find.byKey(const Key('read-sample')),
        -250,
      );
      await tester.tap(find.byKey(const Key('read-sample')));
      await tester.pumpAndSettle();
      expect(find.byKey(const Key('poem-body')), findsOneWidget);
    },
  );
  testWidgets(
    'owner banner and Draft/Ready status persist across tabs and author page',
    (tester) async {
      categorized = false;
      await launch(tester, preview: true);
      expect(find.text(AppStrings.draft), findsOneWidget);
      expect(find.text(AppStrings.ready), findsOneWidget);
      await tester.tap(find.byIcon(Icons.local_library_outlined));
      await tester.pumpAndSettle();
      expect(find.byKey(const Key('owner-preview-banner')), findsOneWidget);
      await tester.tap(find.byIcon(Icons.storefront_outlined));
      await tester.pumpAndSettle();
      await tester.tap(find.text('Book 1'));
      await tester.pumpAndSettle();
      await tester.scrollUntilVisible(
        find.byKey(const Key('owner-preview-collection-detail-status')),
        150,
      );
      expect(
        find.byKey(const Key('owner-preview-collection-detail-status')),
        findsOneWidget,
      );
      await tester.tap(find.widgetWithText(TextButton, 'Writer'));
      await tester.pumpAndSettle();
      expect(find.text(AppStrings.ready), findsOneWidget);
      expect(find.byKey(const Key('owner-preview-banner')), findsOneWidget);
    },
  );
  testWidgets(
    'small RTL phone handles missing book metadata and empty contents',
    (tester) async {
      tester.view.physicalSize = const Size(360, 640);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);
      categorized = false;
      minimal = true;
      await launch(tester, preview: true);
      await tester.tap(find.text('Book 1'));
      await tester.pumpAndSettle();
      await tester.scrollUntilVisible(
        find.byKey(const Key('read-sample')),
        200,
      );
      expect(
        tester
            .widget<FilledButton>(find.byKey(const Key('read-sample')))
            .onPressed,
        isNull,
      );
      await tester.scrollUntilVisible(find.text(AppStrings.noContent), 200);
      expect(find.text(AppStrings.noContent), findsOneWidget);
      expect(tester.takeException(), isNull);
    },
  );

  test('author and category discovery consume every page', () async {
    final api = ApiClient(
      baseUri: Uri.parse('https://shelf.services/api/'),
      client: MockClient((request) async {
        final second = request.url.queryParameters['page'] == '2';
        final authors = request.url.path.endsWith('/authors');
        return http.Response(
          jsonEncode({
            'data': [
              authors
                  ? {...author, 'id': second ? 2 : 1}
                  : {
                      'id': second ? 2 : 1,
                      'name': 'Category',
                      'slug': second ? 'second' : 'first',
                    },
            ],
            'links': {'next': second ? null : '${request.url}?page=2'},
          }),
          200,
        );
      }),
    );
    final source = PoetryRepository(api: api, cache: MemoryCacheStore());
    expect((await source.loadAuthors()).map((a) => a.id), [1, 2]);
    expect((await source.loadCategories()).map((c) => c.id), [1, 2]);
  });
}
