import 'package:flutter/material.dart';

import 'reader_settings.dart';
import 'reader_palette_colors.dart';
import '../l10n/app_strings.dart';

Future<void> showReadingPreferences(
  BuildContext context,
  ReaderSettings settings,
) => showModalBottomSheet<void>(
  context: context,
  showDragHandle: true,
  isScrollControlled: true,
  useSafeArea: true,
  builder: (_) => ReadingPreferences(settings: settings),
);

class ReadingPreferences extends StatelessWidget {
  const ReadingPreferences({required this.settings, super.key});
  final ReaderSettings settings;
  @override
  Widget build(BuildContext context) => AnimatedBuilder(
    animation: settings,
    builder: (context, _) {
      final strings = AppStrings.of(context);
      final colors = ReaderPaletteColors.forPalette(settings.palette);
      return SafeArea(
        top: false,
        child: LayoutBuilder(
          builder: (context, constraints) => Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        strings.readingPreferences,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: Theme.of(context).textTheme.titleMedium
                            ?.copyWith(fontWeight: FontWeight.w700),
                      ),
                    ),
                    IconButton(
                      key: const Key('reading-preferences-close'),
                      tooltip: MaterialLocalizations.of(context)
                          .closeButtonTooltip,
                      onPressed: () => Navigator.pop(context),
                      icon: const Icon(Icons.close),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Container(
                  key: const Key('reading-live-preview'),
                  constraints: BoxConstraints(
                    maxHeight: constraints.maxHeight * .30,
                  ),
                  clipBehavior: Clip.antiAlias,
                  decoration: BoxDecoration(
                    color: colors.background,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  // Keep the selected font and accessibility scale. A single
                  // line can pan horizontally; unusually tall text can pan
                  // vertically within the compact pinned preview region.
                  child: SingleChildScrollView(
                    primary: false,
                    child: SingleChildScrollView(
                      primary: false,
                      scrollDirection: Axis.horizontal,
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 8,
                      ),
                      child: Text(
                        strings.isEnglish
                            ? 'Books change your life'
                            : 'کتاب مو ژوند بدلوي',
                        key: const Key('reading-live-preview-text'),
                        maxLines: 1,
                        softWrap: false,
                        style: TextStyle(
                          fontFamily: settings.fontFamily,
                          fontSize: settings.fontSize,
                          height: 1.75,
                          color: colors.foreground,
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Flexible(
                  child: SingleChildScrollView(
                    key: const Key('reading-preferences-scroll'),
                    primary: false,
                    padding: const EdgeInsets.only(bottom: 20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Text(
                          strings.isEnglish ? 'Reading mode' : 'د لوست بڼه',
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        const SizedBox(height: 8),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: [
                            for (final mode in ReadingMode.values)
                              ChoiceChip(
                                key: Key('reading-mode-${mode.name}'),
                                selected: settings.readingMode == mode,
                                label: Text(
                                  mode == ReadingMode.scroll
                                      ? (strings.isEnglish
                                            ? 'Scroll'
                                            : 'پرله‌پسې لوست')
                                      : (strings.isEnglish ? 'Pages' : 'پاڼې'),
                                ),
                                labelStyle: TextStyle(
                                  color: settings.readingMode == mode
                                      ? Theme.of(context).colorScheme.onPrimary
                                      : Theme.of(context).colorScheme.primary,
                                ),
                                onSelected: (_) =>
                                    settings.setReadingMode(mode),
                              ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Text(
                          strings.fontSize,
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        Row(
                          children: [
                            IconButton.filledTonal(
                              key: const Key('font-size-decrease'),
                              tooltip: strings.isEnglish
                                  ? 'Smaller text'
                                  : 'کوچنۍ لیکنه',
                              onPressed:
                                  settings.fontSize <=
                                      ReaderSettings.minimumFontSize
                                  ? null
                                  : () => settings.setFontSize(
                                      settings.fontSize - 2,
                                    ),
                              icon: const Icon(Icons.text_decrease),
                            ),
                            Expanded(
                              child: Center(
                                child: Text(
                                  settings.fontSize.round().toString(),
                                  style: Theme.of(context)
                                      .textTheme
                                      .titleMedium,
                                ),
                              ),
                            ),
                            IconButton.filledTonal(
                              key: const Key('font-size-increase'),
                              tooltip: strings.isEnglish
                                  ? 'Larger text'
                                  : 'لویه لیکنه',
                              onPressed:
                                  settings.fontSize >=
                                      ReaderSettings.maximumFontSize
                                  ? null
                                  : () => settings.setFontSize(
                                      settings.fontSize + 2,
                                    ),
                              icon: const Icon(Icons.text_increase),
                            ),
                          ],
                        ),
                        Slider(
                          key: const Key('font-size-slider'),
                          min: ReaderSettings.minimumFontSize,
                          max: ReaderSettings.maximumFontSize,
                          divisions: 11,
                          value: settings.fontSize.clamp(
                            ReaderSettings.minimumFontSize,
                            ReaderSettings.maximumFontSize,
                          ),
                          label: settings.fontSize.round().toString(),
                          onChanged: settings.setFontSize,
                        ),
                        Text(
                          strings.readingFont,
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        const SizedBox(height: 12),
                        RadioGroup<ReaderFont>(
                          key: const Key('reading-font-selector'),
                          groupValue: settings.font,
                          onChanged: (value) {
                            if (value != null) settings.setFont(value);
                          },
                          child: Column(
                            children: [
                              for (final font in ReaderFont.values)
                                Padding(
                                  padding: const EdgeInsets.only(bottom: 12),
                                  child: Card(
                                    color: settings.font == font
                                        ? Theme.of(context)
                                              .colorScheme
                                              .primaryContainer
                                        : Colors.white,
                                    child: RadioListTile<ReaderFont>(
                                      key: Key(switch (font) {
                                        ReaderFont.vazirmatn =>
                                          'font-choice-vazirmatn',
                                        ReaderFont.naskh =>
                                          'font-choice-scheherazade',
                                        ReaderFont.literary =>
                                          'font-choice-noto-nastaliq',
                                      }),
                                      value: font,
                                      title: Text(switch (font) {
                                        ReaderFont.vazirmatn =>
                                          strings.vazirmatnName,
                                        ReaderFont.naskh =>
                                          strings.scheherazadeName,
                                        ReaderFont.literary =>
                                          strings.nastaliqName,
                                      }),
                                      subtitle: Text(switch (font) {
                                        ReaderFont.vazirmatn =>
                                          strings.defaultFontDescription,
                                        ReaderFont.naskh =>
                                          strings.naskhDescription,
                                        ReaderFont.literary =>
                                          strings.nastaliqDescription,
                                      }),
                                    ),
                                  ),
                                ),
                            ],
                          ),
                        ),
                        Text(
                          strings.isEnglish ? 'Page color' : 'د پاڼې رنګ',
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        const SizedBox(height: 12),
                        LayoutBuilder(
                          builder: (context, constraints) => Wrap(
                            spacing: 12,
                            runSpacing: 12,
                            children: [
                              for (final palette in ReaderPalette.values)
                                SizedBox(
                                  width: constraints.maxWidth < 280
                                      ? constraints.maxWidth
                                      : (constraints.maxWidth - 24) / 3,
                                  child: _ThemeSwatch(
                                    palette: palette,
                                    settings: settings,
                                  ),
                                ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    },
  );
}

class _ThemeSwatch extends StatelessWidget {
  const _ThemeSwatch({required this.palette, required this.settings});
  final ReaderPalette palette;
  final ReaderSettings settings;
  @override
  Widget build(BuildContext context) {
    final colors = ReaderPaletteColors.forPalette(palette);
    final s = AppStrings.of(context);
    final label = switch (palette) {
      ReaderPalette.light => s.lightPalette,
      ReaderPalette.sepia => s.sepiaPalette,
      ReaderPalette.dark => s.darkPalette,
    };
    return Semantics(
      selected: settings.palette == palette,
      child: OutlinedButton(
        key: Key('theme-${palette.name}'),
        onPressed: () => settings.setPalette(palette),
        style: OutlinedButton.styleFrom(
          backgroundColor: colors.background,
          foregroundColor: colors.foreground,
          side: BorderSide(
            color: settings.palette == palette
                ? Theme.of(context).colorScheme.primary
                : Theme.of(context).colorScheme.outlineVariant,
            width: settings.palette == palette ? 2 : 1,
          ),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('Aa', style: TextStyle(fontSize: 24)),
            Text(label, textAlign: TextAlign.center),
            if (settings.palette == palette) const Icon(Icons.check, size: 18),
          ],
        ),
      ),
    );
  }
}
