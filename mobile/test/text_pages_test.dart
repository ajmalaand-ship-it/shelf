import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:shelf/reading/text_pages.dart';
import 'package:shelf/reading/reading_position.dart';
import 'package:shelf/settings/reader_settings.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  setUpAll(() async {
    for (final entry in {
      'Vazirmatn': 'assets/fonts/vazirmatn/Vazirmatn-wght.ttf',
      'ScheherazadeNew':
          'assets/fonts/scheherazade_new/ScheherazadeNew-Regular.ttf',
      'NotoNastaliqUrdu':
          'assets/fonts/noto_nastaliq_urdu/NotoNastaliqUrdu.ttf',
    }.entries) {
      await (FontLoader(
        entry.key,
      )..addFont(rootBundle.load(entry.value))).load();
    }
  });
  test('exact source continuity, graphemes, stanzas and measured fit across fonts and viewports', () {
    final source = List.generate(
      12,
      (i) => 'بِسم الله — کرښه $i\r\n  بل بند 👨‍👩‍👧‍👦 e\u0301\r\n\r\n',
    ).join();
    final boundaries = <int>{0};
    var offset = 0;
    for (final c in source.characters) {
      offset += c.length;
      boundaries.add(offset);
    }
    for (final font in ['Vazirmatn', 'ScheherazadeNew', 'NotoNastaliqUrdu']) {
      for (final direction in TextDirection.values) {
        for (final viewport in [
          const Size(280, 300),
          const Size(320, 150),
          const Size(720, 450),
        ]) {
          for (final scale in [1.0, 2.0]) {
            final style = TextStyle(
              fontFamily: font,
              fontSize: 38,
              height: 2.2,
            );
            final pages = TextPages.layout(
              source: source,
              style: style,
              textScaler: TextScaler.linear(scale),
              direction: direction,
              width: viewport.width,
              height: viewport.height,
            );
            expect(pages.map((p) => p.text(source)).join(), source);
            expect(pages.first.start, 0);
            expect(pages.last.end, source.length);
            for (var i = 0; i < pages.length; i++) {
              final p = pages[i];
              expect(p.end, greaterThan(p.start));
              expect(boundaries, contains(p.start));
              expect(boundaries, contains(p.end));
              if (i > 0) expect(p.start, pages[i - 1].end);
              final painter = TextPainter(
                text: TextSpan(text: p.text(source), style: style),
                textDirection: direction,
                textScaler: TextScaler.linear(scale),
              )..layout(maxWidth: viewport.width);
              if (!p.oversized) {
                expect(painter.height, lessThanOrEqualTo(viewport.height));
              }
              painter.dispose();
            }
          }
        }
      }
    }
  });
  test(
    'intact fitting stanzas and oversized lines remain exact and reachable',
    () {
      const source = 'first stanza\nsecond line\n\nthird stanza\n\n';
      final pages = TextPages.layout(
        source: source,
        style: const TextStyle(fontSize: 20, height: 1),
        textScaler: TextScaler.noScaling,
        direction: TextDirection.ltr,
        width: 400,
        height: 85,
      );
      expect(pages.first.text(source), 'first stanza\nsecond line\n\n');
      final tiny = TextPages.layout(
        source: source,
        style: const TextStyle(fontSize: 38, height: 2.2),
        textScaler: const TextScaler.linear(3),
        direction: TextDirection.rtl,
        width: 100,
        height: 1,
      );
      expect(tiny.every((p) => p.oversized), true);
      expect(tiny.map((p) => p.text(source)).join(), source);
    },
  );
  test('reflow locates the stable text offset, never an old page number', () {
    final source = List.filled(
      70,
      'A long paragraph with original spaces.\n\n',
    ).join();
    List<TextPage> layout(double w, double h) => TextPages.layout(
      source: source,
      style: const TextStyle(fontSize: 24),
      textScaler: TextScaler.noScaling,
      direction: TextDirection.ltr,
      width: w,
      height: h,
    );
    final old = layout(320, 400), reflow = layout(260, 180);
    final offset = old[3].start;
    final current = reflow[TextPages.containing(reflow, offset)];
    expect(current.start, lessThanOrEqualTo(offset));
    expect(current.end, greaterThan(offset));
    expect(TextPages.containing(reflow, source.length), reflow.length - 1);
  });
  test('local positions isolate account, book and environment and contain no text or page number', () async {
    SharedPreferences.setMockInitialValues({});
    final prefs = await SharedPreferences.getInstance();
    final settings = await ReaderSettings.load(prefs);
    expect(settings.readingMode, ReadingMode.scroll);
    await settings.setReadingMode(ReadingMode.pages);
    expect((await ReaderSettings.load(prefs)).readingMode, ReadingMode.pages);
    final positions = settings.positions;
    String key(String env, String account, String book) =>
        positions.key(environment: env, account: account, book: book);
    final location = ReadingPosition(
      item: 301,
      offset: 19,
      fingerprint: ReadingPosition.fingerprintOf('exact text'),
    );
    await positions.save(key('staging', '1', '42'), location);
    expect(positions.read(key('staging', '1', '42'))!.offset, 19);
    for (final k in [
      key('production', '1', '42'),
      key('staging', '2', '42'),
      key('staging', 'guest', '42'),
      key('staging', '1', '43'),
    ]) {
      expect(positions.read(k), null);
    }
    expect(
      location.toJson().keys,
      unorderedEquals(['item', 'offset', 'fingerprint', 'details']),
    );
    expect(ReadingPosition.parse('{bad'), null);
  });
}
