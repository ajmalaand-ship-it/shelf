import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/accounts/account_controller.dart';
import 'package:shelf/models/poem.dart';
import 'package:shelf/models/poetry_collection.dart';
import 'package:shelf/reading/paged_text_view.dart';
import 'package:shelf/reading/reading_position.dart';
import 'package:shelf/repository/poetry_repository.dart';
import 'package:shelf/screens/poem_reader_screen.dart';
import 'package:shelf/screens/collection_detail_screen.dart';
import 'package:shelf/settings/reader_settings.dart';
import 'package:shelf/services/api_config.dart';
import 'package:shelf/purchases/library_controller.dart';
import 'package:shelf/purchases/owned_book_repository.dart';
import 'package:shelf/accounts/account_service.dart';

import 'book_purchases_test.dart'
    show FakeLibraryApi, FakeBookStore, MemoryDownloads;

import 'accounts_test.dart' show FakeAccounts, FakeGoogle, MemoryTokens;
import 'test_support.dart';

class RecordingRepository implements PoetryDataSource {
  final calls = <int>[];
  final refreshes = <bool>[];
  final data = <int, PoemDetail>{};
  Completer<PoemDetail>? pending;
  @override
  Future<PoemDetail> loadPoem(
    int id,
    int contentVersion, {
    bool refreshEntitlement = false,
  }) async {
    calls.add(id);
    refreshes.add(refreshEntitlement);
    if (pending != null) return pending!.future;
    return data[id]!;
  }

  @override
  Future<AudioAccess> loadAudio(int id) => throw UnimplementedError();
  @override
  Future<CollectionBundle> loadCollection(String slug, int version) async =>
      CollectionBundle(
        collection: book('ps'),
        poems: data.keys.map(summary).toList(),
      );
  @override
  Future<CatalogueSnapshot?> loadCachedCatalogue() =>
      throw UnimplementedError();
  @override
  Future<CatalogueSnapshot> refreshCatalogue(CatalogueSnapshot? cached) =>
      throw UnimplementedError();
}

PoemSummary summary(int id, {bool locked = false}) => PoemSummary.fromJson({
  ...poemSummaryJson,
  'id': id,
  'locked': locked,
  'is_free_sample': !locked,
});
PoemDetail detail(
  int id,
  String text, {
  bool partial = false,
  bool locked = false,
  bool free = true,
}) => PoemDetail.fromJson({
  ...poemDetailJson(title: 'Section $id', body: text, locked: locked),
  'id': id,
  'is_free_sample': free,
  'has_more': partial,
  'requires_entitlement': partial || locked,
});
PoetryCollection book(String language) => PoetryCollection.fromJson({
  ...collectionJson,
  'id': 42,
  'language': language,
  'cover_url': null,
});

