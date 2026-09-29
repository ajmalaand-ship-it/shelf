import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:just_audio_background/just_audio_background.dart';
import 'package:path_provider/path_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'app.dart';
import 'audio/audio_cache_store.dart';
import 'audio/just_audio_controller.dart';
import 'bootstrap/data_source_factory.dart';
import 'purchases/entitlement_controller.dart';
import 'settings/reader_settings.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final preferences = await SharedPreferences.getInstance();
  final settings = await ReaderSettings.load(preferences);
  const ownerPreviewRequested = bool.fromEnvironment('OWNER_PREVIEW');
  const ownerPreviewToken = String.fromEnvironment('OWNER_PREVIEW_TOKEN');
  final sourceMode = selectPoetrySourceMode(
    productBuild: kReleaseMode,
    requestedMode: currentBuildMode,
    ownerPreviewRequested: ownerPreviewRequested,
    ownerPreviewToken: ownerPreviewToken,
  );
  final entitlements = EntitlementController(const InactivePurchaseProvider());
  await entitlements.initialize();
  final repository = createPoetryDataSource(
    preferences: preferences,
    entitlements: entitlements,
  );
  await JustAudioBackground.init(
    androidNotificationChannelId: 'services.shelf.app.audio',
    androidNotificationChannelName: 'Shelf audio',
    androidNotificationOngoing: false,
  );
  final cacheRoot = await getApplicationCacheDirectory();
  final audioController = await JustAudioController.create(
    repository: repository,
    cache: AudioCacheStore(
      Directory('${cacheRoot.path}/shelf_audio_${sourceMode.name}'),
    ),
    entitlements: entitlements,
  );
  runApp(
    ShelfApp(
      repository: repository,
      readerSettings: settings,
      audioController: audioController,
      entitlements: entitlements,
      qaMode: sourceMode == PoetrySourceMode.qa,
      ownerPreviewMode: sourceMode == PoetrySourceMode.ownerPreview,
    ),
  );
}
