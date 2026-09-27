import 'package:flutter_test/flutter_test.dart';
import 'package:shelf/share_cards/share_card_models.dart';
import 'package:shelf/share_cards/share_card_paginator.dart';

void main() {
  test('selected lines preserve exact contiguous source text', () {
    const source = 'ټ، ډ، ړ\nږ، ښ، ڼ\nزېړۀ، نړۍ\nمينهٔ، رؤيا';
    final lines = selectablePoemLines(source);
    var selection = selectContiguousLine(current: null, tapped: 1);
    selection = selectContiguousLine(current: selection, tapped: 2);

    expect(selection.count, 2);
    expect(selectedLineText(lines, selection), 'ږ، ښ، ڼ\nزېړۀ، نړۍ');
  });

  test('selection crossing a stanza preserves its blank separator', () {
    final lines = selectablePoemLines('لومړۍ کرښه\n\nدويم بند');
    expect(
      selectedLineText(lines, const ShareLineSelection(0, 1)),
      'لومړۍ کرښه\n\nدويم بند',
    );
  });

  test('non-contiguous tap starts a new contiguous selection', () {
    var selection = selectContiguousLine(current: null, tapped: 0);
    selection = selectContiguousLine(current: selection, tapped: 3);
    expect(selection.start, 3);
    expect(selection.end, 3);
  });

  test('selection never exceeds four lines', () {
    var selection = const ShareLineSelection(0, 3);
    selection = selectContiguousLine(current: selection, tapped: 4);
    expect(selection.count, 1);
    expect(selection.start, 4);
  });

  test('one line and four lines produce one deterministic card', () {
    const paginator = ShareCardPaginator();
    expect(paginator.paginate('يوه کرښه'), hasLength(1));
    final first = paginator.paginate('يوه\nدوه\nدرې\nڅلور');
    final second = paginator.paginate('يوه\nدوه\nدرې\nڅلور');
    expect(first, hasLength(1));
    expect(second.map((page) => page.text), first.map((page) => page.text));
  });

  test('long poem paginates without blank lost or duplicated lines', () {
    final source = List.generate(
      80,
      (index) => index > 0 && index % 8 == 0
          ? '\nکرښه ${index + 1} — نوی بند'
          : 'کرښه ${index + 1} — ټ ډ ړ ږ ښ ڼ ې ۍ',
    ).join('\n');
    const paginator = ShareCardPaginator(textHeight: 180);
    final pages = paginator.paginate(source);

    expect(pages.length, greaterThan(5));
    expect(pages.every((page) => page.text.trim().isNotEmpty), isTrue);
    expect(pages.last.pageNumber, pages.length);
    expect(pages.every((page) => page.pageCount == pages.length), isTrue);
    for (var index = 1; index <= 80; index++) {
      final marker = 'کرښه $index —';
      expect(pages.where((page) => page.text.contains(marker)), hasLength(1));
    }
  });

  test('short stanzas stay together when they fit', () {
    const source = 'لومړۍ کرښه\nدويمه کرښه\n\nدرېيمه کرښه\nڅلورمه کرښه';
    final pages = const ShareCardPaginator(textHeight: 130).paginate(source);
    expect(pages, hasLength(2));
    expect(pages.first.text, 'لومړۍ کرښه\nدويمه کرښه');
    expect(pages.last.text, 'درېيمه کرښه\nڅلورمه کرښه');
  });

  test('very long single line splits only at word boundaries', () {
    final words = List.generate(100, (index) => 'کلمه$index');
    final pages = const ShareCardPaginator(textHeight: 100)
        .paginate(words.join(' '));
    expect(pages.length, greaterThan(1));
    final outputWords = pages.expand((page) => page.text.split(' ')).toList();
    expect(outputWords, words);
  });
}
