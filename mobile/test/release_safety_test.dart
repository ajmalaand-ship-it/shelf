import 'package:flutter_test/flutter_test.dart';
import 'package:pitswal/bootstrap/data_source_factory.dart';
import 'package:pitswal/qa/qa_fixture_repository.dart';
import 'package:pitswal/repository/poetry_repository.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  const previewToken =
      'v1.4102444800.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.'
      'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';
  test('QA fixtures are allowed in debug mode only', () {
    expect(qaFixturesAllowed(AppBuildMode.debug), isTrue);
    expect(qaFixturesAllowed(AppBuildMode.profile), isFalse);
    expect(qaFixturesAllowed(AppBuildMode.release), isFalse);
  });

  test('product builds reject even a requested debug fixture mode', () {
    expect(
      useQaFixtures(productBuild: true, requestedMode: AppBuildMode.debug),
      isFalse,
    );
    expect(
      useQaFixtures(productBuild: false, requestedMode: AppBuildMode.debug),
      isTrue,
    );
  });

  test('release mode always selects the production repository', () async {
    SharedPreferences.setMockInitialValues({});
    final preferences = await SharedPreferences.getInstance();

    final debug = createPoetryDataSource(
      preferences: preferences,
      buildMode: AppBuildMode.debug,
    );
    final release = createPoetryDataSource(
      preferences: preferences,
      buildMode: AppBuildMode.release,
    );

    expect(debug, isA<QaFixtureRepository>());
    expect(release, isA<PoetryRepository>());
    expect(release, isNot(isA<QaFixtureRepository>()));
    expect(release, isNot(isA<OwnerPreviewRepository>()));
  });

  test('owner preview requires debug, explicit flag, and non-empty token', () {
    expect(
      ownerPreviewAllowed(
        productBuild: false,
        requestedMode: AppBuildMode.debug,
        requested: true,
        token: previewToken,
      ),
      isTrue,
    );
    for (final mode in [AppBuildMode.profile, AppBuildMode.release]) {
      expect(
        ownerPreviewAllowed(
          productBuild: false,
          requestedMode: mode,
          requested: true,
          token: previewToken,
        ),
        isFalse,
      );
    }
    expect(
      ownerPreviewAllowed(
        productBuild: true,
        requestedMode: AppBuildMode.debug,
        requested: true,
        token: previewToken,
      ),
      isFalse,
    );
  });

  test('release cannot select owner preview even when requested', () {
    expect(
      selectPoetrySourceMode(
        productBuild: true,
        requestedMode: AppBuildMode.debug,
        ownerPreviewRequested: true,
        ownerPreviewToken: previewToken,
      ),
      PoetrySourceMode.public,
    );
  });

  test('explicit debug owner preview selects protected repository', () async {
    SharedPreferences.setMockInitialValues({});
    final preferences = await SharedPreferences.getInstance();
    final repository = createPoetryDataSource(
      preferences: preferences,
      buildMode: AppBuildMode.debug,
      ownerPreviewRequested: true,
      ownerPreviewToken: previewToken,
    );

    expect(repository, isA<OwnerPreviewRepository>());
    expect(repository, isNot(isA<QaFixtureRepository>()));
  });

  test('expired preview token cannot select or reopen preview cache', () {
    const expired =
        'v1.100.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.'
        'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';
    expect(ownerPreviewTokenUsable(expired, nowSeconds: 101), isFalse);
    expect(
      selectPoetrySourceMode(
        productBuild: false,
        requestedMode: AppBuildMode.debug,
        ownerPreviewRequested: true,
        ownerPreviewToken: expired,
      ),
      PoetrySourceMode.public,
    );
  });
}
