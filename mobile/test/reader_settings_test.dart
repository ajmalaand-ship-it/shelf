import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:pitswal/settings/reader_settings.dart';

void main() {
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
}
