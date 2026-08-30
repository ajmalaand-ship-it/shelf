import 'package:flutter/material.dart';

import 'audio/audio_playback_controller.dart';
import 'repository/poetry_repository.dart';
import 'purchases/entitlement_controller.dart';
import 'screens/home_screen.dart';
import 'settings/reader_settings.dart';
import 'theme/app_theme.dart';

class PitswalApp extends StatelessWidget {
  const PitswalApp({
    required this.repository,
    required this.readerSettings,
    this.audioController,
    this.entitlements,
    this.qaMode = false,
    super.key,
  });

  final PoetryDataSource repository;
  final ReaderSettings readerSettings;
  final AudioPlaybackController? audioController;
  final EntitlementController? entitlements;
  final bool qaMode;

  @override
  Widget build(BuildContext context) => MaterialApp(
    debugShowCheckedModeBanner: false,
    title: 'پېڅوَل — Pitswal',
    theme: AppTheme.light,
    builder: (context, child) => Directionality(
      textDirection: TextDirection.rtl,
      child: child ?? const SizedBox.shrink(),
    ),
    home: HomeScreen(
      repository: repository,
      readerSettings: readerSettings,
      audioController: audioController ?? InactiveAudioController(),
      entitlements: entitlements,
      qaMode: qaMode,
    ),
  );
}
