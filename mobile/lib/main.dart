import 'package:flutter/widgets.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'app.dart';
import 'repository/poetry_repository.dart';
import 'services/api_client.dart';
import 'services/cache_store.dart';
import 'settings/reader_settings.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final preferences = await SharedPreferences.getInstance();
  final settings = await ReaderSettings.load(preferences);
  final repository = PoetryRepository(
    api: ApiClient(),
    cache: PreferencesCacheStore(preferences),
  );
  runApp(PitswalApp(repository: repository, readerSettings: settings));
}
