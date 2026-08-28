import 'package:flutter/material.dart';

import 'repository/poetry_repository.dart';
import 'screens/home_screen.dart';
import 'settings/reader_settings.dart';
import 'theme/app_theme.dart';

class PitswalApp extends StatelessWidget {
  const PitswalApp({
    required this.repository,
    required this.readerSettings,
    super.key,
  });

  final PoetryRepository repository;
  final ReaderSettings readerSettings;

  @override
  Widget build(BuildContext context) => MaterialApp(
    debugShowCheckedModeBanner: false,
    title: 'پېڅوَل — Pitswal',
    theme: AppTheme.light,
    builder: (context, child) => Directionality(
      textDirection: TextDirection.rtl,
      child: child ?? const SizedBox.shrink(),
    ),
    home: HomeScreen(repository: repository, readerSettings: readerSettings),
  );
}
