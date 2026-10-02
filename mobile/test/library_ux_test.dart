import 'dart:io';
import 'dart:typed_data';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/accounts/account_controller.dart';
import 'package:shelf/app.dart';
import 'package:shelf/purchases/library_controller.dart';
import 'package:shelf/purchases/library_screen.dart';
import 'package:shelf/purchases/book_purchase_provider.dart';
import 'package:shelf/screens/collection_detail_screen.dart';
import 'package:shelf/repository/poetry_repository.dart';
import 'package:shelf/services/api_client.dart';
import 'package:shelf/services/cache_store.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

import 'dart:convert';

import 'package:shelf/settings/interface_language.dart';
import 'package:shelf/settings/reader_settings.dart';

import 'accounts_test.dart' show FakeAccounts, FakeGoogle, MemoryTokens;
import 'book_purchases_test.dart'
    show FakeLibraryApi, FakeBookStore, MemoryDownloads, book;
import 'test_support.dart';

late Uint8List coverBytes;

class CoveredLibraryApi extends FakeLibraryApi {
  CoveredLibraryApi(super.clock);
  String? coverToken;
  @override
  Future<Map<String, dynamic>> request(
    String path, {
    String method = 'GET',
    String? token,
    Map<String, dynamic>? data,
  }) async {
    final response = await super.request(
      path,
      method: method,
      token: token,
      data: data,
    );
    if (response['books'] is List) {
      response['books'] = (response['books'] as List)
          .map(
            (b) => {
              ...b as Map<String, dynamic>,
              'cover_url': 'https://example.test/api/library/books/42/cover',
            },
          )
          .toList();
    }
    return response;
  }

