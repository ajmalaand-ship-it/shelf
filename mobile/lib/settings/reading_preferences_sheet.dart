import 'package:flutter/material.dart';

import 'reader_settings.dart';

Future<void> showReadingPreferences(
  BuildContext context,
  ReaderSettings settings,
) => showModalBottomSheet<void>(
  context: context,
  showDragHandle: true,
  builder: (_) => AnimatedBuilder(
    animation: settings,
    builder: (context, _) => SafeArea(
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
                    min: 18,
                    max: 38,
                    divisions: 10,
                    value: settings.fontSize,
                    label: settings.fontSize.round().toString(),
                    onChanged: settings.setFontSize,
                  ),
                ),
                const Icon(Icons.text_increase_rounded),
              ],
            ),
            const SizedBox(height: 8),
            SegmentedButton<ReaderFont>(
              key: const Key('reading-font-selector'),
              segments: const [
                ButtonSegment(
                  value: ReaderFont.vazirmatn,
                  label: Text('وزيرمتن'),
                ),
                ButtonSegment(value: ReaderFont.naskh, label: Text('نسخ')),
                ButtonSegment(value: ReaderFont.literary, label: Text('ادبي')),
              ],
              selected: {settings.font},
              onSelectionChanged: (value) => settings.setFont(value.first),
            ),
            const SizedBox(height: 14),
            SegmentedButton<ReaderPalette>(
              segments: const [
                ButtonSegment(
                  value: ReaderPalette.light,
                  label: Text('روښانه'),
                ),
                ButtonSegment(value: ReaderPalette.sepia, label: Text('سپيا')),
                ButtonSegment(value: ReaderPalette.dark, label: Text('تياره')),
              ],
              selected: {settings.palette},
              onSelectionChanged: (value) => settings.setPalette(value.first),
            ),
          ],
        ),
      ),
    ),
  ),
);
