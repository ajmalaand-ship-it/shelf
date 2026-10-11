import 'dart:io';

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../l10n/app_strings.dart';
import '../models/book_author.dart';
import '../models/poetry_collection.dart';
import '../services/api_config.dart';
import '../purchases/purchase_screen.dart';

/// Content-sized presentation shared by discovery, search and owned books.
class BookPresentation extends StatelessWidget {
  const BookPresentation({
    required this.book,
    required this.cover,
    this.trailing,
    this.fontFamily,
    super.key,
  });
  final PoetryCollection book;
  final Widget cover;
  final Widget? trailing;
  final String? fontFamily;
  @override
  Widget build(BuildContext context) => LayoutBuilder(
    builder: (context, size) {
      final information = Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            book.title,
            key: Key('collection-title-${book.slug}'),
            textDirection: book.language == 'en'
                ? TextDirection.ltr
                : TextDirection.rtl,
            textAlign: book.language == 'en' ? TextAlign.left : TextAlign.right,
            style: Theme.of(context).textTheme.titleMedium
                ?.copyWith(fontFamily: fontFamily),
          ),
          if (book.creditedAuthors case final author?) ...[
            const SizedBox(height: 4),
            Text(
              author,
              textDirection: TextDirection.rtl,
              textAlign: TextAlign.right,
              style: TextStyle(
                color: Theme.of(context).colorScheme.onSurfaceVariant,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
          if (book.creditedTranslators case final translator?) ...[
            const SizedBox(height: 4),
            Text(
              AppStrings.of(context).translation(translator),
              textDirection: TextDirection.rtl,
              textAlign: TextAlign.right,
            ),
          ],
          const SizedBox(height: 8),
          ?trailing,
        ],
      );
      if (size.maxWidth < 240) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Center(child: cover),
            const SizedBox(height: 12),
            information,
          ],
        );
      }
      return Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          cover,
          const SizedBox(width: 16),
          Expanded(child: information),
        ],
      );
    },
  );
}

