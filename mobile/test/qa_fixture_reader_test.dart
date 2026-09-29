import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shelf/app.dart';
import 'package:shelf/qa/qa_fixture_repository.dart';
import 'package:shelf/settings/reader_settings.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  Future<ReaderSettings> settings() async {
    SharedPreferences.setMockInitialValues({});
    return ReaderSettings.load(await SharedPreferences.getInstance());
  }

  test('QA catalogue covers all requested reader acceptance states', () async {
    final repository = QaFixtureRepository();
    final catalogue = await repository.refreshCatalogue(null);
    final covered = await repository.loadCollection(
      'qa-reader-covered',
      catalogue.config.contentVersion,
    );
    final plain = await repository.loadCollection(
      'qa-reader-no-cover',
      catalogue.config.contentVersion,
    );

    expect(catalogue.collections, hasLength(2));
    expect(catalogue.collections.first.coverUrl, isNotNull);
    expect(catalogue.collections.last.coverUrl, isNull);
    expect([...covered.poems, ...plain.poems], hasLength(7));
    expect(covered.poems.any((poem) => poem.isUntitled), isTrue);
    expect(covered.poems.any((poem) => poem.locked), isTrue);
    expect(plain.poems.any((poem) => poem.title == 'ازاد نظم'), isTrue);

    final translation = await repository.loadPoem(
      9104,
      catalogue.config.contentVersion,
    );
    final longPoem = await repository.loadPoem(
      9202,
      catalogue.config.contentVersion,
    );
    expect(translation.isTranslation, isTrue);
    expect(translation.originalAuthor, isNotEmpty);
    expect(translation.translator, isNotEmpty);
    expect(translation.locked, isTrue);
    expect(translation.body, isNull);
    expect(longPoem.readableText.split('\n').length, greaterThan(48));
  });

  testWidgets('debug QA mode supports the complete reader route and back', (
    tester,
  ) async {
    tester.view.physicalSize = const Size(360, 720);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    await tester.pumpWidget(
      ShelfApp(
        repository: QaFixtureRepository(),
        readerSettings: await settings(),
        qaMode: true,
      ),
    );
    await tester.pumpAndSettle();

    expect(find.byKey(const Key('debug-qa-notice')), findsOneWidget);
    expect(find.text('Shelf'), findsOneWidget);
    expect(find.text('ټول کتابونه'), findsOneWidget);
    await tester.scrollUntilVisible(
      find.text('د لوست ازموينه — پوښ لري'),
      200,
      scrollable: find.byType(Scrollable).first,
    );
    await tester.tap(find.text('د لوست ازموينه — پوښ لري'));
    await tester.pumpAndSettle();
    await tester.scrollUntilVisible(find.text('مصنوعي غږ ازموينه'), 250);
    expect(find.text('مصنوعي غږ ازموينه'), findsOneWidget);

    await tester.tap(find.text('مصنوعي غږ ازموينه'));
    await tester.pumpAndSettle();
    expect(find.byKey(const Key('poem-body')), findsOneWidget);
    expect(find.textContaining('پښتو توري'), findsOneWidget);
    expect(find.byType(BackButton), findsOneWidget);
    expect(
      Directionality.of(tester.element(find.byType(BackButton))),
      TextDirection.rtl,
    );

    await tester.pageBack();
    await tester.pumpAndSettle();
    expect(find.text('لړلیک'), findsOneWidget);
  });
}
