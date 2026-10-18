// Capture automation only. Uses existing visible Store controls and live models.
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shelf/models/poetry_collection.dart';
import 'package:shelf/widgets/bookstore_widgets.dart';

Finder captureBookCard(int id) => find.byWidgetPredicate(
  (widget) => widget is BookPresentation && widget.book.id == id,
);

Future<PoetryCollection> openCaptureBook(WidgetTester tester, int id) async {
  FocusManager.instance.primaryFocus?.unfocus();
  await tester.pumpAndSettle();
  final scrollable = find.byType(Scrollable).first;
  final position = tester.state<ScrollableState>(scrollable).position;
  position.jumpTo(position.minScrollExtent);
  await tester.pumpAndSettle();
  final card = captureBookCard(id).first;
  await tester.scrollUntilVisible(
    card,
    220,
    scrollable: scrollable,
    maxScrolls: 40,
  );
  await Scrollable.ensureVisible(tester.element(card), alignment: .25);
  await tester.pumpAndSettle();
  final book = tester.widget<BookPresentation>(card).book;
  final title = find
      .descendant(
        of: card,
        matching: find.byKey(Key('collection-title-${book.slug}')),
      )
      .first;
  await Scrollable.ensureVisible(tester.element(title), alignment: .25);
  await tester.pumpAndSettle();
  expect(
    title.hitTestable(),
    findsOneWidget,
    reason:
        'Book $id Store title must actually be visible before capture navigation',
  );
  await tester.tap(title, warnIfMissed: true);
  await tester.pumpAndSettle();
  return book;
}
