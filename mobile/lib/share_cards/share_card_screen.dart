import '../widgets/shelf_assets.dart';

import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:path_provider/path_provider.dart';

import '../models/poem.dart';
import '../l10n/app_strings.dart';
import 'poem_card_widget.dart';
import 'share_card_files.dart';
import 'share_card_models.dart';
import 'share_card_output.dart';
import 'share_card_paginator.dart';
import 'share_card_renderer.dart';

class ShareCardScreen extends StatefulWidget {
  const ShareCardScreen({
    required this.poem,
    this.collectionTitle,
    this.fontFamily = 'Vazirmatn',
    this.output = const PlatformShareCardOutput(),
    this.renderer = const ShareCardRenderer(),
    this.files,
    super.key,
  });

  final PoemDetail poem;
  final String? collectionTitle;
  final String fontFamily;
  final ShareCardOutput output;
  final ShareCardRenderer renderer;
  final ShareCardFiles? files;

  @override
  State<ShareCardScreen> createState() => _ShareCardScreenState();
}

class _ShareCardScreenState extends State<ShareCardScreen> {
  ShareCardTheme _theme = ShareCardTheme.parchment;
  ShareCardScope _scope = ShareCardScope.selection;
  ShareLineSelection? _selection;
  bool _working = false;
  late String _fontFamily = widget.fontFamily;
  double _fontSize = 20;
  final PageController _previewController = PageController();
  final List<GlobalKey> _previewKeys = [];

  List<ShareablePoemLine> get _lines =>
      selectablePoemLines(widget.poem.readableText);

  String get _selectedText {
    final selection = _selection;
    return selection == null ? '' : selectedLineText(_lines, selection);
  }

  String get _cardText => _scope == ShareCardScope.accessiblePoem
      ? widget.poem.readableText
      : _selectedText;

  ShareCardRequest get _request => ShareCardRequest(
    poem: widget.poem,
    collectionTitle: widget.collectionTitle,
    scope: _scope,
    text: _cardText,
    theme: _theme,
    fontFamily: _fontFamily,
    fontSize: _fontSize,
  );

  List<ShareCardPage> get _pages => _cardText.trim().isEmpty
      ? const []
      : ShareCardPaginator(
          fontFamily: _fontFamily,
          fontSize: _fontSize,
        ).paginate(_cardText);

  @override
  void initState() {
    super.initState();
    if (_lines.isNotEmpty) _selection = const ShareLineSelection(0, 0);
  }

