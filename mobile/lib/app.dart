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
    this.ownerPreviewMode = false,
    super.key,
  });

  final PoetryDataSource repository;
  final ReaderSettings readerSettings;
  final AudioPlaybackController? audioController;
  final EntitlementController? entitlements;
  final bool qaMode;
  final bool ownerPreviewMode;

  @override
  Widget build(BuildContext context) => MaterialApp(
    debugShowCheckedModeBanner: false,
    title: 'پېڅوَل — Pitswal',
    theme: AppTheme.light,
    builder: (context, child) => Directionality(
      textDirection: TextDirection.rtl,
      child: ownerPreviewMode
          ? Column(
              children: [
                const Material(
                  color: Color(0xff7b241c),
                  child: SafeArea(
                    bottom: false,
                    child: Padding(
                      key: Key('owner-preview-banner'),
                      padding: EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 7,
                      ),
                      child: SizedBox(
                        width: double.infinity,
                        child: Text(
                          'OWNER PREVIEW — UNPUBLISHED CONTENT\n'
                          'PC-OWNER-ADMIN-01 • BUILD 4\n'
                          'د مالک کتنه — ناچاپه منځپانګه',
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            color: Colors.white,
                            fontFamily: 'Vazirmatn',
                            fontWeight: FontWeight.w500,
                            height: 1.2,
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
                Expanded(child: child ?? const SizedBox.shrink()),
              ],
            )
          : child ?? const SizedBox.shrink(),
    ),
    home: HomeScreen(
      repository: repository,
      readerSettings: readerSettings,
      audioController: audioController ?? InactiveAudioController(),
      entitlements: entitlements,
      qaMode: qaMode,
      ownerPreviewMode: ownerPreviewMode,
    ),
  );
}
