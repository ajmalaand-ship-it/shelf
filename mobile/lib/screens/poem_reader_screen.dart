import 'package:flutter/material.dart';

import '../audio/audio_playback_controller.dart';
import '../models/poem.dart';
import '../purchases/entitlement_controller.dart';
import '../repository/poetry_repository.dart';
import '../settings/reader_settings.dart';
import '../settings/reading_preferences_sheet.dart';
import '../share_cards/share_card_screen.dart';
import '../widgets/poetry_text.dart';
import '../widgets/untitled_poem_marker.dart';

class PoemReaderScreen extends StatefulWidget {
  const PoemReaderScreen({
    required this.poemId,
    required this.contentVersion,
    required this.repository,
    required this.settings,
    this.collectionTitle,
    this.audioController,
    this.entitlements,
    this.ownerPreviewMode = false,
    super.key,
  });

  final int poemId;
  final int contentVersion;
  final PoetryDataSource repository;
  final ReaderSettings settings;
  final String? collectionTitle;
  final AudioPlaybackController? audioController;
  final EntitlementController? entitlements;
  final bool ownerPreviewMode;

  @override
  State<PoemReaderScreen> createState() => _PoemReaderScreenState();
}

class _PoemReaderScreenState extends State<PoemReaderScreen> {
  late Future<PoemDetail> _future;
  PoemDetail? _loadedPoem;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<PoemDetail> _load({bool refreshEntitlement = false}) async {
    final poem = await widget.repository.loadPoem(
      widget.poemId,
      widget.contentVersion,
      refreshEntitlement: refreshEntitlement,
    );
    if (mounted) setState(() => _loadedPoem = poem);
    return poem;
  }

  @override
  Widget build(BuildContext context) => AnimatedBuilder(
    animation: widget.settings,
    builder: (context, _) {
      final colors = _ReaderColors.forPalette(widget.settings.palette);
      return Scaffold(
        backgroundColor: colors.background,
        appBar: AppBar(
          key: const Key('reader-app-bar'),
          backgroundColor: colors.background,
          foregroundColor: colors.foreground,
          surfaceTintColor: colors.background,
          shadowColor: Colors.transparent,
          scrolledUnderElevation: 0,
          actions: [
            TextButton.icon(
              key: const Key('reader-share'),
              onPressed:
                  _loadedPoem == null ||
                      _loadedPoem!.readableText.trim().isEmpty
                  ? null
                  : () => Navigator.of(context).push(
                      MaterialPageRoute<void>(
                        builder: (_) => ShareCardScreen(
                          poem: _loadedPoem!,
                          collectionTitle: widget.collectionTitle,
                          fontFamily: widget.settings.fontFamily,
                        ),
                      ),
                    ),
              icon: const Icon(Icons.ios_share_rounded),
              label: const Text('شریکول'),
            ),
            TextButton.icon(
              key: const Key('reader-font-chooser'),
              onPressed: () => showReadingPreferences(context, widget.settings),
              icon: const Icon(Icons.text_fields_rounded),
              label: const Text('لیکبڼه'),
            ),
          ],
        ),
        body: ClipRect(
          key: const Key('reader-body-clip'),
          child: FutureBuilder<PoemDetail>(
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
                  onRetry: () => setState(() {
                    _loadedPoem = null;
                    _future = _load();
                  }),
                );
              }
              return _PoemBody(
                poem: snapshot.requireData,
                settings: widget.settings,
                colors: colors,
                audioController:
                    widget.audioController ?? InactiveAudioController(),
                ownerPreviewMode: widget.ownerPreviewMode,
              );
            },
          ),
        ),
      );
    },
  );
}

class _PoemBody extends StatelessWidget {
  const _PoemBody({
    required this.poem,
    required this.settings,
    required this.colors,
    required this.audioController,
    required this.ownerPreviewMode,
  });

  final PoemDetail poem;
  final ReaderSettings settings;
  final _ReaderColors colors;
  final AudioPlaybackController audioController;
  final bool ownerPreviewMode;

