import 'package:flutter/widgets.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'app.dart';
import 'bootstrap/data_source_factory.dart';
import 'settings/reader_settings.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final preferences = await SharedPreferences.getInstance();
  final settings = await ReaderSettings.load(preferences);
  final repository = createPoetryDataSource(preferences: preferences);
  runApp(
    PitswalApp(
      repository: repository,
      readerSettings: settings,
      qaMode: kDebugMode,
    ),
  );
}
