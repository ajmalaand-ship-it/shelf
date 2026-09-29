import 'package:flutter/material.dart';

import '../audio/audio_playback_controller.dart';
import '../purchases/entitlement_controller.dart';
import '../repository/poetry_repository.dart';
import '../settings/reader_settings.dart';
import 'author_screen.dart';
import 'collection_detail_screen.dart';

class BookstoreNavigation {
  const BookstoreNavigation({
    required this.repository,
    required this.settings,
    required this.contentVersion,
    this.audioController,
    this.entitlements,
    this.ownerPreview = false,
  });
  final PoetryDataSource repository;
  final ReaderSettings settings;
  final int contentVersion;
  final AudioPlaybackController? audioController;
  final EntitlementController? entitlements;
  final bool ownerPreview;

  void openBook(BuildContext context, String slug) =>
      Navigator.of(context).push(
        MaterialPageRoute<void>(
          builder: (_) => CollectionDetailScreen(
            slug: slug,
            contentVersion: contentVersion,
            repository: repository,
            readerSettings: settings,
            audioController: audioController,
            entitlements: entitlements,
            ownerPreviewMode: ownerPreview,
          ),
        ),
      );

  void openAuthor(BuildContext context, String slug) => Navigator.of(context)
      .push(
        MaterialPageRoute<void>(
          builder: (_) => AuthorScreen(slug: slug, navigation: this),
        ),
      );
}
