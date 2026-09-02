import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:pitswal/app.dart';
import 'package:pitswal/models/app_config.dart';
import 'package:pitswal/models/poem.dart';
import 'package:pitswal/models/poetry_collection.dart';
import 'package:pitswal/repository/poetry_repository.dart';
import 'package:pitswal/settings/reader_settings.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  testWidgets('Home shows every collection in repository order', (
    tester,
  ) async {
    SharedPreferences.setMockInitialValues({});
    final settings = await ReaderSettings.load(
      await SharedPreferences.getInstance(),
    );

    await tester.pumpWidget(
      PitswalApp(
        repository: const _SixCollectionDataSource(),
        readerSettings: settings,
        ownerPreviewMode: true,
      ),
    );
    await tester.pumpAndSettle();

    final scrollable = find.byType(Scrollable).first;
    final seenTitles = <String>[];
    for (final collection in _SixCollectionDataSource.collections) {
      final title = find.text(collection.title);
      await tester.scrollUntilVisible(title, 180, scrollable: scrollable);
      expect(title, findsOneWidget);
      seenTitles.add(tester.widget<Text>(title).data!);
    }

    expect(
      seenTitles,
      _SixCollectionDataSource.collections
          .map((collection) => collection.title)
          .toList(),
    );
  });
}

class _SixCollectionDataSource implements PoetryDataSource {
  const _SixCollectionDataSource();

  static const collections = [
    PoetryCollection(
      title: 'لومړۍ ټولګه',
      slug: 'collection-1',
      author: 'اجمل اند',
      poemCount: 1,
      sortOrder: 1,
      isActive: false,
    ),
    PoetryCollection(
      title: 'دویمه ټولګه',
      slug: 'collection-2',
      author: 'اجمل اند',
      poemCount: 1,
      sortOrder: 2,
      isActive: false,
    ),
    PoetryCollection(
      title: 'درېیمه ټولګه',
      slug: 'collection-3',
      author: 'اجمل اند',
      poemCount: 1,
      sortOrder: 3,
      isActive: false,
    ),
    PoetryCollection(
      title: 'سيند په پرخه کې',
      slug: 'collection-4',
      author: 'پروین پژواک',
      poemCount: 74,
      sortOrder: 4,
      isActive: false,
    ),
    PoetryCollection(
      title: 'پنځمه ټولګه',
      slug: 'collection-5',
      author: 'اجمل اند',
      poemCount: 1,
      sortOrder: 5,
      isActive: false,
    ),
    PoetryCollection(
      title: 'شپږمه ټولګه',
      slug: 'collection-6',
      author: 'اجمل اند',
      poemCount: 1,
      sortOrder: 6,
      isActive: false,
    ),
  ];

  @override
  Future<CatalogueSnapshot?> loadCachedCatalogue() async => null;

  @override
  Future<CatalogueSnapshot> refreshCatalogue(CatalogueSnapshot? cached) async =>
      const CatalogueSnapshot(
        config: AppConfig(
          appName: 'پېڅوَل',
          slogan: 'اجمل اند بشپړه شاعري',
          contentVersion: 1,
          minAppVersion: '1.0.0',
        ),
        collections: collections,
        fromCache: false,
      );

  @override
  Future<CollectionBundle> loadCollection(String slug, int contentVersion) =>
      throw UnimplementedError();

  @override
  Future<PoemDetail> loadPoem(
    int id,
    int contentVersion, {
    bool refreshEntitlement = false,
  }) => throw UnimplementedError();

  @override
  Future<AudioAccess> loadAudio(int poemId) => throw UnimplementedError();
}
