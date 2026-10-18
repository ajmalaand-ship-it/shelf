// Capture harness only: real app, real catalogue, no fake responses or purchases.
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:shelf/shelf_capture_main.dart' as app;

void main() {
  final binding = IntegrationTestWidgetsFlutterBinding.ensureInitialized();
  testWidgets('Capture real Shelf submission screens', (tester) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('shelf.interface_language', 'en');
    await app.main();
    Future<void> waitFor(Finder target) async {
      for (var i = 0; i < 180; i++) {
        await tester.pump(const Duration(milliseconds: 500));
        if (target.evaluate().isNotEmpty) return;
      }
      throw StateError('Real app screen did not become available');
    }

    Future<void> capture(String name) async {
      await tester.pumpAndSettle(
        const Duration(milliseconds: 100),
        EnginePhase.sendSemanticsUpdate,
        const Duration(seconds: 30),
      );
      await tester.pump(const Duration(seconds: 3));
      await binding.takeScreenshot(name);
    }

    final books = <int, String>{
      3: 'څپو کې انځورونه',
      4: 'هېندارې او چینې',
      5: 'د زړه پر پاڼه مې انځور دی ګلاب',
      6: 'سيند په پرخه کې',
      7: 'دا ښار، هاغه غرونه',
      8: 'دلته ډېر لرې له غرونو',
    };
    await waitFor(
      find.byWidgetPredicate(
        (w) =>
            w.key is ValueKey<String> &&
            (w.key! as ValueKey<String>).value.startsWith('collection-title-'),
      ),
    );
    await capture('listing-store');
    await tester.tap(find.byType(NavigationDestination).at(1));
    await waitFor(find.byKey(const Key('book-search')));
    for (final book in books.entries) {
      await tester.enterText(find.byKey(const Key('book-search')), book.value);
      final result = find.byWidgetPredicate(
        (w) =>
            w is Text &&
            w.data == book.value &&
            w.key is ValueKey<String> &&
            (w.key! as ValueKey<String>).value.startsWith('collection-title-'),
      );
      await waitFor(result);
      FocusManager.instance.primaryFocus?.unfocus();
      await tester.pumpAndSettle();
      await tester.ensureVisible(result.first);
      await tester.tap(result.first);
      await waitFor(find.byKey(const Key('collection-detail-title')));
      await waitFor(find.byKey(const Key('read-sample')));
      expect(find.text(book.value), findsWidgets);
      await capture('iap-book-${book.key}');
      if (book.key == 3) {
        await tester.tap(find.byKey(const Key('read-sample')));
        await tester.pumpAndSettle(
          const Duration(milliseconds: 100),
          EnginePhase.sendSemanticsUpdate,
          const Duration(seconds: 30),
        );
        await capture('listing-sample');
        await tester.pageBack();
        await waitFor(find.byKey(const Key('collection-detail-title')));
      }
      await tester.pageBack();
      await waitFor(find.byKey(const Key('book-search')));
    }
    // Existing runner secrets are optional; public listing/review captures never
    // require exposing a reader credential or changing server access.
    const email = String.fromEnvironment('SHELF_CAPTURE_REVIEW_EMAIL');
    const password = String.fromEnvironment('SHELF_CAPTURE_REVIEW_PASSWORD');
    if (email.isNotEmpty && password.isNotEmpty) {
      await tester.tap(find.byKey(const Key('store-account')));
      await waitFor(find.byKey(const Key('account-email')));
      await tester.enterText(find.byKey(const Key('account-email')), email);
      await tester.enterText(
        find.byKey(const Key('account-password')),
        password,
      );
      FocusManager.instance.primaryFocus?.unfocus();
      await tester.pumpAndSettle();
      await tester.tap(find.byKey(const Key('account-submit')));
      await waitFor(find.byKey(const Key('account-sign-out')));
      await tester.pageBack();
      await tester.tap(find.byType(NavigationDestination).at(2));
      await capture('listing-library');
    }
  }, timeout: const Timeout(Duration(minutes: 12)));
}
