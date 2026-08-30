import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../qa/qa_fixture_repository.dart';
import '../purchases/entitlement_controller.dart';
import '../repository/poetry_repository.dart';
import '../services/api_client.dart';
import '../services/cache_store.dart';

enum AppBuildMode { debug, profile, release }

enum PoetrySourceMode { qa, ownerPreview, public }

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

bool ownerPreviewAllowed({
  required bool productBuild,
  required AppBuildMode requestedMode,
  required bool requested,
  required String token,
  int? nowSeconds,
}) =>
    !productBuild &&
    requestedMode == AppBuildMode.debug &&
    requested &&
    ownerPreviewTokenUsable(token, nowSeconds: nowSeconds);

bool ownerPreviewTokenUsable(String token, {int? nowSeconds}) {
  final parts = token.split('.');
  if (parts.length != 4 || parts.first != 'v1') return false;
  final expires = int.tryParse(parts[1]);
  if (expires == null || expires <= (nowSeconds ?? _unixNowSeconds())) {
    return false;
  }
  return RegExp(r'^[A-Za-z0-9]{32}$').hasMatch(parts[2]) &&
      RegExp(r'^[a-f0-9]{64}$').hasMatch(parts[3]);
}

int _unixNowSeconds() => DateTime.now().millisecondsSinceEpoch ~/ 1000;

PoetrySourceMode selectPoetrySourceMode({
  required bool productBuild,
  required AppBuildMode requestedMode,
  required bool ownerPreviewRequested,
  required String ownerPreviewToken,
}) {
  if (ownerPreviewAllowed(
    productBuild: productBuild,
    requestedMode: requestedMode,
    requested: ownerPreviewRequested,
    token: ownerPreviewToken,
  )) {
    return PoetrySourceMode.ownerPreview;
  }
  if (ownerPreviewRequested) return PoetrySourceMode.public;
  if (useQaFixtures(productBuild: productBuild, requestedMode: requestedMode)) {
    return PoetrySourceMode.qa;
  }
  return PoetrySourceMode.public;
}

PoetryDataSource createPoetryDataSource({
  required SharedPreferences preferences,
  EntitlementController? entitlements,
  AppBuildMode? buildMode,
  bool? ownerPreviewRequested,
  String? ownerPreviewToken,
}) {
  final mode = buildMode ?? currentBuildMode;
  const configuredPreview = bool.fromEnvironment('OWNER_PREVIEW');
  const configuredToken = String.fromEnvironment('OWNER_PREVIEW_TOKEN');
  final previewRequested = ownerPreviewRequested ?? configuredPreview;
  final previewToken = ownerPreviewToken ?? configuredToken;
  final sourceMode = selectPoetrySourceMode(
    productBuild: kReleaseMode,
    requestedMode: mode,
    ownerPreviewRequested: previewRequested,
    ownerPreviewToken: previewToken,
  );
  // kReleaseMode is a compile-time constant. This guard takes precedence over
  // every caller-supplied value and lets release tree shaking discard fixtures.
  if (sourceMode == PoetrySourceMode.qa) {
    return QaFixtureRepository(entitlements: entitlements);
  }
  if (sourceMode == PoetrySourceMode.ownerPreview) {
    return OwnerPreviewRepository(
      api: ApiClient(
        baseUri: Uri.parse('https://poetry.ajmalaand.com/api/owner-preview/'),
        authorizationToken: previewToken,
      ),
      cache: PreferencesCacheStore(preferences),
    );
  }
  return PoetryRepository(
    api: ApiClient(entitlements: entitlements),
    cache: PreferencesCacheStore(preferences),
    entitlements: entitlements,
    cacheNamespace: 'public',
  );
}
