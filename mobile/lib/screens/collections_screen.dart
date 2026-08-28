import 'package:flutter/material.dart';

import '../repository/poetry_repository.dart';
import '../settings/reader_settings.dart';
import '../widgets/collection_card.dart';
import 'collection_detail_screen.dart';

class CollectionsScreen extends StatelessWidget {
  const CollectionsScreen({
    required this.snapshot,
    required this.repository,
    required this.readerSettings,
    super.key,
  });

  final CatalogueSnapshot snapshot;
  final PoetryRepository repository;
  final ReaderSettings readerSettings;

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('ټولګې')),
    body: snapshot.collections.isEmpty
        ? const Center(child: Text('تر اوسه خپره شوې ټولګه نشته.'))
        : ListView.separated(
            padding: const EdgeInsets.all(20),
            itemCount: snapshot.collections.length,
            separatorBuilder: (_, _) => const SizedBox(height: 14),
            itemBuilder: (context, index) {
              final collection = snapshot.collections[index];
              return CollectionCard(
                collection: collection,
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute<void>(
                    builder: (_) => CollectionDetailScreen(
                      slug: collection.slug,
                      contentVersion: snapshot.config.contentVersion,
                      repository: repository,
                      readerSettings: readerSettings,
                    ),
                  ),
                ),
              );
            },
          ),
  );
}
