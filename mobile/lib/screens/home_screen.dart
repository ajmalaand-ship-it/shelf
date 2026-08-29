import 'package:flutter/material.dart';

import '../models/app_config.dart';
import '../repository/poetry_repository.dart';
import '../settings/reader_settings.dart';
import '../widgets/collection_card.dart';
import 'collection_detail_screen.dart';
import 'collections_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({
    required this.repository,
    required this.readerSettings,
    required this.qaMode,
    super.key,
  });

  final PoetryDataSource repository;
  final ReaderSettings readerSettings;
  final bool qaMode;

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  CatalogueSnapshot? _snapshot;
  Object? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    if (mounted) setState(() => _error = null);
    final cached = await widget.repository.loadCachedCatalogue();
    if (cached != null && mounted) setState(() => _snapshot = cached);
    try {
      final refreshed = await widget.repository.refreshCatalogue(cached);
      if (mounted) setState(() => _snapshot = refreshed);
    } catch (error) {
      if (mounted) setState(() => _error = error);
    }
  }

  @override
  Widget build(BuildContext context) {
    final snapshot = _snapshot;
    return Scaffold(
      body: SafeArea(
        child: snapshot == null
            ? _error == null
                  ? const Center(child: CircularProgressIndicator())
                  : _ErrorState(onRetry: _load)
            : RefreshIndicator(
                onRefresh: _load,
                child: CustomScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  slivers: [
                    SliverPadding(
                      padding: const EdgeInsets.fromLTRB(20, 20, 20, 32),
                      sliver: SliverList.list(
                        children: [
                          _BrandHeader(config: snapshot.config),
                          if (widget.qaMode) ...[
                            const SizedBox(height: 16),
                            const _QaNotice(),
                          ],
                          if (snapshot.refreshError != null) ...[
                            const SizedBox(height: 16),
                            const _OfflineNotice(),
                          ],
                          const SizedBox(height: 32),
                          Semantics(
                            button: true,
                            header: true,
                            label: 'ټولګې',
                            child: InkWell(
                              borderRadius: BorderRadius.circular(8),
                              onTap: () => _openCollections(snapshot),
                              child: Padding(
                                padding: const EdgeInsets.symmetric(
                                  vertical: 6,
                                ),
                                child: Row(
                                  children: [
                                    Expanded(
                                      child: Text(
                                        'ټولګې',
                                        style: Theme.of(context)
                                            .textTheme
                                            .headlineMedium,
                                      ),
                                    ),
                                    const Icon(Icons.arrow_back_rounded),
                                  ],
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(height: 14),
                          if (snapshot.collections.isEmpty)
                            const _EmptyCatalogue()
                          else
                            ...snapshot.collections
                                .take(3)
                                .map(
                                  (collection) => Padding(
                                    padding: const EdgeInsets.only(bottom: 14),
                                    child: CollectionCard(
                                      collection: collection,
                                      onTap: () => _openCollection(
                                        snapshot,
                                        collection.slug,
                                      ),
                                    ),
                                  ),
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

  void _openCollections(CatalogueSnapshot snapshot) {
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => CollectionsScreen(
          snapshot: snapshot,
          repository: widget.repository,
          readerSettings: widget.readerSettings,
        ),
      ),
    );
  }

  void _openCollection(CatalogueSnapshot snapshot, String slug) {
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => CollectionDetailScreen(
          slug: slug,
          contentVersion: snapshot.config.contentVersion,
          repository: widget.repository,
          readerSettings: widget.readerSettings,
        ),
      ),
    );
  }
}

class _QaNotice extends StatelessWidget {
  const _QaNotice();

  @override
  Widget build(BuildContext context) => Semantics(
    label: 'د ازموینې بڼه، يوازې ډيبګ',
    child: Container(
      key: const Key('debug-qa-notice'),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        border: Border.all(color: Theme.of(context).colorScheme.outlineVariant),
        borderRadius: BorderRadius.circular(10),
      ),
      child: const Row(
        children: [
          Icon(Icons.science_outlined, size: 18),
          SizedBox(width: 8),
          Expanded(child: Text('د لوست ازموينه — ډيبګ')),
        ],
      ),
    ),
  );
}

class _BrandHeader extends StatelessWidget {
  const _BrandHeader({required this.config});
  final AppConfig config;

  @override
  Widget build(BuildContext context) => Semantics(
    header: true,
    child: Column(
      children: [
        Text(
          config.appName.isEmpty ? AppConfig.fallback.appName : config.appName,
          textAlign: TextAlign.center,
          style: Theme.of(context).textTheme.headlineLarge?.copyWith(
            fontSize: 42,
            color: Theme.of(context).colorScheme.primary,
          ),
        ),
        const SizedBox(height: 12),
        Text(
          config.slogan.isEmpty ? AppConfig.fallback.slogan : config.slogan,
          textAlign: TextAlign.center,
          style: Theme.of(context).textTheme.titleLarge,
        ),
      ],
    ),
  );
}

class _EmptyCatalogue extends StatelessWidget {
  const _EmptyCatalogue();

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 42),
    decoration: BoxDecoration(
      color: Theme.of(context).colorScheme.surface,
      borderRadius: BorderRadius.circular(16),
    ),
    child: const Column(
      children: [
        Icon(Icons.auto_stories_outlined, size: 42),
        SizedBox(height: 16),
        Text('تر اوسه خپره شوې ټولګه نشته.', textAlign: TextAlign.center),
      ],
    ),
  );
}

class _OfflineNotice extends StatelessWidget {
  const _OfflineNotice();

  @override
  Widget build(BuildContext context) => Semantics(
    liveRegion: true,
    child: Material(
      color: Theme.of(context).colorScheme.secondaryContainer,
      borderRadius: BorderRadius.circular(10),
      child: const Padding(
        padding: EdgeInsets.all(12),
        child: Row(
          children: [
            Icon(Icons.cloud_off_outlined),
            SizedBox(width: 10),
            Expanded(child: Text('ساتل شوې منځپانګه ښودل کېږي.')),
          ],
        ),
      ),
    ),
  );
}

class _ErrorState extends StatelessWidget {
  const _ErrorState({required this.onRetry});
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.wifi_off_rounded, size: 42),
          const SizedBox(height: 16),
          const Text('منځپانګه ترلاسه نه شوه.', textAlign: TextAlign.center),
          const SizedBox(height: 12),
          FilledButton(onPressed: onRetry, child: const Text('بيا هڅه وکړئ')),
        ],
      ),
    ),
  );
}
