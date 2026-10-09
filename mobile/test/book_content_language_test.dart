import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/app.dart';
import 'package:shelf/models/poem.dart';
import 'package:shelf/repository/poetry_repository.dart';
import 'package:shelf/services/api_client.dart';
import 'package:shelf/settings/interface_language.dart';
import 'package:shelf/settings/reader_settings.dart';
import 'package:shelf/share_cards/poem_card_widget.dart';
import 'package:shelf/share_cards/share_card_models.dart';

import 'test_support.dart';

const title = 'د کتاب سرلیک — عنوان کتاب';
const subtitle = 'فرعي سرلیک ۱۲۳';
const name = 'پروین پژواک';
const description = 'ټ ډ ړ ږ ښ ڼ ې ۍ\n\nفارسی: می\u200cروم، رؤيا';
const workTitle = 'د شعر سرلیک';
const body = 'لومړۍ کرښه\nدويمه کرښه\n\nزړۀ، مينهٔ، رؤيا';
final book = {
  ...collectionJson,
  'title': title,
  'subtitle': subtitle,
  'description': description,
  'cover_url': null,
  'authors': [
    {'id': 1, 'slug': 'writer', 'name': name, 'role': 'author'},
  ],
};
PoetryRepository source() => PoetryRepository(
  api: ApiClient(
    baseUri: Uri.parse('https://shelf.services/api/'),
    client: MockClient((request) async {
      final path = request.url.path;
      final payload = switch (path) {
        '/api/app-config' => appConfigJson,
        '/api/collections' => {
          'data': [book],
        },
        '/api/categories' || '/api/authors' => {'data': []},
        '/api/authors/writer' => {
          'data': {
            'id': 1,
            'slug': 'writer',
            'name': name,
            'biography': description,
          },
        },
        '/api/collections/hendaray-aw-chine' => {'data': book},
        '/api/collections/hendaray-aw-chine/poems' => {
          'data': [
            {...poemSummaryJson, 'title': workTitle},
          ],
        },
        '/api/poems/301' => {
          'data': poemDetailJson(title: workTitle, body: body),
        },
        _ => {},
      };
      return http.Response(
        jsonEncode(payload),
        200,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
    }),
  ),
  cache: MemoryCacheStore(),
);

void main() {
  late InterfaceLanguageSettings language;
  late ReaderSettings settings;
  setUp(() async {
    SharedPreferences.setMockInitialValues({});
    final preferences = await SharedPreferences.getInstance();
    language = InterfaceLanguageSettings.load(preferences);
    settings = await ReaderSettings.load(preferences);
    await language.select(InterfaceLanguage.ps);
  });
  void expectSource(WidgetTester tester, Finder finder, String value) {
    final text = tester.widget<Text>(finder);
    expect(text.data, value);
    expect(
      text.textDirection ?? Directionality.of(tester.element(finder)),
      TextDirection.rtl,
    );
    expect(text.textAlign, TextAlign.right);
  }

  Future<void> switchLanguage(
    WidgetTester tester,
    InterfaceLanguage value,
  ) async {
    await language.select(value);
    await tester.pumpAndSettle();
  }

  testWidgets(
    'titles, credits, descriptions, contents and author pages stay unchanged and RTL',
    (tester) async {
      await tester.pumpWidget(
        ShelfApp(
          repository: source(),
          readerSettings: settings,
          languageSettings: language,
        ),
      );
      await tester.pumpAndSettle();
      final tile = find.byKey(const Key('collection-title-hendaray-aw-chine'));
      final bounds = tester.getRect(tile);
      expectSource(tester, tile, title);
      await tester.tap(find.byKey(const Key('store-language-toggle')));
      await tester.pumpAndSettle();
      await tester.tap(find.text('English'));
      await tester.pumpAndSettle();
      expectSource(tester, tile, title);
      // Shared navigation mirrors in English; source direction and available
      // title width remain unchanged.
      expect(tester.getRect(tile).size, bounds.size);
      await tester.tap(tile);
      await tester.pumpAndSettle();
      final heading = find.byKey(const Key('collection-detail-title'));
      final headingBounds = tester.getRect(heading);
      for (final value in InterfaceLanguage.values) {
        await switchLanguage(tester, value);
        expectSource(tester, heading, title);
        expect(tester.getRect(heading), headingBounds);
        expectSource(tester, find.text(subtitle), subtitle);
        expectSource(tester, find.text(name), name);
      }
      await switchLanguage(tester, InterfaceLanguage.en);
      await tester.scrollUntilVisible(find.text('About the book'), 200);
      await tester.tap(find.text('About the book'));
      await tester.pumpAndSettle();
      final descriptionFinder = find.byWidgetPredicate(
        (w) => w is SelectableText && w.data == description,
      );
      final before = tester.widget<SelectableText>(descriptionFinder);
      final descriptionSize = tester.getSize(descriptionFinder);
      await switchLanguage(tester, InterfaceLanguage.ps);
      final after = tester.widget<SelectableText>(descriptionFinder);
      expect(after.data, description);
      expect(after.textDirection, TextDirection.rtl);
      expect(after.textAlign, TextAlign.right);
      expect(after.style, before.style);
      expect(tester.getSize(descriptionFinder), descriptionSize);
      await tester.scrollUntilVisible(
        find.text(workTitle),
        200,
        scrollable: find.byType(Scrollable).first,
      );
      expectSource(tester, find.text(workTitle), workTitle);
      final contentBounds = tester.getRect(find.text(workTitle));
      await switchLanguage(tester, InterfaceLanguage.en);
      expectSource(tester, find.text(workTitle), workTitle);
      expect(tester.getSize(find.text(workTitle)), contentBounds.size);
      await tester.scrollUntilVisible(
        find.widgetWithText(TextButton, name),
        -250,
        scrollable: find.byType(Scrollable).first,
      );
      await tester.tap(find.widgetWithText(TextButton, name));
      await tester.pumpAndSettle();
      for (final value in InterfaceLanguage.values) {
        await switchLanguage(tester, value);
        expectSource(tester, find.text(name).first, name);
        expectSource(tester, find.text(description), description);
      }
    },
  );
  testWidgets(
    'reader source, font, wrapping and position do not change with interface language',
    (tester) async {
      await tester.pumpWidget(
        ShelfApp(
          repository: source(),
          readerSettings: settings,
          languageSettings: language,
        ),
      );
      await tester.pumpAndSettle();
      await tester.tap(find.text(title));
      await tester.pumpAndSettle();
      await tester.scrollUntilVisible(
        find.byKey(const Key('read-sample')),
        200,
      );
      await tester.tap(find.byKey(const Key('read-sample')));
      await tester.pumpAndSettle();
      final finder = find.byKey(const Key('poem-body'));
      final before = tester.widget<SelectableText>(finder);
      final bounds = tester.getRect(finder);
      for (final value in InterfaceLanguage.values) {
        await switchLanguage(tester, value);
        final after = tester.widget<SelectableText>(finder);
        expect(after.data, body);
        expect(after.textDirection, TextDirection.rtl);
        expect(after.textAlign, TextAlign.start);
        expect(after.style, before.style);
        expect(tester.getRect(finder), bounds);
        expectSource(tester, find.byKey(const Key('poem-title')), workTitle);
      }
    },
  );
  testWidgets(
    'share card source, title, credits and geometry stay RTL in English',
    (tester) async {
      final poem = PoemDetail.fromJson(
        poemDetailJson(title: workTitle, body: body),
      );
      final request = ShareCardRequest(
        poem: poem,
        collectionTitle: title,
        scope: ShareCardScope.accessiblePoem,
        text: body,
        theme: ShareCardTheme.parchment,
      );
      await tester.pumpWidget(
        InterfaceLanguageScope(
          settings: language,
          child: MaterialApp(
            home: Center(
              child: PoemCardWidget(
                request: request,
                page: const ShareCardPage(
                  text: body,
                  pageNumber: 1,
                  pageCount: 1,
                ),
              ),
            ),
          ),
        ),
      );
      await tester.pumpAndSettle();
      final finder = find.byKey(const Key('card-poem-text'));
      final bounds = tester.getRect(finder);
      final before = tester.widget<Text>(finder);
      for (final value in InterfaceLanguage.values) {
        await switchLanguage(tester, value);
        final after = tester.widget<Text>(finder);
        expect(after.data, body);
        expect(after.textDirection, TextDirection.rtl);
        expect(after.textAlign, TextAlign.start);
        expect(after.style, before.style);
        expect(tester.getRect(finder), bounds);
        expectSource(tester, find.text(workTitle), workTitle);
        expectSource(tester, find.text(poem.cardAuthor), poem.cardAuthor);
      }
    },
  );
}
