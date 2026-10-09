import '../widgets/shelf_assets.dart';

import 'dart:io';
import 'dart:async';

import '../accounts/account_controller.dart';
import '../purchases/library_controller.dart';
import '../models/poetry_collection.dart';
import '../reading/reading_position.dart';
import '../reading/text_pages.dart';
import '../reading/paged_text_view.dart';

import '../services/api_config.dart';

import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../settings/reader_palette_colors.dart';

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
    this.collection,
    this.contents = const [],
    this.ownedMode = false,
    this.resume = false,
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
  final PoetryCollection? collection;
  final List<PoemSummary> contents;
  final bool ownedMode, resume;
  final AudioPlaybackController? audioController;
  final EntitlementController? entitlements;
  final bool ownerPreviewMode;

  @override
  State<PoemReaderScreen> createState() => _PoemReaderScreenState();
}

class _PoemReaderScreenState extends State<PoemReaderScreen>
    with WidgetsBindingObserver {
  late Future<PoemDetail> _future;
  PoemDetail? _loadedPoem;
  late int _item;
  int _epoch = 0;
  bool _initialized = false,
      _denied = false,
      _suspended = false,
      _changed = false,
      _saveFailed = false;
  AccountController? _accounts;
  LibraryController? _library;
  (int?, String?)? _identity;
  String? _positionKey;
  ReadingPosition? _anchor;
  final _scroll = ScrollController();
  final _textKey = GlobalKey(), _clipKey = GlobalKey();
  Timer? _saveTimer, _leaseTimer;
  Object? _scrollShape;
  bool _restoring = false;

  TextDirection get _direction => widget.collection?.language == 'en'
      ? TextDirection.ltr
      : TextDirection.rtl;
  bool get _valid =>
      !_denied &&
      !_suspended &&
      _identity == (_accounts?.user?.id, _accounts?.readerToken) &&
      (!widget.ownedMode ||
          (_library != null &&
              _library!.readerId == _identity?.$1 &&
              _library!.owns(widget.collection?.id) &&
              _library!.offlineValid));
  bool _canEnter(PoemSummary item) =>
      widget.ownerPreviewMode ||
      widget.ownedMode ||
      (item.isFreeSample && !item.locked);

  @override
  void initState() {
    super.initState();
    _item = widget.poemId;
    _scroll.addListener(_scrollChanged);
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final accounts = AccountScope.of(context);
    final library = LibraryScope.of(context);
    if (!_initialized) {
      _initialized = true;
      _accounts = accounts;
      _library = library;
      _identity = (accounts?.user?.id, accounts?.readerToken);
      if (widget.ownedMode) library?.stopReading.add(_deny);
      final book = widget.collection;
      if (book != null) {
        _positionKey = widget.settings.positions.key(
          environment:
              '${apiBaseUri()}${widget.ownerPreviewMode ? '/owner-preview' : ''}',
          account: accounts?.user?.id.toString() ?? 'guest',
          book: book.id?.toString() ?? book.slug,
        );
        final saved = widget.resume
            ? widget.settings.positions.read(_positionKey!)
            : null;
        if (saved != null &&
            widget.contents.any((s) => s.id == saved.item && _canEnter(s))) {
          _item = saved.item;
          _anchor = saved;
        }
      }
      _future = _load();
    } else if ((accounts != _accounts || library != _library || !_valid) &&
        !_suspended) {
      // This dependency update is itself rebuilding the reader; no setState
      // during the inherited notifier's build is required.
      _deny(rebuild: false);
    }
    _leaseTimer?.cancel();
    if (widget.ownedMode && _valid && library?.validUntil != null) {
      final remaining = library!.validUntil!.difference(library.clock());
      _leaseTimer = Timer(
        remaining.isNegative ? Duration.zero : remaining,
        _deny,
      );
    }
  }

  void _deny({bool rebuild = true}) {
    if (!mounted || _denied) return;
    ++_epoch;
    _denied = true;
    _loadedPoem = null;
    _anchor = null;
    _saveTimer?.cancel();
    if (rebuild) setState(() {});
  }

  Future<PoemDetail> _load({
    bool refreshEntitlement = false,
    bool last = false,
  }) async {
    if (!_valid) throw StateError('Reader access changed');
    if (widget.contents.isNotEmpty &&
        !widget.contents.any((s) => s.id == _item && _canEnter(s))) {
      throw StateError('Section unavailable');
    }
    final epoch = ++_epoch;
    final poem = await widget.repository.loadPoem(
      _item,
      widget.contentVersion,
      refreshEntitlement: refreshEntitlement,
    );
    if (!mounted || epoch != _epoch || !_valid) {
      throw StateError('Reader access changed');
    }
    if (poem.id != _item ||
        (widget.collection != null &&
            poem.collectionSlug != widget.collection!.slug)) {
      throw const FormatException('Wrong book section');
    }
    if (widget.collection != null &&
        !widget.ownedMode &&
        !widget.ownerPreviewMode &&
        !poem.locked &&
        !poem.isFreeSample) {
      throw StateError('Section unavailable');
    }
    final fingerprint = ReadingPosition.fingerprintOf(poem.readableText);
    var anchor = _anchor;
    _changed =
        anchor != null &&
        anchor.item == _item &&
        anchor.fingerprint != fingerprint;
    if (poem.locked) {
      anchor = null;
    } else if (last) {
      anchor = ReadingPosition(
        item: _item,
        offset: poem.readableText.length,
        fingerprint: fingerprint,
      );
    } else if (anchor == null ||
        anchor.item != _item ||
        _changed ||
        anchor.offset > poem.readableText.length) {
      anchor = ReadingPosition(
        item: _item,
        offset: 0,
        fingerprint: fingerprint,
        details: !_changed,
      );
    }
    _anchor = anchor;
    _scrollShape = null;
    setState(() => _loadedPoem = poem);
    unawaited(_save());
    return poem;
  }

  Future<void> _save() async {
    final key = _positionKey, anchor = _anchor;
    if (key == null || anchor == null || !_valid) return;
    try {
      final success = await widget.settings.positions.save(key, anchor);
      if (!success && mounted && _valid) setState(() => _saveFailed = true);
    } catch (_) {
      if (mounted && _valid) setState(() => _saveFailed = true);
    }
  }

  void _location(int offset, bool details) {
    final poem = _loadedPoem;
    if (!_valid) {
      _deny();
      return;
    }
    if (poem == null || poem.locked) return;
    _anchor = ReadingPosition(
      item: poem.id,
      offset: offset,
      fingerprint: ReadingPosition.fingerprintOf(poem.readableText),
      details: details,
    );
    _saveTimer?.cancel();
    _saveTimer = Timer(
      const Duration(milliseconds: 200),
      () => unawaited(_save()),
    );
  }

  TextPainter _painter(PoemDetail poem, double width) => TextPainter(
    text: TextSpan(
      text: poem.readableText,
      style: TextStyle(
        fontFamily: widget.settings.fontFamily,
        fontSize: widget.settings.fontSize,
        height: 2.2,
      ),
    ),
    textDirection: _direction,
    textScaler: MediaQuery.textScalerOf(context),
  )..layout(maxWidth: width);

  void _scrollChanged() {
    if (_restoring ||
        widget.settings.readingMode != ReadingMode.scroll ||
        !_valid) {
      return;
    }
    final poem = _loadedPoem;
    final text = _textKey.currentContext?.findRenderObject();
    final clip = _clipKey.currentContext?.findRenderObject();
    if (poem == null ||
        poem.locked ||
        text is! RenderBox ||
        clip is! RenderBox ||
        !text.hasSize) {
      return;
    }
    final y =
        clip.localToGlobal(Offset.zero).dy - text.localToGlobal(Offset.zero).dy;
    if (y < 0) {
      _location(0, true);
      return;
    }
    final painter = _painter(poem, text.size.width);
    final offset = painter
        .getPositionForOffset(
          Offset(_direction == TextDirection.rtl ? text.size.width : 0, y),
        )
        .offset;
    _location(
      painter.getLineBoundary(TextPosition(offset: offset)).start,
      false,
    );
    painter.dispose();
  }

  void _restoreScroll(Object shape) {
    if (_scrollShape == shape) return;
    _scrollShape = shape;
    final epoch = _epoch;
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted ||
          epoch != _epoch ||
          !_valid ||
          widget.settings.readingMode != ReadingMode.scroll) {
        return;
      }
      final poem = _loadedPoem, anchor = _anchor;
      final text = _textKey.currentContext?.findRenderObject(),
          clip = _clipKey.currentContext?.findRenderObject();
      if (poem == null ||
          anchor == null ||
          text is! RenderBox ||
          clip is! RenderBox ||
          !_scroll.hasClients) {
        return;
      }
      _restoring = true;
      var target = 0.0;
      if (!anchor.details) {
        final painter = _painter(poem, text.size.width);
        target =
            _scroll.offset +
            text.localToGlobal(Offset.zero).dy -
            clip.localToGlobal(Offset.zero).dy +
            painter
                .getOffsetForCaret(
                  TextPosition(offset: anchor.offset),
                  Rect.zero,
                )
                .dy;
        painter.dispose();
      }
      _scroll.jumpTo(target.clamp(0, _scroll.position.maxScrollExtent));
      _restoring = false;
    });
  }

  PoemSummary? _adjacent(int delta) {
    final index = widget.contents.indexWhere((s) => s.id == _item);
    final next = index + delta;
    if (index < 0 || next < 0 || next >= widget.contents.length) return null;
    return widget.contents[next];
  }

  void _move(int delta) {
    if (!_valid) {
      _deny();
      return;
    }
    final next = _adjacent(delta);
    if (next == null || !_canEnter(next) || _loadedPoem?.locked != false) {
      return;
    }
    unawaited(_save());
    setState(() {
      _item = next.id;
      _loadedPoem = null;
      _anchor = null;
      _future = _load(refreshEntitlement: true, last: delta < 0);
    });
  }

  bool _allowTurn() {
    if (_valid) return true;
    _deny();
    return false;
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.paused) {
      unawaited(_save());
      ++_epoch;
      _suspended = true;
      _loadedPoem = null;
      if (mounted) setState(() {});
    } else if (state == AppLifecycleState.resumed && _suspended) {
      _suspended = false;
      if (!_valid) {
        _deny();
        return;
      }
      setState(() => _future = _load(refreshEntitlement: true));
    }
  }

  @override
  void dispose() {
    unawaited(_save());
    _saveTimer?.cancel();
    _leaseTimer?.cancel();
    _library?.stopReading.remove(_deny);
    WidgetsBinding.instance.removeObserver(this);
    _scroll.dispose();
    super.dispose();
  }

  Widget _body(PoemDetail poem, ReaderPaletteColors colors) => LayoutBuilder(
    builder: (context, constraints) {
      final shape = (
        constraints.maxWidth,
        constraints.maxHeight,
        widget.settings.fontFamily,
        widget.settings.fontSize,
        MediaQuery.textScalerOf(context),
        widget.settings.readingMode,
      );
      final details = _PoemBody(
        poem: poem,
        settings: widget.settings,
        colors: colors,
        audioController: widget.audioController ?? InactiveAudioController(),
        ownerPreviewMode: widget.ownerPreviewMode,
        collectionTitle: widget.collectionTitle,
        direction: _direction,
        showText: widget.settings.readingMode == ReadingMode.scroll,
        scrollController: widget.settings.readingMode == ReadingMode.scroll
            ? _scroll
            : null,
        textKey: _textKey,
      );
      if (widget.settings.readingMode == ReadingMode.scroll) {
        _restoreScroll(shape);
        return details;
      }
      final next = _adjacent(1), previous = _adjacent(-1);
      final boundary =
          poem.hasMore && !widget.ownedMode && !widget.ownerPreviewMode
          ? (AppStrings.of(context).choose(
              'Sample — full section locked',
              'نمونه — بشپړه برخه تړلې ده',
              'نمونه — بخش کامل قفل است',
            ))
          : next != null && !_canEnter(next)
          ? (AppStrings.of(context).choose(
              'Next section unavailable',
              'بله برخه اوس نه شي لوستل کېدای',
              'بخش بعدی در دسترس نیست',
            ))
          : null;
      final scale = MediaQuery.textScalerOf(context);
      final boundaryPainter =
          TextPainter(
            text: TextSpan(
              text: boundary ?? '',
              style: const TextStyle(
                fontFamily: 'Vazirmatn',
                fontSize: 14,
                height: 1.5,
              ),
            ),
            textDirection: _direction,
            textScaler: scale,
          )..layout(
            maxWidth: (constraints.maxWidth - 40).clamp(1, double.infinity),
          );
      final footer =
          scale.scale(13) * 1.5 +
          (scale.scale(14) * 1.5 + 16).clamp(48, double.infinity) +
          8 +
          (boundary == null ? 0 : boundaryPainter.height);
      boundaryPainter.dispose();
      final style = TextStyle(
        fontFamily: widget.settings.fontFamily,
        fontSize: widget.settings.fontSize,
        color: colors.foreground,
        height: 2.2,
      );
      final pages = TextPages.layout(
        source: poem.readableText,
        style: style,
        textScaler: scale,
        direction: _direction,
        width: (constraints.maxWidth - 40).clamp(1, 720),
        height:
            (constraints.maxHeight -
                    footer.clamp(0, constraints.maxHeight * .70) -
                    40)
                .clamp(1, double.infinity),
      );
      return PagedTextView(
        key: ValueKey((_item, _epoch, shape, boundary)),
        source: poem.readableText,
        pages: pages,
        style: style,
        direction: _direction,
        colors: colors,
        details: details,
        initialOffset: _anchor?.offset ?? 0,
        initialDetails: _anchor?.details ?? true,
        onLocation: _location,
        allowTurn: _allowTurn,
        footerLimit: constraints.maxHeight * .70,
        previousItem: previous != null && _canEnter(previous) && !poem.locked
            ? () => _move(-1)
            : null,
        nextItem: next != null && _canEnter(next) && !poem.locked
            ? () => _move(1)
            : null,
        boundary: boundary,
        onContents: () {
          unawaited(_save());
          Navigator.maybePop(context);
        },
      );
    },
  );

  @override
  Widget build(BuildContext context) => AnimatedBuilder(
    animation: widget.settings,
    builder: (context, _) {
      final colors = ReaderPaletteColors.forPalette(widget.settings.palette);
      return Scaffold(
        backgroundColor: colors.background,
        appBar: AppBar(
          key: const Key('reader-app-bar'),
          backgroundColor: colors.background,
          foregroundColor: colors.foreground,
          surfaceTintColor: colors.background,
          shadowColor: Colors.transparent,
          scrolledUnderElevation: 0,
          title: _loadedPoem?.isFreeSample == true
              ? Text(
                  AppStrings.of(context).sample,
                  style: TextStyle(fontSize: 13, color: colors.muted),
                )
              : null,
          actions: [
            IconButton(
              tooltip: AppStrings.of(context)
                  .choose('Share', 'شریکول', 'اشتراک‌گذاری'),
              key: const Key('reader-share'),
              onPressed:
                  !_valid ||
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
              icon: ShelfActionIcon(
                'share',
                dark: widget.settings.palette == ReaderPalette.dark,
              ),
            ),
            ShelfFontButton(
              key: const Key('reader-font-chooser'),
              onPressed: () => showReadingPreferences(context, widget.settings),
              dark: widget.settings.palette == ReaderPalette.dark,
            ),
          ],
        ),
        bottomNavigationBar:
            widget.settings.readingMode == ReadingMode.pages &&
                _valid &&
                _loadedPoem != null
            ? null
            : SafeArea(
                top: false,
                child: ColoredBox(
                  color: colors.foreground.withValues(alpha: .055),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 20,
                      vertical: 12,
                    ),
                    child: TextButton(
                      key: const Key('reader-contents'),
                      style: TextButton.styleFrom(
                        foregroundColor: colors.foreground,
                      ),
                      onPressed: () {
                        unawaited(_save());
                        Navigator.maybePop(context);
                      },
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          ShelfActionIcon(
                            "contents",
                            dark: widget.settings.palette == ReaderPalette.dark,
                          ),
                          const SizedBox(width: 8),
                          Text(AppStrings.of(context).contents),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
        body: SafeArea(
          top: false,
          child: Column(
            children: [
              if (_changed || _saveFailed)
                Padding(
                  padding: const EdgeInsets.all(8),
                  child: Text(
                    _saveFailed
                        ? (AppStrings.of(context).choose(
                            'Reading position could not be saved.',
                            'د لوست ځای خوندي نه شو.',
                            'موقعیت خواندن ذخیره نشد.',
                          ))
                        : (AppStrings.of(context).choose(
                            'Book text changed. This section starts again.',
                            'متن بدل شوی؛ دا برخه له پیله لوستل کېږي.',
                            'متن کتاب تغییر کرده است. این بخش از آغاز خوانده می‌شود.',
                          )),
                    key: const Key('reading-position-notice'),
                    style: TextStyle(color: colors.foreground),
                  ),
                ),
              Expanded(
                child: SizedBox.expand(
                  key: _clipKey,
                  child: ClipRect(
                    key: const Key('reader-body-clip'),
                    child: !_valid
                        ? Center(
                            child: Text(
                              AppStrings.of(context).choose(
                                'Return to Contents and check book access.',
                                'لړلیک ته ستانه شئ او د کتاب لاسرسی وګورئ.',
                                'به فهرست برگردید و دسترسی کتاب را بررسی کنید.',
                              ),
                              key: const Key('reader-access-changed'),
                              style: TextStyle(color: colors.foreground),
                            ),
                          )
                        : FutureBuilder<PoemDetail>(
                            future: _future,
                            builder: (context, snapshot) {
                              if (snapshot.connectionState !=
                                  ConnectionState.done) {
                                return Center(
                                  child: CircularProgressIndicator(
                                    color: colors.foreground,
                                  ),
                                );
                              }
                              if (snapshot.hasError || !snapshot.hasData) {
                                return _ReaderError(
                                  color: colors.foreground,
                                  onRetry: () => setState(() {
                                    _loadedPoem = null;
                                    _future = _load(refreshEntitlement: true);
                                  }),
                                );
                              }
                              return _body(snapshot.requireData, colors);
                            },
                          ),
                  ),
                ),
              ),
            ],
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
    this.collectionTitle,
    this.showText = true,
    this.direction = TextDirection.rtl,
    this.scrollController,
    this.textKey,
  });

  final PoemDetail poem;
  final ReaderSettings settings;
  final ReaderPaletteColors colors;
  final AudioPlaybackController audioController;
  final bool ownerPreviewMode;
  final String? collectionTitle;
  final bool showText;
  final TextDirection direction;
  final ScrollController? scrollController;
  final GlobalKey? textKey;

  @override
  Widget build(BuildContext context) => Directionality(
    textDirection: direction,
    child: SafeArea(
      top: false,
      child: SelectionArea(
        child: SingleChildScrollView(
          key: const Key('poem-scroll-view'),
          controller: scrollController,
          clipBehavior: Clip.hardEdge,
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 40),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 720),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  if (collectionTitle != null) ...[
                    Text(
                      collectionTitle!,
                      textAlign: direction == TextDirection.rtl
                          ? TextAlign.right
                          : TextAlign.left,
                      style: TextStyle(color: colors.muted, fontSize: 13),
                    ),
                    const SizedBox(height: 16),
                    Divider(color: colors.muted.withValues(alpha: .25)),
                    const SizedBox(height: 16),
                  ],
                  if (!poem.isUntitled)
                    Text(
                      poem.title!,
                      key: const Key('poem-title'),
                      textAlign: direction == TextDirection.rtl
                          ? TextAlign.right
                          : TextAlign.left,
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
                      child: Image(
                        image: poem.artworkUrl?.scheme == 'file'
                            ? FileImage(File.fromUri(poem.artworkUrl!))
                                  as ImageProvider
                            : NetworkImage(
                                poem.artworkUrl.toString(),
                                headers: shelfTestHeaders(poem.artworkUrl!),
                              ),
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
                      textAlign: direction == TextDirection.rtl
                          ? TextAlign.right
                          : TextAlign.left,
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
                  if (showText)
                    KeyedSubtree(
                      key: textKey,
                      child: PoetryText(
                        direction: direction,
                        text: poem.readableText,
                        style: TextStyle(
                          color: colors.foreground,
                          fontFamily: settings.fontFamily,
                          fontSize: settings.fontSize,
                          height: 2.2,
                        ),
                      ),
                    ),
                  if (poem.sourceDatePlace case final datePlace?) ...[
                    const SizedBox(height: 24),
                    Text(
                      datePlace,
                      key: const Key('poem-date-place'),
                      textAlign: direction == TextDirection.rtl
                          ? TextAlign.right
                          : TextAlign.left,
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
  final ReaderPaletteColors colors;

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
        label: AppStrings.of(context)
            .choose('Book audio', 'د شعر غږ', 'صدای کتاب'),
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
                    ShelfActionIcon(
                      'audio',
                      dark: colors.background.computeLuminance() < .3,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        poem.audioLabel ??
                            AppStrings.of(context)
                                .choose('Book audio', 'د شعر غږ', 'صدای کتاب'),
                        style: TextStyle(color: colors.muted),
                      ),
                    ),
                  ],
                ),
                if (state == AudioControlState.error) ...[
                  const SizedBox(height: 10),
                  Text(
                    AppStrings.of(context).choose(
                      'Could not play audio. Try again.',
                      controller.errorMessage ?? 'غږ ونه غږېد.',
                      'صدا پخش نشد. دوباره کوشش کنید.',
                    ),
                    key: const Key('audio-error'),
                    style: TextStyle(color: colors.foreground),
                  ),
                  Align(
                    alignment: AlignmentDirectional.centerStart,
                    child: TextButton.icon(
                      onPressed: () => controller.retry(poem),
                      icon: const ShelfActionIcon('refresh'),
                      label: Text(AppStrings.of(context).retry),
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
                        tooltip: playing
                            ? AppStrings.of(context)
                                  .choose('Pause', 'تم', 'توقف')
                            : AppStrings.of(context)
                                  .choose('Play', 'غږول', 'پخش'),
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
                              ? AppStrings.of(context)
                                    .choose('Saved', 'ساتل شوی', 'ذخیره‌شده')
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
        Text(AppStrings.of(context).error, style: TextStyle(color: color)),
        const SizedBox(height: 12),
        OutlinedButton(
          onPressed: onRetry,
          child: Text(AppStrings.of(context).retry),
        ),
      ],
    ),
  );
}
