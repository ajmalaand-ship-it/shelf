import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../models/poetry_collection.dart';

class CollectionCard extends StatelessWidget {
  const CollectionCard({
    required this.collection,
    required this.onTap,
    super.key,
  });

  final PoetryCollection collection;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => Semantics(
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
                      style: Theme.of(context).textTheme.titleLarge,
                    ),
                    if (collection.author case final author?) ...[
                      const SizedBox(height: 8),
                      Text(author),
                    ],
                    if (collection.poemCount case final count?) ...[
                      const SizedBox(height: 8),
                      Text('$count شعرونه'),
                    ],
                  ],
                ),
              ),
              const Icon(Icons.arrow_back_ios_new_rounded, size: 18),
            ],
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
