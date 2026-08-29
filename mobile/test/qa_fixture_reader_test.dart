import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:pitswal/app.dart';
import 'package:pitswal/qa/qa_fixture_repository.dart';
import 'package:pitswal/settings/reader_settings.dart';
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
      PitswalApp(
        repository: QaFixtureRepository(),
        readerSettings: await settings(),
        qaMode: true,
      ),
    );
    await tester.pumpAndSettle();

    expect(find.byKey(const Key('debug-qa-notice')), findsOneWidget);
    expect(find.text('پېڅوَل'), findsOneWidget);
    expect(find.text('ټولګې'), findsOneWidget);
    expect(find.byIcon(Icons.arrow_forward_rounded), findsOneWidget);

    await tester.tap(find.text('ټولګې'));
    await tester.pumpAndSettle();
    expect(find.text('د لوست ازموينه — پوښ لري'), findsOneWidget);
    final cardForwardIcon = tester.widget<Icon>(
      find.byIcon(Icons.arrow_forward_ios_rounded).first,
    );
    expect(cardForwardIcon.icon!.matchTextDirection, isTrue);

    await tester.tap(find.text('د لوست ازموينه — پوښ لري'));
    await tester.pumpAndSettle();
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
    expect(find.text('شعرونه'), findsOneWidget);
  });
}
