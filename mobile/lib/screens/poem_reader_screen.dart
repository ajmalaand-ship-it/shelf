import 'package:flutter/material.dart';

import '../models/poem.dart';
import '../repository/poetry_repository.dart';
import '../settings/reader_settings.dart';

class PoemReaderScreen extends StatefulWidget {
  const PoemReaderScreen({
    required this.poemId,
    required this.initialTitle,
    required this.contentVersion,
    required this.repository,
    required this.settings,
    super.key,
  });

  final int poemId;
  final String initialTitle;
  final int contentVersion;
  final PoetryDataSource repository;
  final ReaderSettings settings;

  @override
  State<PoemReaderScreen> createState() => _PoemReaderScreenState();
}

class _PoemReaderScreenState extends State<PoemReaderScreen> {
  late Future<PoemDetail> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<PoemDetail> _load() =>
      widget.repository.loadPoem(widget.poemId, widget.contentVersion);

  @override
  Widget build(BuildContext context) => AnimatedBuilder(
    animation: widget.settings,
    builder: (context, _) {
      final colors = _ReaderColors.forPalette(widget.settings.palette);
      return Scaffold(
        backgroundColor: colors.background,
        appBar: AppBar(
          backgroundColor: colors.background,
          foregroundColor: colors.foreground,
          title: Text(widget.initialTitle, maxLines: 1),
          actions: [
            IconButton(
              tooltip: 'د لوست بڼه',
              onPressed: () => _showControls(context),
              icon: const Icon(Icons.text_fields_rounded),
            ),
          ],
        ),
        body: FutureBuilder<PoemDetail>(
          future: _future,
          builder: (context, snapshot) {
            if (snapshot.connectionState != ConnectionState.done) {
              return Center(
                child: CircularProgressIndicator(color: colors.foreground),
              );
            }
            if (snapshot.hasError || !snapshot.hasData) {
              return _ReaderError(
                color: colors.foreground,
                onRetry: () => setState(() => _future = _load()),
              );
            }
            return _PoemBody(
              poem: snapshot.requireData,
              settings: widget.settings,
              colors: colors,
            );
          },
        ),
      );
    },
  );

  Future<void> _showControls(
    BuildContext context,
  ) => showModalBottomSheet<void>(
    context: context,
    showDragHandle: true,
    builder: (_) => AnimatedBuilder(
      animation: widget.settings,
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
                      value: widget.settings.fontSize,
                      label: widget.settings.fontSize.round().toString(),
                      onChanged: widget.settings.setFontSize,
                    ),
                  ),
                  const Icon(Icons.text_increase_rounded),
                ],
              ),
              const SizedBox(height: 8),
              SegmentedButton<ReaderFont>(
                segments: const [
                  ButtonSegment(
                    value: ReaderFont.nastaliq,
                    label: Text('نستعليق'),
                  ),
                  ButtonSegment(value: ReaderFont.naskh, label: Text('نسخ')),
                ],
                selected: {widget.settings.font},
                onSelectionChanged: (value) =>
                    widget.settings.setFont(value.first),
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
                selected: {widget.settings.palette},
                onSelectionChanged: (value) =>
                    widget.settings.setPalette(value.first),
              ),
            ],
          ),
        ),
      ),
    ),
  );
}

class _PoemBody extends StatelessWidget {
  const _PoemBody({
    required this.poem,
    required this.settings,
    required this.colors,
  });

  final PoemDetail poem;
  final ReaderSettings settings;
  final _ReaderColors colors;

  @override
  Widget build(BuildContext context) => Directionality(
    textDirection: TextDirection.rtl,
    child: SelectionArea(
      child: SingleChildScrollView(
        key: const Key('poem-scroll-view'),
        padding: const EdgeInsets.fromLTRB(24, 18, 24, 72),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 720),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                if (!poem.isUntitled)
                  Text(
                    poem.title!,
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: colors.foreground,
                      fontFamily: settings.fontFamily,
                      fontSize: settings.fontSize + 5,
                      height: 1.8,
                    ),
                  ),
                if (poem.isTranslation) ...[
                  const SizedBox(height: 18),
                  Text(
                    'اصلي شاعر: ${poem.originalAuthor ?? '—'}\n'
                    'پښتو ژباړه: ${poem.translator ?? '—'}',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: colors.muted, height: 1.6),
                  ),
                ],
                if (poem.sourceDatePlace case final datePlace?) ...[
                  const SizedBox(height: 14),
                  Text(
                    datePlace,
                    textAlign: TextAlign.center,
                    style: TextStyle(color: colors.muted),
                  ),
                ],
                const SizedBox(height: 28),
                if (poem.locked) _LockedNotice(color: colors.foreground),
                SelectableText(
                  poem.readableText,
                  key: const Key('poem-body'),
                  textDirection: TextDirection.rtl,
                  textAlign: TextAlign.start,
                  style: TextStyle(
                    color: colors.foreground,
                    fontFamily: settings.fontFamily,
                    fontSize: settings.fontSize,
                    height: 2.2,
                  ),
                ),
                if (poem.audioAvailable) ...[
                  const SizedBox(height: 26),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.graphic_eq_rounded, color: colors.muted),
                      const SizedBox(width: 8),
                      Text(
                        'غږيزه بڼه شته',
                        style: TextStyle(color: colors.muted),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    ),
  );
}

class _LockedNotice extends StatelessWidget {
  const _LockedNotice({required this.color});
  final Color color;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 18),
    child: Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Icon(Icons.lock_outline_rounded, color: color),
        const SizedBox(width: 8),
        Text('دا شعر تړلی دی', style: TextStyle(color: color)),
      ],
    ),
  );
}

class _ReaderError extends StatelessWidget {
  const _ReaderError({required this.color, required this.onRetry});
  final Color color;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) => Center(
    child: Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text('شعر ترلاسه نه شو.', style: TextStyle(color: color)),
        const SizedBox(height: 12),
        OutlinedButton(onPressed: onRetry, child: const Text('بيا هڅه وکړئ')),
      ],
    ),
  );
}

class _ReaderColors {
  const _ReaderColors(this.background, this.foreground, this.muted);
  final Color background;
  final Color foreground;
  final Color muted;

  static _ReaderColors forPalette(ReaderPalette palette) => switch (palette) {
    ReaderPalette.light => const _ReaderColors(
      Color(0xfffffdf8),
      Color(0xff211d18),
      Color(0xff6a6258),
    ),
    ReaderPalette.sepia => const _ReaderColors(
      Color(0xfff1e3c7),
      Color(0xff3e2f20),
      Color(0xff765f47),
    ),
    ReaderPalette.dark => const _ReaderColors(
      Color(0xff171512),
      Color(0xfff2e9db),
      Color(0xffbcb0a1),
    ),
  };
}
