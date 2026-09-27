class AppConfig {
  const AppConfig({
    required this.appName,
    required this.slogan,
    required this.contentVersion,
    required this.minAppVersion,
  });

  factory AppConfig.fromJson(Map<String, dynamic> json) => AppConfig(
    appName: _requiredString(json, 'app_name'),
    slogan: _requiredString(json, 'slogan'),
    contentVersion: _requiredInt(json, 'content_version'),
    minAppVersion: _requiredString(json, 'min_app_version'),
  );

  static const fallback = AppConfig(
    appName: 'Shelf',
    slogan: 'اجمل اند بشپړه شاعري',
    contentVersion: 0,
    minAppVersion: '1.0.0',
  );

  final String appName;
  final String slogan;
  final int contentVersion;
  final String minAppVersion;

  Map<String, dynamic> toJson() => {
    'app_name': appName,
    'slogan': slogan,
    'content_version': contentVersion,
    'min_app_version': minAppVersion,
  };
}

String _requiredString(Map<String, dynamic> json, String key) {
  final value = json[key];
  if (value is! String) throw FormatException('$key must be a string');
  return value;
}

int _requiredInt(Map<String, dynamic> json, String key) {
  final value = json[key];
  if (value is! int) throw FormatException('$key must be an integer');
  return value;
}