  @override
  Widget build(BuildContext context) => Directionality(
    textDirection: TextDirection.rtl,
    child: SafeArea(
      top: false,
      child: SelectionArea(
        child: SingleChildScrollView(
          key: const Key('poem-scroll-view'),
          clipBehavior: Clip.hardEdge,
          padding: const EdgeInsets.fromLTRB(12, 24, 12, 72),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 720),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  if (!poem.isUntitled)
                    Text(
                      poem.title!,
                      key: const Key('poem-title'),
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: colors.foreground,
                        fontFamily: settings.fontFamily,
                        fontSize: settings.fontSize + 8,
                        fontWeight: settings.font == ReaderFont.vazirmatn
                            ? FontWeight.w700
                            : FontWeight.w400,
                        height: 1.6,
                      ),
                    )
                  else
                    Center(child: UntitledPoemMarker(color: colors.muted)),
                  if (poem.artworkAvailable &&
                      !poem.artworkLocked &&
                      poem.artworkUrl != null) ...[
                    const SizedBox(height: 22),
                    ConstrainedBox(
                      constraints: const BoxConstraints(maxHeight: 420),
                      child: Image.network(
                        poem.artworkUrl.toString(),
                        key: const Key('poem-artwork'),
                        fit: BoxFit.contain,
                        alignment: Alignment.center,
                        semanticLabel: 'د شعر اصلي انځور',
                        errorBuilder: (context, error, stackTrace) =>
                            const SizedBox.shrink(),
                      ),
                    ),
                  ],
                  if (poem.isTranslation) ...[
                    const SizedBox(height: 18),
                    Text(
                      'اصلي شاعر: ${poem.originalAuthor ?? '—'}\n'
                      'پښتو ژباړه: ${poem.translator ?? '—'}',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: colors.muted, height: 1.6),
                    ),
                  ],
                  if (ownerPreviewMode) ...[
                    const SizedBox(height: 14),
                    Center(
                      child: Text(
                        '${poem.isActive ? 'خپور' : 'مسوده'} • '
                        '${poem.isFreeSample ? 'وړيا' : 'تړلی'}'
                        '${poem.isTranslation ? ' • ژباړه' : ''}',
                        key: const Key('owner-preview-reader-status'),
                        style: TextStyle(color: colors.muted),
                      ),
                    ),
                  ],
                  if (poem.hasPlayableAudio) ...[
                    const SizedBox(height: 24),
                    _AudioPlayerPanel(
                      poem: poem,
                      controller: audioController,
                      colors: colors,
                    ),
                  ],
                  const SizedBox(height: 28),
                  if (!ownerPreviewMode && poem.isFreeSample && !poem.locked)
                    Text(
                      'Sample — نمونه',
                      key: const Key('sample-label'),
                      textAlign: TextAlign.center,
                      style: TextStyle(color: colors.muted),
                    ),
                  if (!ownerPreviewMode && (poem.locked || poem.hasMore))
                    _LockedNotice(color: colors.foreground),
                  PoetryText(
                    text: poem.readableText,
                    style: TextStyle(
                      color: colors.foreground,
                      fontFamily: settings.fontFamily,
                      fontSize: settings.fontSize,
                      height: 2.2,
                    ),
                  ),
                  if (poem.sourceDatePlace case final datePlace?) ...[
                    const SizedBox(height: 24),
                    Text(
                      datePlace,
                      key: const Key('poem-date-place'),
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: colors.muted,
                        fontSize: settings.fontSize > 16
                            ? settings.fontSize - 3
                            : 13,
                        height: 1.5,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ),
      ),
    ),
  );
}

class _AudioPlayerPanel extends StatelessWidget {
  const _AudioPlayerPanel({
    required this.poem,
    required this.controller,
    required this.colors,
  });

  final PoemDetail poem;
  final AudioPlaybackController controller;
  final _ReaderColors colors;