  @override
  Future<Uint8List> media(String url, String token) async {
    expect(url, 'https://example.test/api/library/books/42/cover');
    coverToken = token;
    return coverBytes;
  }
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  setUpAll(() async {
    await (FontLoader('Vazirmatn')..addFont(
          rootBundle.load('assets/fonts/vazirmatn/Vazirmatn-wght.ttf'),
        ))
        .load();
    for (final font in ['Roboto', 'Ahem']) {
      await (FontLoader(font)..addFont(
            Future.value(
              ByteData.sublistView(
                await File(
                  '/home/shelf/flutter/bin/cache/artifacts/material_fonts/Roboto-Regular.ttf',
                ).readAsBytes(),
              ),
            ),
          ))
          .load();
    }
    await (FontLoader('MaterialIcons')..addFont(
          Future.value(
            ByteData.sublistView(
              await File(
                '/home/shelf/flutter/bin/cache/artifacts/material_fonts/MaterialIcons-Regular.otf',
              ).readAsBytes(),
            ),
          ),
        ))
        .load();
  });
  setUpAll(() async {
    final recorder = ui.PictureRecorder();
    final canvas = Canvas(recorder);
    canvas.drawRect(
      const Rect.fromLTWH(0, 0, 112, 156),
      Paint()..color = const Color(0xff245c4a),
    );
    canvas.drawRect(
      const Rect.fromLTWH(10, 10, 92, 136),
      Paint()
        ..color = const Color(0xffeadbc3)
        ..style = PaintingStyle.stroke
        ..strokeWidth = 2,
    );
    final text = TextPainter(
      text: const TextSpan(
        text: 'کتاب',
        style: TextStyle(
          fontFamily: 'Vazirmatn',
          fontSize: 26,
          color: Colors.white,
        ),
      ),
      textDirection: TextDirection.rtl,
    )..layout();
    text.paint(canvas, Offset((112 - text.width) / 2, 55));
    final picture = recorder.endRecording();
    final image = await picture.toImage(112, 156);
    coverBytes = (await image.toByteData(format: ui.ImageByteFormat.png))!
        .buffer
        .asUint8List();
    image.dispose();
    picture.dispose();
  });
  for (final language in InterfaceLanguage.values) {
    for (final size in [const Size(320, 568), const Size(430, 932)]) {
      testWidgets(
        'Library ${language.name} ${size.width}: cover, actions, refresh and purchase notification',
        (tester) async {
          tester.view.physicalSize = size;
          tester.view.devicePixelRatio = 1;
          addTearDown(tester.view.resetPhysicalSize);
          addTearDown(tester.view.resetDevicePixelRatio);
          SharedPreferences.setMockInitialValues({
            InterfaceLanguageSettings.preferenceKey: language.name,
          });
          final prefs = await SharedPreferences.getInstance();
          final settings = await ReaderSettings.load(prefs);
          final accounts = AccountController(
            service: FakeAccounts(),
            store: MemoryTokens(),
            google: FakeGoogle(),
          );
          await accounts.initialize();
          await accounts.signIn('synthetic@example.test', 'synthetic');
          final api = CoveredLibraryApi(() => DateTime.utc(2026, 10, 2))
            ..owned = true;
          final library = LibraryController(
            accounts: accounts,
            service: api,
            provider: FakeBookStore(),
            downloads: MemoryDownloads(),
          );
          var disposed = false;
          addTearDown(() {
            if (!disposed) {
              library.dispose();
              accounts.dispose();
            }
          });
          await library.product(book);
          final boundary = GlobalKey();
          await tester.pumpWidget(
            RepaintBoundary(
              key: boundary,
              child: ShelfApp(
                repository: fixtureRepository(includeCover: false),
                readerSettings: settings,
                languageSettings: InterfaceLanguageSettings.load(prefs),
                accountController: accounts,
                libraryController: library,
              ),
            ),
          );
          await tester.pumpAndSettle();
          await tester.tap(find.byIcon(Icons.local_library_outlined).last);
          await tester.pumpAndSettle();
          expect(find.byKey(const Key('store-account')), findsOneWidget);
          expect(find.byIcon(Icons.person_outline), findsNothing);
          expect(find.byType(OwnedBookCover), findsOneWidget);
          expect(api.coverToken, accounts.readerToken);
          expect(
            find.descendant(
              of: find.byType(OwnedBookCover),
              matching: find.byType(Image),
            ),
            findsOneWidget,
          );
          expect(find.byKey(const Key('library-refresh')), findsOneWidget);
          expect(find.text('Check Library'), findsNothing);
          final read = find.text(
            language == InterfaceLanguage.en ? 'Read book' : 'کتاب ولولئ',
          );
          await tester.ensureVisible(read);
          await tester.pumpAndSettle();
          expect(
            tester
                .getSize(
                  find.ancestor(of: read, matching: find.byType(FilledButton)),
                )
                .height,
            greaterThanOrEqualTo(48),
          );
          expect(
            tester.widget<Text>(find.text(book.title)).textDirection,
            TextDirection.rtl,
          );
          Future<void> capture(String state) async {
            expect(tester.takeException(), isNull);
            final directory = Platform.environment['SHELF_LIBRARY_SCREENSHOTS'];
            if (directory != null) {
              await tester.runAsync(() async {
                final render =
                    boundary.currentContext!.findRenderObject()!
                        as RenderRepaintBoundary;
                final image = await render.toImage();
                final bytes = await image.toByteData(
                  format: ui.ImageByteFormat.png,
                );
                await Directory(directory).create(recursive: true);
                await File(
                  '$directory/${language.name}-${size.width.toInt()}-$state.png',
                ).writeAsBytes(bytes!.buffer.asUint8List());
                image.dispose();
              });
            }
          }

          await capture('owned');
          library.downloaded.add(book.id!);
          await library.refresh();
          await tester.pumpAndSettle();
          expect(find.text('Download for offline reading'), findsNothing);
          expect(
            find.text('له انټرنېټ پرته لوستلو لپاره کښته کړئ'),
            findsNothing,
          );
          expect(
            find.text(
              language == InterfaceLanguage.en ? 'Downloaded' : 'کښته شوی',
            ),
            findsOneWidget,
          );
          expect(find.byKey(const Key('library-downloaded')), findsOneWidget);
          final remove = find.text(
            language == InterfaceLanguage.en
                ? 'Remove download'
                : 'کښته شوې کاپي لرې کړئ',
          );
          await tester.ensureVisible(remove);
          await tester.pumpAndSettle();
          await capture('downloaded');
          await tester.tap(remove);
          await tester.pumpAndSettle();
          expect(library.downloaded, isEmpty);
          await tester.drag(
            find.byType(PurchasedLibraryScreen),
            const Offset(0, 600),
          );
          await tester.pumpAndSettle();
          final before = api.calls.length;
          await tester.tap(find.byKey(const Key('library-refresh')));
          await tester.pumpAndSettle();
          expect(api.calls.length, greaterThan(before));
          api.offline = true;
          await tester.tap(find.byKey(const Key('library-refresh')));
          await tester.pumpAndSettle();
          expect(library.refreshFailed, true);
          await capture('refresh-error');
          await tester.pumpWidget(const SizedBox.shrink());
          library.dispose();
          accounts.dispose();
          disposed = true;
        },
      );
    }
  }
  testWidgets(
    'book page switches from Buy book immediately on server confirmation',
    (tester) async {
      SharedPreferences.setMockInitialValues({
        InterfaceLanguageSettings.preferenceKey: 'en',
      });
      final prefs = await SharedPreferences.getInstance();
      final accounts = AccountController(
        service: FakeAccounts(),
        store: MemoryTokens(),
        google: FakeGoogle(),
      );
      await accounts.initialize();
      await accounts.signIn('synthetic@example.test', 'synthetic');
      final api = FakeLibraryApi(() => DateTime.utc(2026, 10, 2));
      final library = LibraryController(
        accounts: accounts,
        service: api,
        provider: FakeBookStore(),
        downloads: MemoryDownloads(),
      );
      await library.product(book);
      final repository = PoetryRepository(
        api: ApiClient(
          baseUri: Uri.parse('https://example.test/api/'),
          client: MockClient(
            (r) async => http.Response(
              jsonEncode({
                'data': r.url.path.endsWith('/poems') ? [] : book.toJson(),
              }),
              200,
              headers: {'content-type': 'application/json; charset=utf-8'},
            ),
          ),
        ),
        cache: MemoryCacheStore(),
      );
      await tester.pumpWidget(
        InterfaceLanguageScope(
          settings: InterfaceLanguageSettings.load(prefs),
          child: AccountScope(
            controller: accounts,
            child: LibraryScope(
              controller: library,
              child: MaterialApp(
                home: CollectionDetailScreen(
                  slug: book.slug,
                  contentVersion: 0,
                  repository: repository,
                  readerSettings: await ReaderSettings.load(prefs),
                ),
              ),
            ),
          ),
        ),
      );
      await tester.pumpAndSettle();
      expect(find.text('Buy book'), findsOneWidget);
      api.owned = true;
      await library.purchase(
        book,
        const StoreBookProduct('shelf_book_42', '€2.79'),
        true,
      );
      await tester.pump();
      expect(find.text('Buy book'), findsNothing);
      expect(find.text('Open owned book'), findsOneWidget);
      expect(tester.takeException(), isNull);
      await tester.pumpWidget(const SizedBox.shrink());
      library.dispose();
      accounts.dispose();
    },
  );
}
