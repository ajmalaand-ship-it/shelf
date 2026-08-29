import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:pitswal/audio/audio_playback_controller.dart';
import 'package:pitswal/repository/poetry_repository.dart';
import 'package:pitswal/screens/poem_reader_screen.dart';
import 'package:pitswal/services/api_client.dart';
import 'package:pitswal/settings/reader_settings.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'test_support.dart';

void main() {
  Future<ReaderSettings> settings() async {
    SharedPreferences.setMockInitialValues({});
    return ReaderSettings.load(await SharedPreferences.getInstance());
  }

  PoetryRepository repository(Map<String, dynamic> poemJson) =>
      PoetryRepository(
        api: ApiClient(
          client: MockClient(
            (_) async => http.Response.bytes(
              utf8.encode(jsonEncode({'data': poemJson})),
              200,
              headers: {'content-type': 'application/json; charset=utf-8'},
            ),
          ),
        ),
        cache: MemoryCacheStore(),
      );

  testWidgets('poem without audio has no player or disabled controls', (
    tester,
  ) async {
    await tester.pumpWidget(
      MaterialApp(
        home: PoemReaderScreen(
          poemId: 301,
          contentVersion: 7,
          repository: repository(poemDetailJson()),
          settings: await settings(),
          audioController: FakeAudioController(),
        ),
      ),
    );
    await tester.pumpAndSettle();
    expect(find.byKey(const Key('audio-play-pause')), findsNothing);
  });

  testWidgets(
    'player exposes play pause seek duration loading and cache state',
    (tester) async {
      final controller = FakeAudioController();
      await tester.pumpWidget(
        MaterialApp(
          home: PoemReaderScreen(
            poemId: 301,
            contentVersion: 7,
            repository: repository(
              poemDetailJson(
                title: 'غږيز شعر',
                audioAvailable: true,
                audioDurationSeconds: 75,
                audioCacheKey: 'recording-v1',
                audioFormat: 'm4a',
              ),
            ),
            settings: await settings(),
            audioController: controller,
          ),
        ),
      );
      await tester.pumpAndSettle();
      expect(find.text('0:00 / 1:15'), findsOneWidget);

      await tester.tap(find.byKey(const Key('audio-play-pause')));
      await tester.pump();
      expect(controller.toggleCount, 1);
      expect(find.byIcon(Icons.pause_rounded), findsOneWidget);

      controller.emit(
        activePoemId: 301,
        state: AudioControlState.buffering,
        duration: const Duration(seconds: 75),
        cacheProgress: 0.5,
      );
      await tester.pump();
      expect(find.byType(CircularProgressIndicator), findsOneWidget);
      expect(find.text('50٪'), findsOneWidget);

      controller.emit(
        activePoemId: 301,
        state: AudioControlState.paused,
        position: const Duration(seconds: 15),
        duration: const Duration(seconds: 75),
        buffered: const Duration(seconds: 45),
        cacheProgress: 1,
      );
      await tester.pump();
      expect(find.text('0:15 / 1:15'), findsOneWidget);
      expect(find.text('ساتل شوی'), findsOneWidget);

      await tester.drag(
        find.byKey(const Key('audio-seek-slider')),
        const Offset(80, 0),
      );
      await tester.pump();
      expect(controller.lastSeek, isNotNull);
    },
  );

  testWidgets(
    'recoverable error shows retry and keeps translation attribution',
    (tester) async {
      final controller = FakeAudioController();
      await tester.pumpWidget(
        MaterialApp(
          home: PoemReaderScreen(
            poemId: 301,
            contentVersion: 7,
            repository: repository(
              poemDetailJson(
                title: 'ژمى',
                workType: 'TRANSLATION',
                audioAvailable: true,
                audioDurationSeconds: 10,
                audioCacheKey: 'translation-v1',
                audioFormat: 'mp3',
              ),
            ),
            settings: await settings(),
            audioController: controller,
          ),
        ),
      );
      await tester.pumpAndSettle();
      controller.emit(
        activePoemId: 301,
        state: AudioControlState.error,
        error: 'غږ اوس ترلاسه نه شو. بيا هڅه وکړئ.',
      );
      await tester.pump();

      expect(find.textContaining('اصلي شاعر: پروین پژواک'), findsOneWidget);
      expect(find.textContaining('پښتو ژباړه: اجمل اند'), findsOneWidget);
      expect(find.byKey(const Key('audio-error')), findsOneWidget);
      await tester.tap(find.text('بيا هڅه وکړئ'));
      await tester.pump();
      expect(controller.retryCount, 1);
    },
  );
}
