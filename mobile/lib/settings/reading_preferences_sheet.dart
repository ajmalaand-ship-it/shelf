import 'package:flutter/material.dart';

import 'reader_settings.dart';

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
              Text('د ليک کچه', style: Theme.of(context).textTheme.titleMedium),
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
                'د شعر ليکدود',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 4),
              RadioGroup<ReaderFont>(
                key: const Key('reading-font-selector'),
                groupValue: settings.font,
                onChanged: (value) {
                  if (value != null) settings.setFont(value);
                },
                child: const Column(
                  children: [
                    RadioListTile<ReaderFont>(
                      key: Key('font-choice-vazirmatn'),
                      value: ReaderFont.vazirmatn,
                      title: Text('وزيرمتن — Vazirmatn'),
                      subtitle: Text('اصلي او د پيل ليکدود'),
                    ),
                    RadioListTile<ReaderFont>(
                      key: Key('font-choice-scheherazade'),
                      value: ReaderFont.naskh,
                      title: Text('شهرزاد نو — Scheherazade New'),
                      subtitle: Text('دوديز نسخ'),
                    ),
                    RadioListTile<ReaderFont>(
                      key: Key('font-choice-noto-nastaliq'),
                      value: ReaderFont.literary,
                      title: Text('نوټو نستعليق — Noto Nastaliq Urdu'),
                      subtitle: Text('ادبي نستعليق'),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),
              SegmentedButton<ReaderPalette>(
                segments: const [
                  ButtonSegment(
                    value: ReaderPalette.light,
                    label: Text('روښانه'),
                  ),
                  ButtonSegment(
                    value: ReaderPalette.sepia,
                    label: Text('سپيا'),
                  ),
                  ButtonSegment(
                    value: ReaderPalette.dark,
                    label: Text('تياره'),
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
