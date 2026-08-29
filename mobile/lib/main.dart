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
import 'settings/reader_settings.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final preferences = await SharedPreferences.getInstance();
  final settings = await ReaderSettings.load(preferences);
  final repository = createPoetryDataSource(preferences: preferences);
  await JustAudioBackground.init(
    androidNotificationChannelId: 'com.hindara.pitswal.audio',
    androidNotificationChannelName: 'د پېڅوَل غږ',
    androidNotificationOngoing: false,
  );
  final cacheRoot = await getApplicationCacheDirectory();
  final audioController = await JustAudioController.create(
    repository: repository,
    cache: AudioCacheStore(Directory('${cacheRoot.path}/pitswal_audio')),
  );
  runApp(
    PitswalApp(
      repository: repository,
      readerSettings: settings,
      audioController: audioController,
      qaMode: kDebugMode,
    ),
  );
}
