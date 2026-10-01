import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../models/poetry_collection.dart';
import '../settings/reader_settings.dart';
import '../services/api_config.dart';

class CollectionCard extends StatelessWidget {
  const CollectionCard({
    required this.collection,
    required this.onTap,
    required this.readerSettings,
    this.ownerPreviewMode = false,
    super.key,
  });

  final PoetryCollection collection;
  final VoidCallback onTap;
  final ReaderSettings readerSettings;
  final bool ownerPreviewMode;

  @override
  Widget build(BuildContext context) => AnimatedBuilder(
    animation: readerSettings,
    builder: (context, _) => Semantics(
      button: true,
      label: 'ټولګه ${collection.title}',
      child: Card(
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                _Cover(url: collection.coverUrl),
                const SizedBox(width: 18),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        collection.title,
                        key: Key('collection-title-${collection.slug}'),
                        style: Theme.of(context).textTheme.titleLarge
                            ?.copyWith(fontFamily: readerSettings.fontFamily),
                      ),
                      if (collection.creditedAuthors case final author?) ...[
                        const SizedBox(height: 8),
                        Text(author),
                      ],
                      if (collection.creditedTranslators
                          case final translators?) ...[
                        const SizedBox(height: 8),
                        Text(translators),
                      ],
                      if (collection.poemCount case final count?) ...[
                        const SizedBox(height: 8),
                        Text('$count شعرونه'),
                      ],
                      if (ownerPreviewMode) ...[
                        const SizedBox(height: 8),
                        Text(
                          collection.isActive ? 'خپره' : 'مسوده',
                          key: Key('owner-preview-collection-status'),
                          style: Theme.of(context).textTheme.labelMedium,
                        ),
                      ],
                    ],
                  ),
                ),
                const Icon(Icons.arrow_forward_ios_rounded, size: 18),
              ],
            ),
          ),
        ),
      ),
    ),
  );
}

class _Cover extends StatelessWidget {
  const _Cover({this.url});
  final String? url;

  @override
  Widget build(BuildContext context) {
    const placeholder = ColoredBox(
      color: Color(0xffe9dece),
      child: Center(child: Icon(Icons.auto_stories_outlined, size: 34)),
    );
    return ClipRRect(
      borderRadius: BorderRadius.circular(5),
      child: SizedBox(
        width: 76,
        height: 108,
        child: url == null
            ? placeholder
            : url!.startsWith('qa-cover:')
            ? const _QaCover()
            : CachedNetworkImage(
                imageUrl: url!,
                httpHeaders: shelfTestHeaders(Uri.parse(url!)),
                fit: BoxFit.contain,
                placeholder: (_, _) => placeholder,
                errorWidget: (_, _, _) => placeholder,
              ),
      ),
    );
  }
}

class _QaCover extends StatelessWidget {
  const _QaCover();

  @override
  Widget build(BuildContext context) => ColoredBox(
    color: const Color(0xff765430),
    child: Center(
      child: Padding(
        padding: const EdgeInsets.all(8),
        child: Text(
          'QA',
          textDirection: TextDirection.ltr,
          style: Theme.of(context).textTheme.titleLarge
              ?.copyWith(color: const Color(0xfffffbf4), fontFamily: null),
        ),
      ),
    ),
  );
}
