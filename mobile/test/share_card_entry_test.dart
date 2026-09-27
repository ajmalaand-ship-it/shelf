import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shelf/screens/poem_reader_screen.dart';
import 'package:shelf/settings/reader_settings.dart';
import 'package:shelf/share_cards/share_card_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'test_support.dart';

void main() {
  testWidgets('reader exposes a secondary share entry after poem load', (
    tester,
  ) async {
    SharedPreferences.setMockInitialValues({});
    final settings = ReaderSettings.load(await SharedPreferences.getInstance());
    await tester.pumpWidget(
      MaterialApp(
        home: PoemReaderScreen(
          poemId: 301,
          contentVersion: 7,
          repository: fixtureRepository(),
          settings: await settings,
          collectionTitle: 'هېندارې او چینې',
        ),
      ),
    );
    await tester.pumpAndSettle();
    await tester.tap(find.byKey(const Key('reader-share')));
    await tester.pumpAndSettle();
    expect(find.byType(ShareCardScreen), findsOneWidget);
    expect(find.text('هېندارې او چینې'), findsOneWidget);
  });
}
