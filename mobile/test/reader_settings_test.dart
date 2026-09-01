import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:pitswal/settings/reader_settings.dart';

void main() {
  test('fresh installs use the owner-approved 16 point default', () async {
    SharedPreferences.setMockInitialValues({});
    final preferences = await SharedPreferences.getInstance();
    final settings = await ReaderSettings.load(preferences);

    expect(settings.fontSize, 16);
    expect(preferences.containsKey('reader.font_size'), isFalse);
  });

  test(
    'saved font sizes, including the former default, are preserved',
    () async {
      for (final savedSize in [18.0, 24.0, 32.0, 38.0]) {
        SharedPreferences.setMockInitialValues({'reader.font_size': savedSize});
        final preferences = await SharedPreferences.getInstance();
        final settings = await ReaderSettings.load(preferences);

        expect(settings.fontSize, savedSize);
        expect(preferences.getDouble('reader.font_size'), savedSize);
      }
    },
  );

  test('legacy two-font preferences migrate to the three-font model', () async {
    for (final legacy in ['nastaliq', 'naskh']) {
      SharedPreferences.setMockInitialValues({'reader.font': legacy});
      final preferences = await SharedPreferences.getInstance();
      final settings = await ReaderSettings.load(preferences);

      expect(
        settings.font,
        legacy == 'nastaliq' ? ReaderFont.literary : ReaderFont.naskh,
      );
      expect(preferences.getString('reader.font'), settings.font.name);
    }
  });

  test('reader font, palette, and size preferences persist locally', () async {
    SharedPreferences.setMockInitialValues({});
    final preferences = await SharedPreferences.getInstance();
    final settings = await ReaderSettings.load(preferences);

    await settings.setPalette(ReaderPalette.dark);
    await settings.setFont(ReaderFont.naskh);
    await settings.setFontSize(32);

    final restored = await ReaderSettings.load(preferences);
    expect(restored.palette, ReaderPalette.dark);
    expect(restored.font, ReaderFont.naskh);
    expect(restored.fontFamily, 'ScheherazadeNew');
    expect(restored.fontSize, 32);
  });

  test('reader font size remains within safe limits', () async {
    SharedPreferences.setMockInitialValues({});
    final settings = await ReaderSettings.load(
      await SharedPreferences.getInstance(),
    );

    await settings.setFontSize(100);
    expect(settings.fontSize, 38);
    await settings.setFontSize(5);
    expect(settings.fontSize, 18);
  });

  test('adjustable font-size range remains the established 18 to 38', () {
    expect(ReaderSettings.minimumFontSize, 18);
    expect(ReaderSettings.maximumFontSize, 38);
  });
}
