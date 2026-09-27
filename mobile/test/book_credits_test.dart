import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/models/poetry_collection.dart';
import 'package:shelf/repository/poetry_repository.dart';
import 'package:shelf/screens/collection_detail_screen.dart';
import 'package:shelf/services/api_client.dart';
import 'package:shelf/settings/reader_settings.dart';
import 'package:shelf/widgets/collection_card.dart';

import 'test_support.dart' show MemoryCacheStore;

const bookJson = {
  'title': 'Book',
  'slug': 'book',
  'author': 'Legacy name',
  'language': 'ps',
  'sort_order': 1,
  'authors': [
    {'id': 1, 'slug': 'first', 'name': 'پروین پژواک', 'role': 'author'},
    {'id': 2, 'slug': 'second', 'name': 'Second author', 'role': 'author'},
    {'id': 3, 'slug': 'translator', 'name': 'اجمل اند', 'role': 'translator'},
    {'id': 4, 'slug': 'editor', 'name': 'Editor', 'role': 'editor'},
  ],
};

void main() {
  test('credits and language survive API and cache round trips in order', () {
    final book = PoetryCollection.fromJson(bookJson);
    final cached = PoetryCollection.fromJson(
      jsonDecode(jsonEncode(book.toJson())),
    );
    expect(cached.language, 'ps');
    expect(cached.authors.map((credit) => credit.id), [1, 2, 3, 4]);
    expect(cached.creditedAuthors, 'پروین پژواک، Second author');
    expect(cached.creditedTranslators, 'ژباړه: اجمل اند');
    expect(cached.author, 'Legacy name');
  });

  test('old responses remain readable without credits or language', () {
    final old = PoetryCollection.fromJson({
      'title': 'Old book',
      'slug': 'old',
      'author': 'Legacy name',
    });
    expect(old.language, isNull);
    expect(old.authors, isEmpty);
    expect(old.creditedAuthors, 'Legacy name');
    expect(old.creditedTranslators, isNull);
  });

  test('malformed credit fields fail safely', () {
    for (final value in [
      'invalid',
      [null],
      [
        {'id': 'invalid', 'slug': 'person', 'name': 'Person', 'role': 'author'},
      ],
    ]) {
      expect(
        () => PoetryCollection.fromJson({...bookJson, 'authors': value}),
        throwsFormatException,
      );
    }
    expect(
      () => PoetryCollection.fromJson({...bookJson, 'language': 42}),
      throwsFormatException,
    );
  });

  testWidgets('book card displays credited authors and labelled translator', (
    tester,
  ) async {
    SharedPreferences.setMockInitialValues({});
    final settings = await ReaderSettings.load(
      await SharedPreferences.getInstance(),
    );
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: CollectionCard(
            collection: PoetryCollection.fromJson(bookJson),
            onTap: () {},
            readerSettings: settings,
          ),
        ),
      ),
    );
    expect(find.text('پروین پژواک، Second author'), findsOneWidget);
    expect(find.text('ژباړه: اجمل اند'), findsOneWidget);
    expect(find.text('Legacy name'), findsNothing);
    expect(find.text('Editor'), findsNothing);
  });

  testWidgets(
    'book detail displays credits without replacing work attribution',
    (tester) async {
      SharedPreferences.setMockInitialValues({});
      final settings = await ReaderSettings.load(
        await SharedPreferences.getInstance(),
      );
      final repository = PoetryRepository(
        api: ApiClient(
          client: MockClient(
            (request) async => http.Response(
              jsonEncode({
                'data': request.url.path.endsWith('/poems') ? [] : bookJson,
              }),
              200,
              headers: {'content-type': 'application/json; charset=utf-8'},
            ),
          ),
        ),
        cache: MemoryCacheStore(),
      );
      await tester.pumpWidget(
        MaterialApp(
          home: CollectionDetailScreen(
            slug: 'book',
            contentVersion: 1,
            repository: repository,
            readerSettings: settings,
          ),
        ),
      );
      await tester.pumpAndSettle();
      expect(find.text('پروین پژواک، Second author'), findsOneWidget);
      expect(find.text('ژباړه: اجمل اند'), findsOneWidget);
      expect(find.text('Legacy name'), findsNothing);
    },
  );
}
