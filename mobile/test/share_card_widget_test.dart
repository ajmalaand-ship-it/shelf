import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shelf/models/poem.dart';
import 'package:shelf/widgets/shelf_assets.dart';
import 'package:shelf/share_cards/poem_card_widget.dart';
import 'package:shelf/share_cards/share_card_models.dart';

import 'test_support.dart';

void main() {
  testWidgets('card is RTL branded and preserves special Pashto text', (
    tester,
  ) async {
    final poem = PoemDetail.fromJson(
      poemDetailJson(
        title: 'د تورو ازموينه',
        body: 'ټ ډ ړ ږ ښ ڼ ې ۍ\nزړۀ، مينهٔ، رؤيا',
      ),
    );
    final request = ShareCardRequest(
      poem: poem,
      collectionTitle: 'ازموينيزه ټولګه',
      scope: ShareCardScope.selection,
      text: poem.readableText,
      theme: ShareCardTheme.parchment,
    );
    await tester.pumpWidget(
      MaterialApp(
        home: Center(
          child: PoemCardWidget(
            request: request,
            page: ShareCardPage(
              text: poem.readableText,
              pageNumber: 1,
              pageCount: 1,
            ),
          ),
        ),
      ),
    );

    expect(find.text('اجمل اند'), findsOneWidget);
    expect(find.byType(ShelfLogo), findsOneWidget);
    expect(find.text('Shelf'), findsNothing);
    expect(find.text('د تورو ازموينه'), findsNothing);
    expect(find.textContaining('ټ ډ ړ ږ ښ ڼ ې ۍ'), findsOneWidget);
    expect(
      Directionality.of(
        tester.element(find.byKey(const Key('card-poem-text'))),
      ),
      TextDirection.rtl,
    );
    expect(tester.takeException(), isNull);
  });

  testWidgets('untitled card has no manufactured heading', (tester) async {
    final poem = PoemDetail.fromJson(
      poemDetailJson(title: null, body: 'لومړۍ کرښه\nدويمه کرښه'),
    );
    final request = ShareCardRequest(
      poem: poem,
      collectionTitle: null,
      scope: ShareCardScope.accessiblePoem,
      text: poem.readableText,
      theme: ShareCardTheme.light,
    );
    await tester.pumpWidget(
      MaterialApp(
        home: PoemCardWidget(
          request: request,
          page: ShareCardPage(
            text: poem.readableText,
            pageNumber: 1,
            pageCount: 1,
          ),
        ),
      ),
    );
    expect(find.text('بې سرليکه'), findsNothing);
    expect(find.text('لومړۍ کرښه'), findsNothing);
    expect(find.textContaining('لومړۍ کرښه'), findsOneWidget);
  });

  testWidgets('translation attribution remains truthful', (tester) async {
    final poem = PoemDetail.fromJson(
      poemDetailJson(
        title: 'ژمى',
        workType: 'TRANSLATION',
        body: 'ژباړل شوې کرښه',
      ),
    );
    final request = ShareCardRequest(
      poem: poem,
      collectionTitle: 'ژباړې',
      scope: ShareCardScope.selection,
      text: poem.readableText,
      theme: ShareCardTheme.dark,
    );
    await tester.pumpWidget(
      MaterialApp(
        home: PoemCardWidget(
          request: request,
          page: ShareCardPage(
            text: poem.readableText,
            pageNumber: 1,
            pageCount: 1,
          ),
        ),
      ),
    );
    expect(find.textContaining('اصلي شاعر: پروین پژواک'), findsOneWidget);
    expect(find.textContaining('پښتو ژباړه: اجمل اند'), findsOneWidget);
  });
}