  @override
  Widget build(BuildContext context) => AnimatedBuilder(
    animation: controller,
    builder: (context, _) {
      final active = controller.activePoemId == poem.id;
      final state = active ? controller.state : AudioControlState.idle;
      final position = active ? controller.position : Duration.zero;
      final duration = active
          ? controller.duration
          : poem.audioDurationSeconds == null
          ? null
          : Duration(seconds: poem.audioDurationSeconds!);
      final maxMilliseconds = (duration?.inMilliseconds ?? 0).clamp(1, 1 << 31);
      final positionMilliseconds = position.inMilliseconds.clamp(
        0,
        maxMilliseconds,
      );
      final loading =
          state == AudioControlState.loading ||
          state == AudioControlState.buffering;
      final playing = state == AudioControlState.playing;

      return Semantics(
        container: true,
        label: 'د شعر غږ',
        child: DecoratedBox(
          decoration: BoxDecoration(
            border: Border.all(color: colors.muted.withValues(alpha: 0.35)),
            borderRadius: BorderRadius.circular(14),
          ),
          child: Padding(
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  children: [
                    Icon(Icons.graphic_eq_rounded, color: colors.muted),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        poem.audioLabel ?? 'د شعر غږ',
                        style: TextStyle(color: colors.muted),
                      ),
                    ),
                  ],
                ),
                if (state == AudioControlState.error) ...[
                  const SizedBox(height: 10),
                  Text(
                    controller.errorMessage ?? 'غږ ونه غږېد.',
                    key: const Key('audio-error'),
                    style: TextStyle(color: colors.foreground),
                  ),
                  Align(
                    alignment: AlignmentDirectional.centerStart,
                    child: TextButton.icon(
                      onPressed: () => controller.retry(poem),
                      icon: const Icon(Icons.refresh_rounded),
                      label: const Text('بيا هڅه وکړئ'),
                    ),
                  ),
                ] else ...[
                  Slider(
                    key: const Key('audio-seek-slider'),
                    min: 0,
                    max: maxMilliseconds.toDouble(),
                    value: positionMilliseconds.toDouble(),
                    secondaryTrackValue: active
                        ? controller.bufferedPosition.inMilliseconds
                              .clamp(0, maxMilliseconds)
                              .toDouble()
                        : 0,
                    onChanged: duration == null
                        ? null
                        : (value) => controller.seek(
                            Duration(milliseconds: value.round()),
                          ),
                  ),
                  Row(
                    children: [
                      IconButton.filledTonal(
                        key: const Key('audio-play-pause'),
                        tooltip: playing ? 'تم' : 'غږول',
                        onPressed: loading
                            ? null
                            : () => controller.toggle(poem),
                        icon: loading
                            ? const SizedBox.square(
                                dimension: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                ),
                              )
                            : Icon(
                                playing
                                    ? Icons.pause_rounded
                                    : Icons.play_arrow_rounded,
                              ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          '${_durationText(position)} / ${_durationText(duration)}',
                          key: const Key('audio-time'),
                          textDirection: TextDirection.ltr,
                          style: TextStyle(color: colors.muted),
                        ),
                      ),
                      if (active && controller.cacheProgress != null)
                        Text(
                          controller.cacheProgress! >= 1
                              ? 'ساتل شوی'
                              : '${(controller.cacheProgress! * 100).round()}٪',
                          key: const Key('audio-cache-status'),
                          style: TextStyle(color: colors.muted),
                        ),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ),
      );
    },
  );

  static String _durationText(Duration? duration) {
    if (duration == null) return '--:--';
    final minutes = duration.inMinutes;
    final seconds = duration.inSeconds.remainder(60).toString().padLeft(2, '0');
    return '$minutes:$seconds';
  }
}

class _LockedNotice extends StatelessWidget {
  const _LockedNotice({required this.color});
  final Color color;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 18),
    child: Text(
      'دا برخه د بشپړ کتاب برخه ده.\nComing soon — ډېر ژر',
      key: const Key('book-coming-soon'),
      textAlign: TextAlign.center,
      style: TextStyle(color: color, height: 1.6),
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
