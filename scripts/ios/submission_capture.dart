// Capture harness only: real app, real catalogue, no fake responses or purchases.
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:shelf/shelf_capture_main.dart' as app;
import 'package:shelf/screens/collection_detail_screen.dart';
import 'package:shelf/widgets/bookstore_widgets.dart';

import 'capture_navigation.dart';

void main() {
  final binding = IntegrationTestWidgetsFlutterBinding.ensureInitialized();
  testWidgets('Capture real Shelf submission screens', (tester) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('shelf.interface_language', 'en');
    await app.main();
    Future<void> waitFor(Finder target, String stage) async {
      for (var i = 0; i < 180; i++) {
        await tester.pump(const Duration(milliseconds: 500));
        if (target.evaluate().isNotEmpty) return;
      }
      throw StateError(
        'Capture stage $stage did not become available; '
        'view=${tester.view.physicalSize}, dpr=${tester.view.devicePixelRatio}, '
        'keyboard=${tester.view.viewInsets.bottom}',
      );
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

    // The original run reached Search, then timed out waiting for a keyed Text.
    // Capture does not need search: use already-loaded normal Store cards.
    await waitFor(find.byType(BookPresentation), 'store-ready');
    await capture('listing-store');
    for (final id in [3, 4, 5, 6, 7, 8]) {
      debugPrint('CAPTURE_STAGE open-store-book-$id');
      final book = await openCaptureBook(tester, id);
      await waitFor(
        find.byKey(const Key('collection-detail-title')),
        'book-$id-details',
      );
      await waitFor(
        find.byKey(const Key('read-sample')),
        'book-$id-sample-button',
      );
      expect(
        tester
            .widget<CollectionDetailScreen>(find.byType(CollectionDetailScreen))
            .slug,
        book.slug,
      );
      expect(find.text(book.title), findsWidgets);
      await capture('iap-book-$id');
      if (id == 3) {
        await tester.tap(find.byKey(const Key('read-sample')));
        await tester.pumpAndSettle(
          const Duration(milliseconds: 100),
          EnginePhase.sendSemanticsUpdate,
          const Duration(seconds: 30),
        );
        await capture('listing-sample');
        await tester.pageBack();
        await waitFor(
          find.byKey(const Key('collection-detail-title')),
          'sample-back',
        );
      }
      await tester.pageBack();
      await waitFor(find.byType(BookPresentation), 'store-back-$id');
    }
    // Existing runner secrets are optional; public listing/review captures never
    // require exposing a reader credential or changing server access.
    const email = String.fromEnvironment('SHELF_CAPTURE_REVIEW_EMAIL');
    const password = String.fromEnvironment('SHELF_CAPTURE_REVIEW_PASSWORD');
    if (email.isNotEmpty && password.isNotEmpty) {
      await tester.tap(find.byKey(const Key('store-account')));
      await waitFor(find.byKey(const Key('account-email')), 'reviewer-sign-in');
      await tester.enterText(find.byKey(const Key('account-email')), email);
      await tester.enterText(
        find.byKey(const Key('account-password')),
        password,
      );
      FocusManager.instance.primaryFocus?.unfocus();
      await tester.pumpAndSettle();
      await tester.tap(find.byKey(const Key('account-submit')));
      await waitFor(
        find.byKey(const Key('account-sign-out')),
        'reviewer-signed-in',
      );
      await tester.pageBack();
      await tester.tap(find.byType(NavigationDestination).at(2));
      await capture('listing-library');
    }
  }, timeout: const Timeout(Duration(minutes: 12)));
}
