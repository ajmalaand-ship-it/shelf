import 'package:flutter/material.dart';

import 'reader_settings.dart';
import '../l10n/app_strings.dart';

Future<void> showReadingPreferences(
  BuildContext context,
  ReaderSettings settings,
) => showModalBottomSheet<void>(
  context: context,
  showDragHandle: true,
  isScrollControlled: true,
  builder: (_) => AnimatedBuilder(
    animation: settings,
    builder: (context, _) => SafeArea(
      child: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text(
                AppStrings.of(context).fontSize,
                style: Theme.of(context).textTheme.titleMedium,
              ),
              Row(
                children: [
                  const Icon(Icons.text_decrease_rounded),
                  Expanded(
                    child: Slider(
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
                  ),
                  const Icon(Icons.text_increase_rounded),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                AppStrings.of(context).readingFont,
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 4),
              RadioGroup<ReaderFont>(
                key: const Key('reading-font-selector'),
                groupValue: settings.font,
                onChanged: (value) {
                  if (value != null) settings.setFont(value);
                },
                child: Column(
                  children: [
                    RadioListTile<ReaderFont>(
                      key: Key('font-choice-vazirmatn'),
                      value: ReaderFont.vazirmatn,
                      title: Text(AppStrings.of(context).vazirmatnName),
                      subtitle: Text(
                        AppStrings.of(context).defaultFontDescription,
                      ),
                    ),
                    RadioListTile<ReaderFont>(
                      key: Key('font-choice-scheherazade'),
                      value: ReaderFont.naskh,
                      title: Text(AppStrings.of(context).scheherazadeName),
                      subtitle: Text(AppStrings.of(context).naskhDescription),
                    ),
                    RadioListTile<ReaderFont>(
                      key: Key('font-choice-noto-nastaliq'),
                      value: ReaderFont.literary,
                      title: Text(AppStrings.of(context).nastaliqName),
                      subtitle: Text(
                        AppStrings.of(context).nastaliqDescription,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),
              SegmentedButton<ReaderPalette>(
                segments: [
                  ButtonSegment(
                    value: ReaderPalette.light,
                    label: Text(AppStrings.of(context).lightPalette),
                  ),
                  ButtonSegment(
                    value: ReaderPalette.sepia,
                    label: Text(AppStrings.of(context).sepiaPalette),
                  ),
                  ButtonSegment(
                    value: ReaderPalette.dark,
                    label: Text(AppStrings.of(context).darkPalette),
                  ),
                ],
                selected: {settings.palette},
                onSelectionChanged: (value) => settings.setPalette(value.first),
              ),
            ],
          ),
        ),
      ),
    ),
  ),
);
