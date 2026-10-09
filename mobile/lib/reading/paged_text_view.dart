import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../settings/reader_palette_colors.dart';
import 'text_pages.dart';

class PagedTextView extends StatefulWidget {
  const PagedTextView({
    required this.source,
    required this.pages,
    required this.style,
    required this.direction,
    required this.colors,
    required this.details,
    required this.initialOffset,
    required this.initialDetails,
    required this.onLocation,
    required this.onContents,
    required this.allowTurn,
    required this.footerLimit,
    this.previousItem,
    this.nextItem,
    this.boundary,
    super.key,
  });
  final double footerLimit;
  final String source;
  final List<TextPage> pages;
  final TextStyle style;
  final TextDirection direction;
  final ReaderPaletteColors colors;
  final Widget details;
  final int initialOffset;
  final bool initialDetails;
  final void Function(int offset, bool details) onLocation;
  final VoidCallback onContents;
  final bool Function() allowTurn;
  final VoidCallback? previousItem, nextItem;
  final String? boundary;
  @override
  State<PagedTextView> createState() => _PagedTextViewState();
}

class _PagedTextViewState extends State<PagedTextView> {
  late int index = widget.initialDetails || widget.pages.isEmpty
      ? 0
      : TextPages.containing(widget.pages, widget.initialOffset) + 1;
  late final controller = PageController(initialPage: index);
  double overscroll = 0;
  bool edgeTurned = false;
  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }

  void turn(int delta) {
    if (!widget.allowTurn()) return;
    final next = index + delta;
    if (next < 0) {
      widget.previousItem?.call();
    } else if (next > widget.pages.length) {
      widget.nextItem?.call();
    } else {
      controller.animateToPage(
        next,
        duration: const Duration(milliseconds: 180),
        curve: Curves.easeOut,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final english = AppStrings.of(context).isEnglish;
    return Directionality(
      textDirection: widget.direction,
      child: Column(
        children: [
          Expanded(
            child: NotificationListener<ScrollNotification>(
              onNotification: (notification) {
                if (notification.metrics.axis != Axis.horizontal) return false;
                if (notification is ScrollStartNotification) {
                  overscroll = 0;
                  edgeTurned = false;
                }
                if (notification is OverscrollNotification &&
                    notification.dragDetails != null &&
                    !edgeTurned) {
                  overscroll += notification.overscroll;
                  if ((overscroll > 36 && index == widget.pages.length) ||
                      (overscroll < -36 && index == 0)) {
                    edgeTurned = true;
                    turn(overscroll > 0 ? 1 : -1);
                  }
                }
                return false;
              },
              child: PageView.builder(
                key: const Key('book-pages'),
                controller: controller,
                physics: const PageScrollPhysics(
                  parent: AlwaysScrollableScrollPhysics(
                    parent: ClampingScrollPhysics(),
                  ),
                ),
                itemCount: widget.pages.length + 1,
                onPageChanged: (page) {
                  if (!widget.allowTurn()) return;
                  setState(() => index = page);
                  widget.onLocation(
                    page == 0 ? 0 : widget.pages[page - 1].start,
                    page == 0,
                  );
                },
                itemBuilder: (context, page) => page == 0
                    ? widget.details
                    : Padding(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 20,
                          vertical: 16,
                        ),
                        child: _TextRangePage(
                          source: widget.source,
                          range: widget.pages[page - 1],
                          style: widget.style,
                          direction: widget.direction,
                          initialOffset: index == page
                              ? widget.initialOffset
                              : widget.pages[page - 1].start,
                          onLocation: (offset) {
                            if (index == page && widget.allowTurn()) {
                              widget.onLocation(offset, false);
                            }
                          },
                        ),
                      ),
              ),
            ),
          ),
          ConstrainedBox(
            constraints: BoxConstraints(maxHeight: widget.footerLimit),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (widget.boundary != null)
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 20),
                      child: Text(
                        widget.boundary!,
                        key: const Key('reading-boundary'),
                        style: TextStyle(
                          color: widget.colors.muted,
                          fontFamily: 'Vazirmatn',
                          fontSize: 14,
                          height: 1.5,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 4,
                    ),
                    child: Row(
                      children: [
                        IconButton(
                          key: const Key('page-previous'),
                          constraints: const BoxConstraints(
                            minWidth: 48,
                            minHeight: 48,
                          ),
                          tooltip: english ? 'Previous page' : 'مخکینۍ پاڼه',
                          color: widget.colors.foreground,
                          onPressed: index > 0 || widget.previousItem != null
                              ? () => turn(-1)
                              : null,
                          icon: Icon(
                            widget.direction == TextDirection.rtl
                                ? Icons.chevron_right
                                : Icons.chevron_left,
                          ),
                        ),
                        Expanded(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(
                                index == 0
                                    ? (english
                                          ? 'Book details'
                                          : 'د کتاب په اړه')
                                    : '$index / ${widget.pages.length}',
                                key: const Key('item-page-number'),
                                textDirection: index == 0
                                    ? widget.direction
                                    : TextDirection.ltr,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                  color: widget.colors.muted,
                                  fontFamily: 'Vazirmatn',
                                  fontSize: 13,
                                  height: 1.5,
                                ),
                              ),
                              TextButton(
                                key: const Key('reader-contents'),
                                onPressed: widget.onContents,
                                style: TextButton.styleFrom(
                                  foregroundColor: widget.colors.foreground,
                                  minimumSize: const Size(48, 48),
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 8,
                                    vertical: 8,
                                  ),
                                ),
                                child: Text(
                                  AppStrings.of(context).contents,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
                                    fontFamily: 'Vazirmatn',
                                    fontSize: 14,
                                    height: 1.5,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        IconButton(
                          key: const Key('page-next'),
                          constraints: const BoxConstraints(
                            minWidth: 48,
                            minHeight: 48,
                          ),
                          tooltip: english ? 'Next page' : 'بله پاڼه',
                          color: widget.colors.foreground,
                          onPressed:
                              index < widget.pages.length ||
                                  widget.nextItem != null
                              ? () => turn(1)
                              : null,
                          icon: Icon(
                            widget.direction == TextDirection.rtl
                                ? Icons.chevron_left
                                : Icons.chevron_right,
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
    );
  }
}

// A very tall line/stanza can pan vertically without losing its stable text
// location. Normal measured pages also retain this fallback for font metrics.
class _TextRangePage extends StatefulWidget {
  const _TextRangePage({
    required this.source,
    required this.range,
    required this.style,
    required this.direction,
    required this.initialOffset,
    required this.onLocation,
  });
  final String source;
  final TextPage range;
  final TextStyle style;
  final TextDirection direction;
  final int initialOffset;
  final ValueChanged<int> onLocation;
  @override
  State<_TextRangePage> createState() => _TextRangePageState();
}

class _TextRangePageState extends State<_TextRangePage> {
  final scroll = ScrollController();
  bool restoring = false;
  double width = 1;
  TextPainter painter() => TextPainter(
    text: TextSpan(text: widget.range.text(widget.source), style: widget.style),
    textDirection: widget.direction,
    textScaler: MediaQuery.textScalerOf(context),
  )..layout(maxWidth: width);
  @override
  void initState() {
    super.initState();
    scroll.addListener(moved);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted || !scroll.hasClients) return;
      final local = (widget.initialOffset - widget.range.start).clamp(
        0,
        widget.range.end - widget.range.start,
      );
      if (local == 0) return;
      final text = painter();
      final y = text
          .getOffsetForCaret(TextPosition(offset: local), Rect.zero)
          .dy;
      text.dispose();
      restoring = true;
      scroll.jumpTo(y.clamp(0, scroll.position.maxScrollExtent));
      restoring = false;
    });
  }

  void moved() {
    if (restoring || !mounted || !scroll.hasClients) return;
    final text = painter();
    final offset = text
        .getPositionForOffset(
          Offset(
            widget.direction == TextDirection.rtl ? width : 0,
            scroll.offset,
          ),
        )
        .offset;
    final start = text.getLineBoundary(TextPosition(offset: offset)).start;
    text.dispose();
    widget.onLocation(widget.range.start + start);
  }

  @override
  void dispose() {
    scroll.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => LayoutBuilder(
    builder: (context, constraints) {
      width = constraints.maxWidth.clamp(1, 720);
      return SingleChildScrollView(
        controller: scroll,
        key: ValueKey('text-page-${widget.range.start}'),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 720),
            child: SizedBox(
              width: double.infinity,
              child: SelectableText(
                widget.range.text(widget.source),
                key: ValueKey('page-text-${widget.range.start}'),
                textDirection: widget.direction,
                textAlign: TextAlign.start,
                style: widget.style,
              ),
            ),
          ),
        ),
      );
    },
  );
}
