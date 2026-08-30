import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:pitswal/models/poem.dart';
import 'package:pitswal/share_cards/poem_card_widget.dart';
import 'package:pitswal/share_cards/share_card_models.dart';
import 'package:pitswal/share_cards/share_card_paginator.dart';
import 'package:pitswal/share_cards/share_card_renderer.dart';

import 'test_support.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUpAll(() async {
    final nastaliq = FontLoader('NotoNastaliqUrdu')
      ..addFont(
        rootBundle.load('assets/fonts/noto_nastaliq_urdu/NotoNastaliqUrdu.ttf'),
      );
    final naskh = FontLoader('ScheherazadeNew')
      ..addFont(
        rootBundle.load(
          'assets/fonts/scheherazade_new/ScheherazadeNew-Regular.ttf',
        ),
      );
    await Future.wait([nastaliq.load(), naskh.load()]);
  });

  test('renderer targets an exact 1080 by 1350 output', () {
    expect(const ShareCardRenderer().outputSize, const Size(1080, 1350));
  });

  test('PNG validation accepts the signature and exact IHDR dimensions', () {
    final bytes = Uint8List(24);
    bytes.setAll(0, const [137, 80, 78, 71, 13, 10, 26, 10]);
    final data = ByteData.sublistView(bytes);
    data.setUint32(16, 1080);
    data.setUint32(20, 1350);

    const ShareCardRenderer().validatePng(bytes, 1080, 1350);
  });

  test('PNG validation rejects empty, corrupt, and wrong-sized output', () {
    const renderer = ShareCardRenderer();
    expect(
      () => renderer.validatePng(Uint8List(0), 1080, 1350),
      throwsA(
        isA<ShareCardExportException>().having(
          (error) => error.stage,
          'stage',
          ShareCardExportStage.pngEncode,
        ),
      ),
    );
    final bytes = Uint8List(24);
    bytes.setAll(0, const [137, 80, 78, 71, 13, 10, 26, 10]);
    final data = ByteData.sublistView(bytes);
    data.setUint32(16, 360);
    data.setUint32(20, 450);
    expect(
      () => renderer.validatePng(bytes, 1080, 1350),
      throwsA(isA<ShareCardExportException>()),
    );
  });

  testWidgets('representative P4 cards rasterize without layout failure', (
    tester,
  ) async {
    tester.view.physicalSize = const Size(360, 450);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);
    final generateArtifacts = Platform.environment['P4_ARTIFACT_DIR'] != null;

    final shortPoem = PoemDetail.fromJson(
      poemDetailJson(
        title: 'لنډ شعر',
        body: 'ټ ډ ړ ږ ښ ڼ ې ۍ\nزړۀ، نړۍ، مينهٔ',
      ),
    );
    for (final theme in ShareCardTheme.values) {
      final request = ShareCardRequest(
        poem: shortPoem,
        collectionTitle: 'ازموينيزه ټولګه',
        scope: ShareCardScope.selection,
        text: shortPoem.readableText,
        theme: theme,
      );
      await _pumpCard(
        tester,
        request: request,
        page: ShareCardPage(
          text: shortPoem.readableText,
          pageNumber: 1,
          pageCount: 1,
        ),
      );
      if (generateArtifacts) {
        await expectLater(
          find.byKey(const Key('visual-card-boundary')),
          matchesGoldenFile('../build/p4-qa-artifacts/short_${theme.name}.png'),
        );
      }
      expect(tester.takeException(), isNull);
    }

    final translation = PoemDetail.fromJson(
      poemDetailJson(
        title: 'ژمى',
        workType: 'TRANSLATION',
        body: 'د ژباړې لومړۍ کرښه\nد ژباړې دويمه کرښه',
      ),
    );
    final translationRequest = ShareCardRequest(
      poem: translation,
      collectionTitle: 'ژباړې',
      scope: ShareCardScope.accessiblePoem,
      text: translation.readableText,
      theme: ShareCardTheme.dark,
    );
    await _pumpCard(
      tester,
      request: translationRequest,
      page: ShareCardPage(
        text: translation.readableText,
        pageNumber: 1,
        pageCount: 1,
      ),
    );
    if (generateArtifacts) {
      await expectLater(
        find.byKey(const Key('visual-card-boundary')),
        matchesGoldenFile('../build/p4-qa-artifacts/translation_dark.png'),
      );
    }

    final longText = List.generate(
      18,
      (index) => index > 0 && index % 6 == 0
          ? '\nد ازموينې ${index + 1} کرښه — نوی بند'
          : 'د ازموينې ${index + 1} کرښه — ټ ډ ړ ږ ښ ڼ ې ۍ',
    ).join('\n');
    final longPoem = PoemDetail.fromJson(
      poemDetailJson(title: 'اوږد شعر', body: longText),
    );
    final pages = const ShareCardPaginator().paginate(longText);
    expect(pages.length, greaterThan(2));
    final longRequest = ShareCardRequest(
      poem: longPoem,
      collectionTitle: 'اوږده ازموينه',
      scope: ShareCardScope.accessiblePoem,
      text: longText,
      theme: ShareCardTheme.light,
    );
    for (final page in pages) {
      await _pumpCard(tester, request: longRequest, page: page);
      if (generateArtifacts) {
        await expectLater(
          find.byKey(const Key('visual-card-boundary')),
          matchesGoldenFile(
            '../build/p4-qa-artifacts/long_light_${page.pageNumber.toString().padLeft(2, '0')}.png',
          ),
        );
      }
      expect(tester.takeException(), isNull);
    }
  });
}

Future<void> _pumpCard(
  WidgetTester tester, {
  required ShareCardRequest request,
  required ShareCardPage page,
}) async {
  await tester.pumpWidget(
    MaterialApp(
      home: Scaffold(
        body: Center(
          child: RepaintBoundary(
            key: const Key('visual-card-boundary'),
            child: PoemCardWidget(request: request, page: page),
          ),
        ),
      ),
    ),
  );
  await tester.pump();
}
