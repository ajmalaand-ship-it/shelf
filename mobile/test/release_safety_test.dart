import 'package:flutter_test/flutter_test.dart';
import 'package:pitswal/bootstrap/data_source_factory.dart';
import 'package:pitswal/qa/qa_fixture_repository.dart';
import 'package:pitswal/repository/poetry_repository.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
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
  });
}
