import 'package:flutter_test/flutter_test.dart';
import 'package:shelf/models/app_config.dart';
import 'package:shelf/models/poem.dart';
import 'package:shelf/models/poetry_collection.dart';

import 'test_support.dart';

void main() {
  test('API models preserve Pashto Unicode and collection metadata', () {
    final config = AppConfig.fromJson(appConfigJson);
    final collection = PoetryCollection.fromJson(collectionJson);
    final poem = PoemDetail.fromJson(poemDetailJson());

    expect(config.appName, 'Shelf');
    expect(config.slogan, 'اجمل اند بشپړه شاعري');
    expect(collection.title, 'هېندارې او چینې');
    expect(collection.dedication, 'مينې ته');
    expect(poem.body, contains('ټ ډ ړ ږ ښ ڼ ې ۍ'));
    expect(poem.body, contains('\n\n'));
  });

  test('untitled work uses first line without creating an authored title', () {
    final poem = PoemSummary.fromJson(poemSummaryJson);

    expect(poem.title, isNull);
    expect(poem.isUntitled, isTrue);
    expect(poem.displayTitle, 'د لومړۍ کرښې پېژندنه');
    expect(poem.toJson()['title'], isNull);
  });

  test('translated and locked work parsing preserves truthful attribution', () {
    final summary = PoemSummary.fromJson(translationSummaryJson);
    final detail = PoemDetail.fromJson(
      poemDetailJson(locked: true, title: 'ژمى', workType: 'TRANSLATION'),
    );

    expect(summary.isTranslation, isTrue);
    expect(summary.originalAuthor, 'پروین پژواک');
    expect(summary.translator, 'اجمل اند');
    expect(detail.locked, isTrue);
    expect(detail.body, isNull);
    expect(detail.readableText, 'لنډه برخه\nدويمه کرښه');
  });

  test('malformed responses fail safely', () {
    expect(
      () => AppConfig.fromJson({...appConfigJson, 'content_version': 'bad'}),
      throwsFormatException,
    );
    expect(
      () => PoemDetail.fromJson({...poemDetailJson(), 'audio': 'bad'}),
      throwsFormatException,
    );
  });

  test('playable audio requires free access and stable identity', () {
    final playable = PoemDetail.fromJson(
      poemDetailJson(
        audioAvailable: true,
        audioDurationSeconds: 15,
        audioCacheKey: 'recording-v1',
        audioFormat: 'm4a',
      ),
    );
    final locked = PoemDetail.fromJson(
      poemDetailJson(
        locked: true,
        audioAvailable: true,
        audioLocked: true,
        audioCacheKey: 'recording-v1',
      ),
    );

    expect(playable.hasPlayableAudio, isTrue);
    expect(locked.hasPlayableAudio, isFalse);
  });
}
