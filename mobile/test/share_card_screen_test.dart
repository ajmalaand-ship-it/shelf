import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:pitswal/models/poem.dart';
import 'package:pitswal/share_cards/poem_card_widget.dart';
import 'package:pitswal/share_cards/share_card_files.dart';
import 'package:pitswal/share_cards/share_card_models.dart';
import 'package:pitswal/share_cards/share_card_output.dart';
import 'package:pitswal/share_cards/share_card_renderer.dart';
import 'package:pitswal/share_cards/share_card_screen.dart';

import 'test_support.dart';

void main() {
  late Directory directory;

  setUp(() async {
    directory = await Directory.systemTemp.createTemp('pitswal-card-screen-');
  });

  tearDown(() async {
    if (await directory.exists()) await directory.delete(recursive: true);
  });

  testWidgets('selection preview switches through all three themes', (
    tester,
  ) async {
    final poem = PoemDetail.fromJson(
      poemDetailJson(body: 'لومړۍ\nدويمه\nدرېيمه\nڅلورمه\nپنځمه'),
    );
    await tester.pumpWidget(
      MaterialApp(
        home: ShareCardScreen(
          poem: poem,
          output: _FakeOutput(),
          renderer: _FakeRenderer(),
          files: _FakeFiles(directory),
        ),
      ),
    );

    expect(find.byKey(const Key('card-preview')), findsOneWidget);
    await tester.tap(find.byKey(const Key('share-line-1')));
    await tester.pump();
    expect(find.text('2/۴'), findsOneWidget);

    await tester.tap(find.text('تياره'));
    await tester.pump();
    await tester.tap(find.text('روښانه'));
    await tester.pump();
    expect(find.byType(PoemCardWidget), findsOneWidget);
    expect(tester.takeException(), isNull);

    await _scrollToShare(tester);
    final shareButton = tester.widget<FilledButton>(
      find.byKey(const Key('share-cards')),
    );
    shareButton.onPressed!();
    await tester.pump();
    await tester.runAsync(
      () => Future<void>.delayed(const Duration(milliseconds: 100)),
    );
    await tester.pump();
    final renderer =
        tester.widget<ShareCardScreen>(find.byType(ShareCardScreen)).renderer
            as _FakeRenderer;
    expect(renderer.preparedPaintedPage, isTrue);
  });

  testWidgets('locked poem shares only its visible excerpt', (tester) async {
    final poem = PoemDetail.fromJson(poemDetailJson(locked: true));
    final output = _FakeOutput();
    final renderer = _FakeRenderer();
    await tester.pumpWidget(
      MaterialApp(
        home: ShareCardScreen(
          poem: poem,
          output: output,
          renderer: renderer,
          files: _FakeFiles(directory),
        ),
      ),
    );

    expect(find.text('شته لنډه برخه'), findsOneWidget);
    await tester.tap(find.text('شته لنډه برخه'));
    await tester.pump();
    await _scrollToShare(tester);
    final shareButton = tester.widget<FilledButton>(
      find.byKey(const Key('share-cards')),
    );
    expect(shareButton.onPressed, isNotNull);
    shareButton.onPressed!();
    await tester.pump();
    await tester.runAsync(
      () => Future<void>.delayed(const Duration(milliseconds: 100)),
    );
    await tester.pump();

    expect(renderer.lastRequest?.text, poem.excerpt);
    expect(renderer.lastRequest?.text, isNot(contains('پټ بشپړ متن')));
    expect(output.sharedCount, 1);
  });

  testWidgets('export errors are recoverable and do not crash', (tester) async {
    final poem = PoemDetail.fromJson(poemDetailJson());
    await tester.pumpWidget(
      MaterialApp(
        home: ShareCardScreen(
          poem: poem,
          output: _FakeOutput(),
          renderer: const _FailingRenderer(),
          files: _FakeFiles(directory),
        ),
      ),
    );
    await _scrollToShare(tester);
    final shareButton = tester.widget<FilledButton>(
      find.byKey(const Key('share-cards')),
    );
    expect(shareButton.onPressed, isNotNull);
    shareButton.onPressed!();
    await tester.pump();
    await tester.runAsync(
      () => Future<void>.delayed(const Duration(milliseconds: 100)),
    );
    await tester.pump();
    expect(find.text('کارت جوړ نه شو. بيا هڅه وکړئ.'), findsOneWidget);
    expect(find.byKey(const Key('share-cards')), findsOneWidget);
  });

  testWidgets('save action exports cards through the gallery adapter', (
    tester,
  ) async {
    final output = _FakeOutput();
    await tester.pumpWidget(
      MaterialApp(
        home: ShareCardScreen(
          poem: PoemDetail.fromJson(poemDetailJson()),
          output: output,
          renderer: _FakeRenderer(),
          files: _FakeFiles(directory),
        ),
      ),
    );
    await _scrollToShare(tester);
    final saveButton = tester.widget<OutlinedButton>(
      find.byKey(const Key('save-cards')),
    );
    expect(saveButton.onPressed, isNotNull);
    saveButton.onPressed!();
    await tester.pump();
    await tester.runAsync(
      () => Future<void>.delayed(const Duration(milliseconds: 100)),
    );
    await tester.pump();
    expect(output.savedCount, 1);
    expect(find.textContaining('په ګالرۍ کې وساتل شول'), findsOneWidget);
  });
}

Future<void> _scrollToShare(WidgetTester tester) async {
  for (var attempt = 0; attempt < 5; attempt++) {
    if (find.byKey(const Key('share-cards')).evaluate().isNotEmpty) {
      await tester.ensureVisible(find.byKey(const Key('share-cards')));
      await tester.pump();
      return;
    }
    await tester.drag(find.byType(ListView).first, const Offset(0, -400));
    await tester.pump();
  }
}

class _FakeOutput implements ShareCardOutput {
  int sharedCount = 0;
  int savedCount = 0;

  @override
  Future<void> share(List<File> files) async => sharedCount += files.length;

  @override
  Future<void> save(List<File> files) async => savedCount += files.length;
}

class _FakeFiles extends ShareCardFiles {
  const _FakeFiles(super.directory);

  @override
  Future<void> cleanStale({
    required DateTime now,
    Duration maximumAge = const Duration(hours: 24),
  }) async {}

  @override
  Future<void> remove(List<File> files) async {}
}

class _FakeRenderer extends ShareCardRenderer {
  ShareCardRequest? lastRequest;
  bool preparedPaintedPage = false;

  @override
  Future<List<File>> render({
    required ShareCardRequest request,
    required List<ShareCardPage> pages,
    required ShareCardFiles files,
    required PrepareShareCardPage preparePage,
    DateTime? createdAt,
  }) async {
    lastRequest = request;
    final boundary = await preparePage(0);
    preparedPaintedPage =
        boundary != null && boundary.attached && !boundary.debugNeedsPaint;
    return [File('${files.directory.path}/fake.png')];
  }
}

class _FailingRenderer extends ShareCardRenderer {
  const _FailingRenderer();

  @override
  Future<List<File>> render({
    required ShareCardRequest request,
    required List<ShareCardPage> pages,
    required ShareCardFiles files,
    required PrepareShareCardPage preparePage,
    DateTime? createdAt,
  }) => throw const ShareCardExportException(
    ShareCardExportStage.render,
    'render failed',
  );
}