  @override
  void dispose() {
    _previewController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final pages = _pages;
    _syncPreviewKeys(pages.length);
    return Scaffold(
      appBar: AppBar(
        title: Text(
          AppStrings.of(context).choose('Share', 'شریکول', 'اشتراک‌گذاری'),
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
        actions: [
          IconButton(
            key: const Key('share-preview-expand'),
            tooltip: AppStrings.of(context).choose(
              'Expand preview',
              'مخکتنه لویه کړئ',
              'بزرگ کردن پیش‌نمایش',
            ),
            onPressed: _working || pages.isEmpty
                ? null
                : () => showDialog<void>(
                    context: context,
                    builder: (context) => Dialog.fullscreen(
                      child: Scaffold(
                        appBar: AppBar(
                          title: Text(
                            AppStrings.of(context).choose(
                              'Card preview',
                              'د کارت مخکتنه',
                              'پیش‌نمایش کارت',
                            ),
                          ),
                        ),
                        body: InteractiveViewer(
                          minScale: .3,
                          maxScale: 4,
                          child: Center(
                            child: FittedBox(
                              child: MediaQuery.withNoTextScaling(
                                child: PoemCardWidget(
                                  request: _request,
                                  page: pages.first,
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
            icon: const ShelfActionIcon('share-card'),
          ),
        ],
      ),
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) => Column(
            children: [
              if (pages.isNotEmpty)
                SizedBox(
                  height: constraints.maxHeight * .32,
                  child: PageView.builder(
                    key: const Key('card-preview'),
                    controller: _previewController,
                    itemCount: pages.length,
                    itemBuilder: (context, index) => Center(
                      child: FittedBox(
                        fit: BoxFit.scaleDown,
                        child: RepaintBoundary(
                          key: _previewKeys[index],
                          child: MediaQuery.withNoTextScaling(
                            child: PoemCardWidget(
                              request: _request,
                              page: pages[index],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                ),

              Expanded(
                child: ListView(
                  key: const Key('share-controls-scroll'),
                  padding: const EdgeInsets.fromLTRB(18, 8, 18, 28),
                  children: [
                    SegmentedButton<ShareCardScope>(
                      key: const Key('card-scope'),
                      segments: [
                        ButtonSegment(
                          value: ShareCardScope.selection,
                          label: Text(
                            AppStrings.of(context).choose(
                              'Selected lines',
                              'ټاکلې کرښې',
                              'خط‌های انتخاب‌شده',
                            ),
                          ),
                        ),
                        ButtonSegment(
                          value: ShareCardScope.accessiblePoem,
                          label: Text(
                            widget.poem.hasMore || widget.poem.locked
                                ? AppStrings.of(context).sample
                                : AppStrings.of(context).choose(
                                    'Full accessible text',
                                    'بشپړ متن',
                                    'متن کامل قابل دسترس',
                                  ),
                          ),
                        ),
                      ],
                      selected: {_scope},
                      onSelectionChanged: _working
                          ? null
                          : (values) =>
                                _updatePreview(() => _scope = values.first),
                    ),
                    if (_scope == ShareCardScope.selection) ...[
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              AppStrings.of(context).choose(
                                'Choose 1 to 4 consecutive lines',
                                'له ۱ تر ۴ پرله‌پسې کرښې وټاکئ',
                                '۱ تا ۴ خط پی‌هم را انتخاب کنید',
                              ),
                            ),
                          ),
                          Text('${_selection?.count ?? 0}/۴'),
                        ],
                      ),
                      const SizedBox(height: 8),
                      DecoratedBox(
                        decoration: BoxDecoration(
                          border: Border.all(
                            color: Theme.of(context).dividerColor,
                          ),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Column(
                          children: List.generate(_lines.length, (index) {
                            final selected =
                                _selection != null &&
                                index >= _selection!.start &&
                                index <= _selection!.end;
                            return ListTile(
                              key: Key('share-line-$index'),
                              selected: selected,
                              dense: true,
                              leading: Icon(
                                selected
                                    ? Icons.check_circle_rounded
                                    : Icons.circle_outlined,
                              ),
                              title: Text(
                                _lines[index].text,
                                textDirection: TextDirection.rtl,
                                style: TextStyle(
                                  fontFamily: _fontFamily,
                                  height: 1.8,
                                ),
                              ),
                              onTap: _working
                                  ? null
                                  : () => _updatePreview(
                                      () => _selection = selectContiguousLine(
                                        current: _selection,
                                        tapped: index,
                                      ),
                                    ),
                            );
                          }),
                        ),
                      ),
                    ],
                    const SizedBox(height: 18),
                    Text(AppStrings.of(context).font),
                    DropdownButton<String>(
                      key: const Key('share-font'),
                      isExpanded: true,
                      value: _fontFamily,
                      items: [
                        DropdownMenuItem(
                          value: 'Vazirmatn',
                          child: Text(AppStrings.of(context).vazirmatnName),
                        ),
                        DropdownMenuItem(
                          value: 'ScheherazadeNew',
                          child: Text(AppStrings.of(context).scheherazadeName),
                        ),
                        DropdownMenuItem(
                          value: 'NotoNastaliqUrdu',
                          child: Text(AppStrings.of(context).nastaliqName),
                        ),
                      ],
                      onChanged: _working
                          ? null
                          : (value) =>
                                _updatePreview(() => _fontFamily = value!),
                    ),
                    Text(
                      '${AppStrings.of(context).fontSize}: ${_fontSize.round()}',
                    ),
                    Slider(
                      key: const Key('share-font-size'),
                      min: 16,
                      max: 32,
                      divisions: 16,
                      value: _fontSize,
                      label: '${_fontSize.round()}',
                      onChanged: _working
                          ? null
                          : (value) => _updatePreview(() => _fontSize = value),
                    ),
                    Text(
                      AppStrings.of(context)
                          .choose('Card colors', 'د کارت رنګ', 'رنگ کارت'),
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    const SizedBox(height: 8),
                    SegmentedButton<ShareCardTheme>(
                      key: const Key('card-theme'),
                      segments: [
                        ButtonSegment(
                          value: ShareCardTheme.parchment,
                          label: Text(AppStrings.of(context).sepiaPalette),
                        ),
                        ButtonSegment(
                          value: ShareCardTheme.dark,
                          label: Text(AppStrings.of(context).darkPalette),
                        ),
                        ButtonSegment(
                          value: ShareCardTheme.light,
                          label: Text(AppStrings.of(context).lightPalette),
                        ),
                      ],
                      selected: {_theme},
                      onSelectionChanged: _working
                          ? null
                          : (values) =>
                                _updatePreview(() => _theme = values.first),
                    ),
                    const SizedBox(height: 18),
                    LayoutBuilder(
                      builder: (context, buttons) {
                        final stacked =
                            buttons.maxWidth < 400 ||
                            MediaQuery.textScalerOf(context).scale(16) > 24;
                        final width = stacked
                            ? buttons.maxWidth
                            : (buttons.maxWidth - 12) / 2;
                        return Wrap(
                          spacing: 12,
                          runSpacing: 12,
                          children: [
                            SizedBox(
                              width: width,
                              child: OutlinedButton.icon(
                                key: const Key('save-cards'),
                                onPressed: _working || pages.isEmpty
                                    ? null
                                    : () => _export(save: true),
                                icon: const ShelfActionIcon('download'),
                                label: Text(
                                  AppStrings.of(context).choose(
                                    'Save to gallery',
                                    'په ګالرۍ کې ساتل',
                                    'ذخیره در گالری',
                                  ),
                                ),
                              ),
                            ),
                            SizedBox(
                              width: width,
                              child: FilledButton.icon(
                                key: const Key('share-cards'),
                                onPressed: _working || pages.isEmpty
                                    ? null
                                    : () => _export(save: false),
                                icon: _working
                                    ? const SizedBox.square(
                                        dimension: 18,
                                        child: CircularProgressIndicator(
                                          strokeWidth: 2,
                                        ),
                                      )
                                    : const ShelfActionIcon(
                                        'share',
                                        dark: true,
                                      ),
                                label: Text(
                                  AppStrings.of(
                                    context,
                                  ).choose('Share', 'شریکول', 'اشتراک‌گذاری'),
                                ),
                              ),
                            ),
                          ],
                        );
                      },
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _updatePreview(VoidCallback update) {
    if (_previewController.hasClients) _previewController.jumpToPage(0);
    setState(update);
  }

  Future<void> _export({required bool save}) async {
    setState(() => _working = true);
    ShareCardFiles? files;
    List<File> rendered = const [];
    try {
      files = widget.files ?? await _defaultFiles();
      await files.cleanStale(now: DateTime.now());
      if (!mounted) return;
      rendered = await widget.renderer.render(
        request: _request,
        pages: _pages,
        files: files,
        preparePage: _preparePreviewPage,
      );
      if (save) {
        try {
          await widget.output.save(rendered);
        } catch (error, stackTrace) {
          _debugOutputFailure(ShareCardExportStage.gallery, error, stackTrace);
          throw ShareCardExportException(
            ShareCardExportStage.gallery,
            'Gallery insertion failed',
            error,
          );
        }
      } else {
        try {
          await widget.output.share(rendered);
        } catch (error, stackTrace) {
          _debugOutputFailure(ShareCardExportStage.share, error, stackTrace);
          throw ShareCardExportException(
            ShareCardExportStage.share,
            'Android share failed',
            error,
          );
        }
      }
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            save
                ? AppStrings.of(context).choose(
                    '${rendered.length} cards saved.',
                    '${rendered.length} کارتونه وساتل شول.',
                    '${rendered.length} کارت ذخیره شد.',
                  )
                : AppStrings.of(context).choose(
                    'Cards ready to share.',
                    'کارتونه د شریکولو لپاره چمتو شول.',
                    'کارت‌ها برای اشتراک‌گذاری آماده شدند.',
                  ),
          ),
        ),
      );
    } catch (error) {
      if (!mounted) return;
      final message = error is ShareCardExportException
          ? switch (error.stage) {
              ShareCardExportStage.share => AppStrings.of(context).choose(
                'Could not share. Try again.',
                'کارت شريک نه شو. بيا هڅه وکړئ.',
                'اشتراک‌گذاری انجام نشد. دوباره کوشش کنید.',
              ),
              ShareCardExportStage.gallery => AppStrings.of(context).choose(
                'Could not save to gallery. Try again.',
                'کارت په ګالرۍ کې ونه ساتل شو. بيا هڅه وکړئ.',
                'کارت در گالری ذخیره نشد. دوباره کوشش کنید.',
              ),
              _ => AppStrings.of(context).choose(
                'Could not create card. Try again.',
                'کارت جوړ نه شو. بيا هڅه وکړئ.',
                'کارت ساخته نشد. دوباره کوشش کنید.',
              ),
            }
          : AppStrings.of(context).choose(
              'Could not create card. Try again.',
              'کارت جوړ نه شو. بيا هڅه وکړئ.',
              'کارت ساخته نشد. دوباره کوشش کنید.',
            );
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text(message)));
    } finally {
      if (save && files != null) await files.remove(rendered);
      if (mounted) setState(() => _working = false);
    }
  }

  void _syncPreviewKeys(int count) {
    while (_previewKeys.length < count) {
      _previewKeys.add(GlobalKey());
    }
    if (_previewKeys.length > count) {
      _previewKeys.removeRange(count, _previewKeys.length);
    }
  }

  Future<RenderRepaintBoundary?> _preparePreviewPage(int index) async {
    if (!mounted || index >= _previewKeys.length) return null;
    if (_previewController.hasClients) {
      _previewController.jumpToPage(index);
    }
    await WidgetsBinding.instance.endOfFrame;
    if (!mounted || index >= _previewKeys.length) return null;
    final renderObject = _previewKeys[index].currentContext?.findRenderObject();
    return renderObject is RenderRepaintBoundary ? renderObject : null;
  }

  void _debugOutputFailure(
    ShareCardExportStage stage,
    Object error,
    StackTrace stackTrace,
  ) {
    assert(() {
      debugPrint(
        '[P4 export] FAILURE stage=${stage.name} '
        'type=${error.runtimeType} error=$error\n$stackTrace',
      );
      return true;
    }());
  }

  Future<ShareCardFiles> _defaultFiles() async {
    final cache = await getTemporaryDirectory();
    return ShareCardFiles(Directory('${cache.path}/shelf_share_cards'));
  }
}
