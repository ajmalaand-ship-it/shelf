import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../qa/qa_fixture_repository.dart';
import '../purchases/entitlement_controller.dart';
import '../repository/poetry_repository.dart';
import '../services/api_client.dart';
import '../services/cache_store.dart';

enum AppBuildMode { debug, profile, release }

AppBuildMode get currentBuildMode => kDebugMode
    ? AppBuildMode.debug
    : kProfileMode
    ? AppBuildMode.profile
    : AppBuildMode.release;

bool qaFixturesAllowed(AppBuildMode mode) => mode == AppBuildMode.debug;

bool useQaFixtures({
  required bool productBuild,
  required AppBuildMode requestedMode,
}) => !productBuild && qaFixturesAllowed(requestedMode);

PoetryDataSource createPoetryDataSource({
  required SharedPreferences preferences,
  EntitlementController? entitlements,
  AppBuildMode? buildMode,
}) {
  final mode = buildMode ?? currentBuildMode;
  // kReleaseMode is a compile-time constant. This guard takes precedence over
  // every caller-supplied value and lets release tree shaking discard fixtures.
  if (useQaFixtures(productBuild: kReleaseMode, requestedMode: mode)) {
    return QaFixtureRepository(entitlements: entitlements);
  }
  return PoetryRepository(
    api: ApiClient(entitlements: entitlements),
    cache: PreferencesCacheStore(preferences),
    entitlements: entitlements,
  );
}
