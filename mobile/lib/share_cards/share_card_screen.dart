import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:path_provider/path_provider.dart';

import '../models/poem.dart';
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
    fontFamily: widget.fontFamily,
  );

  List<ShareCardPage> get _pages => _cardText.trim().isEmpty
      ? const []
      : ShareCardPaginator(fontFamily: widget.fontFamily).paginate(_cardText);

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
      appBar: AppBar(title: const Text('شريکول او ساتل')),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.fromLTRB(18, 8, 18, 28),
          children: [
            SegmentedButton<ShareCardScope>(
              key: const Key('card-scope'),
              segments: [
                const ButtonSegment(
                  value: ShareCardScope.selection,
                  label: Text('ټاکلې کرښې'),
                ),
                ButtonSegment(
                  value: ShareCardScope.accessiblePoem,
                  label: Text(
                    widget.poem.hasMore || widget.poem.locked
                        ? 'Sample — نمونه'
                        : 'بشپړ متن',
                  ),
                ),
              ],
              selected: {_scope},
              onSelectionChanged: (values) =>
                  setState(() => _scope = values.first),
            ),
            if (_scope == ShareCardScope.selection) ...[
              const SizedBox(height: 16),
              Row(
                children: [
                  const Expanded(child: Text('له ۱ تر ۴ پرله‌پسې کرښې وټاکئ')),
                  Text('${_selection?.count ?? 0}/۴'),
                ],
              ),
              const SizedBox(height: 8),
              DecoratedBox(
                decoration: BoxDecoration(
                  border: Border.all(color: Theme.of(context).dividerColor),
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
                          fontFamily: widget.fontFamily,
                          height: 1.8,
                        ),
                      ),
                      onTap: () => setState(
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
            Text('ډيزاين', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            SegmentedButton<ShareCardTheme>(
              key: const Key('card-theme'),
              segments: const [
                ButtonSegment(
                  value: ShareCardTheme.parchment,
                  label: Text('کاغذ'),
                ),
                ButtonSegment(value: ShareCardTheme.dark, label: Text('تياره')),
                ButtonSegment(
                  value: ShareCardTheme.light,
                  label: Text('روښانه'),
                ),
              ],
              selected: {_theme},
              onSelectionChanged: (values) =>
                  setState(() => _theme = values.first),
            ),
            const SizedBox(height: 18),
            Text(
              pages.length > 1 ? '${pages.length} کارتونه' : 'د کارت مخکتنه',
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 10),
            if (pages.isNotEmpty)
              SizedBox(
                height: 450,
                child: PageView.builder(
                  key: const Key('card-preview'),
                  controller: _previewController,
                  itemCount: pages.length,
                  itemBuilder: (context, index) => Center(
                    child: FittedBox(
                      fit: BoxFit.scaleDown,
                      child: RepaintBoundary(
                        key: _previewKeys[index],
                        child: PoemCardWidget(
                          request: _request,
                          page: pages[index],
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            const SizedBox(height: 18),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    key: const Key('save-cards'),
                    onPressed: _working || pages.isEmpty
                        ? null
                        : () => _export(save: true),
                    icon: const Icon(Icons.download_rounded),
                    label: const Text('په ګالرۍ کې ساتل'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: FilledButton.icon(
                    key: const Key('share-cards'),
                    onPressed: _working || pages.isEmpty
                        ? null
                        : () => _export(save: false),
                    icon: _working
                        ? const SizedBox.square(
                            dimension: 18,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Icon(Icons.share_rounded),
                    label: const Text('شريکول'),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
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
                ? '${rendered.length} کارتونه په ګالرۍ کې وساتل شول.'
                : 'کارتونه د شريکولو لپاره چمتو شول.',
          ),
        ),
      );
    } catch (error) {
      if (!mounted) return;
      final message = error is ShareCardExportException
          ? switch (error.stage) {
              ShareCardExportStage.share => 'کارت شريک نه شو. بيا هڅه وکړئ.',
              ShareCardExportStage.gallery =>
                'کارت په ګالرۍ کې ونه ساتل شو. بيا هڅه وکړئ.',
              _ => 'کارت جوړ نه شو. بيا هڅه وکړئ.',
            }
          : 'کارت جوړ نه شو. بيا هڅه وکړئ.';
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