class BookList extends StatelessWidget {
  const BookList({
    required this.books,
    required this.onTap,
    this.ownerPreview = false,
    this.fontFamily,
    super.key,
  });
  final List<PoetryCollection> books;
  final ValueChanged<PoetryCollection> onTap;
  final bool ownerPreview;
  final String? fontFamily;
  @override
  Widget build(BuildContext context) => Column(
    children: [
      for (final book in books)
        Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: Card(
            clipBehavior: Clip.antiAlias,
            child: InkWell(
              onTap: () => onTap(book),
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: BookPresentation(
                  book: book,
                  fontFamily: fontFamily,
                  cover: BookCover(url: book.coverUrl, width: 92, height: 136),
                  trailing: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        AppStrings.of(context).typeName(book.bookType),
                        style: TextStyle(
                          color: Theme.of(context).colorScheme.primary,
                        ),
                      ),
                      if (ownerPreview)
                        Text(
                          AppStrings.of(context).status(book.displayStatus),
                          key: const Key('owner-preview-collection-status'),
                        )
                      else
                        BookPriceLabel(book: book),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
    ],
  );
}

class BookCover extends StatelessWidget {
  const BookCover({this.url, this.width = 112, this.height = 156, super.key});
  final String? url;
  final double width;
  final double height;
  @override
  Widget build(BuildContext context) {
    final placeholder = ColoredBox(
      color: Theme.of(context).colorScheme.surfaceContainerHighest,
      child: const Center(child: Icon(Icons.auto_stories_outlined, size: 36)),
    );
    return ClipRRect(
      borderRadius: BorderRadius.circular(8),
      child: SizedBox(
        width: width,
        height: height,
        child: url == null || url!.isEmpty || url!.startsWith('qa-cover:')
            ? placeholder
            : url!.startsWith('file:')
            ? Image.file(
                File.fromUri(Uri.parse(url!)),
                fit: BoxFit.contain,
                errorBuilder: (_, _, _) => placeholder,
              )
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

class AuthorPortrait extends StatelessWidget {
  const AuthorPortrait({required this.author, this.radius = 30, super.key});
  final BookAuthor author;
  final double radius;
  @override
  Widget build(BuildContext context) {
    final initials = author.name
        .trim()
        .split(RegExp(r'\s+'))
        .where((part) => part.isNotEmpty)
        .take(2)
        .map((part) => part.characters.first)
        .join(' ');
    final fallback = CircleAvatar(radius: radius, child: Text(initials));
    if (author.imageUrl == null || author.imageUrl!.isEmpty) return fallback;
    return ClipOval(
      child: CachedNetworkImage(
        imageUrl: author.imageUrl!,
        httpHeaders: shelfTestHeaders(Uri.parse(author.imageUrl!)),
        width: radius * 2,
        height: radius * 2,
        fit: BoxFit.cover,
        placeholder: (_, _) => fallback,
        errorWidget: (_, _, _) => fallback,
      ),
    );
  }
}

class BookTile extends StatelessWidget {
  const BookTile({
    required this.book,
    required this.onTap,
    this.ownerPreview = false,
    this.fontFamily,
    super.key,
  });
  final PoetryCollection book;
  final VoidCallback onTap;
  final bool ownerPreview;
  final String? fontFamily;
  @override
  Widget build(BuildContext context) => Card(
    clipBehavior: Clip.antiAlias,
    child: InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.all(10),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(
              child: Center(
                child: BookCover(url: book.coverUrl, width: 130, height: 180),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              book.title,
              key: Key('collection-title-${book.slug}'),
              textDirection: TextDirection.rtl,
              textAlign: TextAlign.right,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: Theme.of(context).textTheme.titleMedium
                  ?.copyWith(fontFamily: fontFamily),
            ),
            if (book.creditedAuthors case final author?)
              Text(
                author,
                style: const TextStyle(fontWeight: FontWeight.bold),
                textDirection: TextDirection.rtl,
                textAlign: TextAlign.right,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            if (ownerPreview)
              Text(
                AppStrings.of(context).status(book.displayStatus),
                key: const Key('owner-preview-collection-status'),
                style: Theme.of(context).textTheme.labelSmall,
              ),
          ],
        ),
      ),
    ),
  );
}

class BookGrid extends StatelessWidget {
  const BookGrid({
    required this.books,
    required this.onTap,
    this.ownerPreview = false,
    this.fontFamily,
    super.key,
  });
  final List<PoetryCollection> books;
  final ValueChanged<PoetryCollection> onTap;
  final bool ownerPreview;
  final String? fontFamily;
  @override
  Widget build(BuildContext context) => LayoutBuilder(
    builder: (context, constraints) => GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: constraints.maxWidth < 500
            ? 2
            : constraints.maxWidth < 850
            ? 3
            : 4,
        mainAxisExtent:
            284 * MediaQuery.textScalerOf(context).scale(1).clamp(1, 1.6),
        crossAxisSpacing: 8,
        mainAxisSpacing: 8,
      ),
      itemCount: books.length,
      itemBuilder: (context, index) => BookTile(
        book: books[index],
        onTap: () => onTap(books[index]),
        ownerPreview: ownerPreview,
        fontFamily: fontFamily,
      ),
    ),
  );
}

class BookRow extends StatelessWidget {
  const BookRow({
    required this.title,
    required this.books,
    required this.onTap,
    this.ownerPreview = false,
    this.fontFamily,
    super.key,
  });
  final String title;
  final List<PoetryCollection> books;
  final ValueChanged<PoetryCollection> onTap;
  final bool ownerPreview;
  final String? fontFamily;
  @override
  Widget build(BuildContext context) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      SectionTitle(title),
      BookList(
        books: books,
        onTap: onTap,
        ownerPreview: ownerPreview,
        fontFamily: fontFamily,
      ),
    ],
  );
}

class SectionTitle extends StatelessWidget {
  SectionTitle(this.title, {super.key});
  final String title;
  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 16),
    child: Text(title, style: Theme.of(context).textTheme.headlineSmall),
  );
}

class BookstoreError extends StatelessWidget {
  const BookstoreError({required this.onRetry, super.key});
  final VoidCallback onRetry;
  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.wifi_off_outlined, size: 40),
          const SizedBox(height: 16),
          Text(AppStrings.of(context).error),
          const SizedBox(height: 12),
          FilledButton(
            onPressed: onRetry,
            child: Text(AppStrings.of(context).retry),
          ),
        ],
      ),
    ),
  );
}