void main() {
  setUpAll(() async {
    for (final font in {
      'Vazirmatn': 'assets/fonts/vazirmatn/Vazirmatn-wght.ttf',
      'ScheherazadeNew':
          'assets/fonts/scheherazade_new/ScheherazadeNew-Regular.ttf',
      'NotoNastaliqUrdu':
          'assets/fonts/noto_nastaliq_urdu/NotoNastaliqUrdu.ttf',
    }.entries) {
      await (FontLoader(font.key)..addFont(rootBundle.load(font.value))).load();
    }
  });
  Future<ReaderSettings> settings() async {
    SharedPreferences.setMockInitialValues({});
    final s = await ReaderSettings.load(await SharedPreferences.getInstance());
    await s.setReadingMode(ReadingMode.pages);
    return s;
  }

  Future<void> mount(
    WidgetTester tester,
    ReaderSettings s,
    RecordingRepository r, {
    String language = 'ps',
    int item = 301,
    bool resume = false,
    List<PoemSummary>? contents,
    AccountController? accounts,
  }) async {
    await tester.pumpWidget(
      MaterialApp(
        home: AccountScope(
          controller: accounts,
          child: PoemReaderScreen(
            key: ValueKey(item),
            poemId: item,
            contentVersion: 7,
            repository: r,
            settings: s,
            collection: book(language),
            contents: contents ?? [summary(301), summary(302)],
            resume: resume,
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();
  }

  Future<void> next(WidgetTester t) async {
    await t.tap(find.byKey(const Key('page-next')));
    await t.pumpAndSettle();
  }

  Future<void> previous(WidgetTester t) async {
    await t.tap(find.byKey(const Key('page-previous')));
    await t.pumpAndSettle();
  }

  for (final language in ['ps', 'en']) {
    testWidgets(
      '$language controls and swipes follow book direction across ordered sections',
      (t) async {
        final s = await settings(),
            r = RecordingRepository()
              ..data.addAll({
                301: detail(301, 'First original\n\n'),
                302: detail(302, 'Second original\n'),
              });
        await mount(t, s, r, language: language);
        expect(find.byKey(const Key('reader-share')), findsOneWidget);
        expect(find.byKey(const Key('poem-title')), findsOneWidget);
        await next(t);
        final view = t.widget<PagedTextView>(find.byType(PagedTextView));
        expect(
          view.direction,
          language == 'ps' ? TextDirection.rtl : TextDirection.ltr,
        );
        await t.drag(
          find.byKey(const Key('book-pages')),
          Offset(language == 'ps' ? 420 : -420, 0),
        );
        await t.pumpAndSettle();
        expect(r.calls, [301, 302]);
        expect(r.refreshes, [false, true]);
        await previous(t);
        expect(r.calls, [301, 302, 301]);
        expect(
          t.widget<PagedTextView>(find.byType(PagedTextView)).initialDetails,
          false,
        );
        await previous(t);
        expect(find.byKey(const Key('poem-title')), findsOneWidget);
        expect(t.takeException(), null);
      },
    );
  }
  testWidgets(
    'partial sample ends exactly; locked adjacent item is never fetched',
    (t) async {
      final s = await settings(),
          r = RecordingRepository()
            ..data[301] = detail(301, 'Approved sample\n\n', partial: true);
      await mount(
        t,
        s,
        r,
        contents: [summary(301), summary(302, locked: true)],
      );
      expect(find.byKey(const Key('reading-boundary')), findsOneWidget);
      final view = t.widget<PagedTextView>(find.byType(PagedTextView));
      expect(
        view.pages.map((p) => p.text(view.source)).join(),
        'Approved sample\n\n',
      );
      await next(t);
      expect(
        t.widget<IconButton>(find.byKey(const Key('page-next'))).onPressed,
        null,
      );
      await t.drag(find.byKey(const Key('book-pages')), const Offset(500, 0));
      await t.pumpAndSettle();
      expect(r.calls, [301]);
      expect(t.takeException(), null);
    },
  );
  testWidgets(
    'locked initial Contents jump and unexpected paid response fail closed',
    (t) async {
      final s = await settings(),
          r = RecordingRepository()
            ..data[302] = detail(302, 'Must not display', free: false);
      await mount(
        t,
        s,
        r,
        item: 302,
        contents: [summary(301), summary(302, locked: true)],
      );
      expect(r.calls, isEmpty);
      expect(find.byType(PagedTextView), findsNothing);
      await t.pumpWidget(const SizedBox());
      await mount(t, s, r, item: 302);
      expect(r.calls, [302]);
      expect(find.textContaining('Must not display'), findsNothing);
      expect(find.byType(PagedTextView), findsNothing);
    },
  );
  testWidgets(
    'reflow and reopen retain a text location; explicit Contents jump starts its selected item',
    (t) async {
      t.view.physicalSize = const Size(390, 700);
      t.view.devicePixelRatio = 1;
      addTearDown(t.view.resetPhysicalSize);
      addTearDown(t.view.resetDevicePixelRatio);
      final s = await settings(),
          r = RecordingRepository()
            ..data.addAll({
              301: detail(
                301,
                List.generate(40, (i) => 'Original line $i\n\n').join(),
              ),
              302: detail(302, 'Contents jump'),
            });
      await mount(t, s, r);
      await next(t);
      await next(t);
      await next(t);
      final before = t.widget<PagedTextView>(find.byType(PagedTextView));
      final page = t
          .widget<PageView>(find.byKey(const Key('book-pages')))
          .controller!
          .page!
          .round();
      final anchor = before.pages[page - 1].start;
      expect(anchor, greaterThan(0));
      await s.setFont(ReaderFont.naskh);
      await s.setFontSize(38);
      t.view.physicalSize = const Size(320, 520);
      await t.pumpAndSettle();
      var view = t.widget<PagedTextView>(find.byType(PagedTextView));
      expect(view.initialOffset, anchor);
      var idx = t
          .widget<PageView>(find.byKey(const Key('book-pages')))
          .controller!
          .page!
          .round();
      expect(view.pages[idx - 1].start, lessThanOrEqualTo(anchor));
      expect(view.pages[idx - 1].end, greaterThan(anchor));
      await t.pumpWidget(const SizedBox());
      await t.pumpAndSettle();
      await mount(t, s, r, resume: true);
      expect(
        t.widget<PagedTextView>(find.byType(PagedTextView)).initialOffset,
        anchor,
      );
      await t.pumpWidget(const SizedBox());
      await mount(t, s, r, item: 302);
      expect(
        t.widget<PagedTextView>(find.byType(PagedTextView)).initialDetails,
        true,
      );
      expect(r.calls.last, 302);
      expect(find.byKey(const Key('reader-contents')), findsOneWidget);
      await s.setReadingMode(ReadingMode.scroll);
      await t.pumpAndSettle();
      expect(find.byKey(const Key('poem-body')), findsOneWidget);
      await s.setReadingMode(ReadingMode.pages);
      await t.pumpAndSettle();
      expect(find.byType(PagedTextView), findsOneWidget);
      expect(t.takeException(), null);
    },
  );
  testWidgets(
    'Contents returns to the real list and jumps directly; Read resumes the last section',
    (t) async {
      final s = await settings(),
          r = RecordingRepository()
            ..data.addAll({
              301: detail(301, 'First'),
              302: detail(302, 'Second'),
            });
      await t.pumpWidget(
        MaterialApp(
          home: CollectionDetailScreen(
            slug: book('ps').slug,
            contentVersion: 7,
            repository: r,
            readerSettings: s,
          ),
        ),
      );
      await t.pumpAndSettle();
      await t.tap(find.byKey(const Key('read-sample')));
      await t.pumpAndSettle();
      await next(t);
      await next(t);
      await next(t);
      await t.tap(find.byKey(const Key('reader-contents')));
      await t.pumpAndSettle();
      expect(find.byType(CollectionDetailScreen), findsOneWidget);
      await t.scrollUntilVisible(
        find.byKey(const Key('poem-list-title-301')),
        200,
      );
      await t.tap(find.byKey(const Key('poem-list-title-301')));
      await t.pumpAndSettle();
      expect(r.calls.last, 301);
      expect(
        t.widget<PagedTextView>(find.byType(PagedTextView)).initialDetails,
        true,
      );
      await next(t);
      await next(t);
      await next(t);
      await t.tap(find.byKey(const Key('reader-contents')));
      await t.pumpAndSettle();
      await t.tap(find.byKey(const Key('read-sample')));
      await t.pumpAndSettle();
      expect(r.calls.last, 302);
      expect(
        t.widget<PagedTextView>(find.byType(PagedTextView)).initialDetails,
        false,
      );
      expect(t.takeException(), null);
    },
  );
  testWidgets(
    'Pages is optional through preferences and keeps the preview pinned',
    (t) async {
      final s = await settings();
      await s.setReadingMode(ReadingMode.scroll);
      final r = RecordingRepository()..data[301] = detail(301, 'Original text');
      await mount(t, s, r);
      expect(find.byKey(const Key('poem-body')), findsOneWidget);
      await t.tap(find.byKey(const Key('reader-font-chooser')));
      await t.pumpAndSettle();
      final previewTop = t.getTopLeft(
        find.byKey(const Key('reading-live-preview')),
      );
      await t.ensureVisible(find.byKey(const Key('reading-mode-pages')));
      await t.tap(find.byKey(const Key('reading-mode-pages')));
      await t.pumpAndSettle();
      expect(s.readingMode, ReadingMode.pages);
      expect(
        t.getTopLeft(find.byKey(const Key('reading-live-preview'))),
        previewTop,
      );
      await t.tap(find.byKey(const Key('reading-preferences-close')));
      await t.pumpAndSettle();
      expect(find.byType(PagedTextView), findsOneWidget);
      expect(t.takeException(), null);
    },
  );
  testWidgets(
    'changed source restores to item start instead of using a stale offset',
    (t) async {
      final s = await settings(),
          r = RecordingRepository()
            ..data[301] = detail(301, 'New exact source');
      final key = s.positions.key(
        environment: '${apiBaseUri()}',
        account: 'guest',
        book: '42',
      );
      await s.positions.save(
        key,
        ReadingPosition(
          item: 301,
          offset: 4,
          fingerprint: ReadingPosition.fingerprintOf('Old text'),
        ),
      );
      await mount(t, s, r, resume: true);
      final view = t.widget<PagedTextView>(find.byType(PagedTextView));
      expect(view.initialOffset, 0);
      expect(view.initialDetails, false);
      expect(find.byKey(const Key('reading-position-notice')), findsOneWidget);
    },
  );
  testWidgets(
    'owned pages reuse isolated offline copy and stop on expiry or revocation',
    (t) async {
      var now = DateTime.utc(2026, 10, 9);
      final accounts = AccountController(
        service: FakeAccounts(),
        store: MemoryTokens(),
        google: FakeGoogle(),
      );
      await accounts.initialize();
      await accounts.signIn('synthetic@example.test', 'synthetic');
      final api = FakeLibraryApi(() => now)..owned = true;
      final downloads = MemoryDownloads();
      final library = LibraryController(
        accounts: accounts,
        service: api,
        provider: FakeBookStore(),
        downloads: downloads,
        clock: () => now,
      );
      await library.refresh();
      final b = book('ps');
      downloads.copies['1/42'] = {
        'collection': b.toJson(),
        'summaries': [summary(301).toJson(), summary(302).toJson()],
        'poems': [
          {
            ...poemDetailJson(body: 'Owned original 301'),
            'is_free_sample': false,
          },
          {
            ...poemDetailJson(body: 'Owned original 302'),
            'id': 302,
            'is_free_sample': false,
          },
        ],
      };
      library.downloaded.add(42);
      api.offline = true;
      final repo = OwnedBookRepository(library, b), s = await settings();
      Future<void> open() async {
        await t.pumpWidget(
          MaterialApp(
            home: AccountScope(
              controller: accounts,
              child: LibraryScope(
                controller: library,
                child: PoemReaderScreen(
                  poemId: 301,
                  contentVersion: 7,
                  repository: repo,
                  settings: s,
                  collection: b,
                  contents: [summary(301), summary(302)],
                  ownedMode: true,
                ),
              ),
            ),
          ),
        );
        await t.pumpAndSettle();
      }

      await open();
      await next(t);
      await next(t);
      await next(t);
      expect(
        t.widget<PagedTextView>(find.byType(PagedTextView)).source,
        'Owned original 302',
      );
      now = now.add(const Duration(days: 31));
      await previous(t);
      expect(find.byKey(const Key('reader-access-changed')), findsOneWidget);
      expect(find.byType(PagedTextView), findsNothing);
      await expectLater(repo.loadPoem(301, 7), throwsA(isA<AccountFailure>()));
      await t.pumpWidget(const SizedBox());
      now = DateTime.utc(2026, 10, 9);
      api.offline = false;
      await library.refresh();
      // A valid offline read records the observed clock. Rolling it back
      // must stop page turns as well as fail the repository check.
      library.downloaded.add(42);
      await open();
      api.offline = true;
      now = now.add(const Duration(days: 1));
      await repo.loadPoem(301, 7);
      now = now.subtract(const Duration(days: 2));
      await next(t);
      expect(find.byType(PagedTextView), findsNothing);
      await expectLater(repo.loadPoem(301, 7), throwsA(isA<AccountFailure>()));
      await t.pumpWidget(const SizedBox());
      now = DateTime.utc(2026, 10, 9);
      api.offline = false;
      await library.refresh();
      library.downloaded.add(42);
      await open();
      api.owned = false;
      await library.refresh();
      await t.pumpAndSettle();
      expect(find.byType(PagedTextView), findsNothing);
      expect(find.byKey(const Key('reader-access-changed')), findsOneWidget);
      await t.pumpWidget(const SizedBox());
      library.dispose();
      accounts.dispose();
      expect(t.takeException(), null);
    },
  );
  testWidgets(
    'large text and oversized pages remain scrollable without vertical chapter turns',
    (t) async {
      t.view.physicalSize = const Size(320, 360);
      t.view.devicePixelRatio = 1;
      addTearDown(t.view.resetPhysicalSize);
      addTearDown(t.view.resetDevicePixelRatio);
      final s = await settings();
      await s.setFontSize(38);
      final r = RecordingRepository()
        ..data.addAll({
          301: detail(301, 'One long original line ' * 80),
          302: detail(302, 'Next'),
        });
      await t.pumpWidget(
        MaterialApp(
          home: MediaQuery(
            data: const MediaQueryData(
              size: Size(320, 360),
              textScaler: TextScaler.linear(3),
            ),
            child: PoemReaderScreen(
              poemId: 301,
              contentVersion: 7,
              repository: r,
              settings: s,
              collection: book('ps'),
              contents: [summary(301), summary(302)],
            ),
          ),
        ),
      );
      await t.pumpAndSettle();
      await next(t);
      final view = t.widget<PagedTextView>(find.byType(PagedTextView));
      expect(view.pages.first.oversized, true);
      await t.drag(find.byKey(const Key('text-page-0')), const Offset(0, -500));
      await t.pumpAndSettle();
      await t.pump(const Duration(milliseconds: 300));
      expect(r.calls, [301]);
      final saved = s.positions.read(
        s.positions.key(
          environment: '${apiBaseUri()}',
          account: 'guest',
          book: '42',
        ),
      );
      expect(saved!.offset, greaterThan(0));
      expect(t.takeException(), null);
      t.view.physicalSize = const Size(568, 240);
      await t.pumpAndSettle();
      expect(find.byKey(const Key('page-next')), findsOneWidget);
      expect(t.takeException(), null);
    },
  );
  testWidgets(
    'sign-out drops resident pages and rejects an in-flight section result',
    (t) async {
      final api = FakeAccounts(),
          accounts = AccountController(
            service: api,
            store: MemoryTokens(),
            google: FakeGoogle(),
          );
      await accounts.initialize();
      await accounts.signIn('synthetic@example.test', 'synthetic');
      final s = await settings(),
          r = RecordingRepository()..data[301] = detail(301, 'Account sample');
      await mount(t, s, r, accounts: accounts);
      await next(t);
      r.pending = Completer<PoemDetail>();
      await t.tap(find.byKey(const Key('page-next')));
      await t.pump();
      await accounts.signOut();
      await t.pump();
      r.pending!.complete(detail(302, 'Stale result'));
      await t.pumpAndSettle();
      expect(find.byType(PagedTextView), findsNothing);
      expect(find.textContaining('Stale result'), findsNothing);
      expect(find.byKey(const Key('reader-access-changed')), findsOneWidget);
      expect(t.takeException(), null);
      await t.pumpWidget(const SizedBox());
      accounts.dispose();
    },
  );
}
