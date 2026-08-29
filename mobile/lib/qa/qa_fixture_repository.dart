import '../models/app_config.dart';
import '../models/poem.dart';
import '../models/poetry_collection.dart';
import '../repository/poetry_repository.dart';
import 'qa_audio_server.dart';

/// Synthetic, local-only reader acceptance data. None of this text is
/// represented as Ajmal Aand's authored or published poetry.
class QaFixtureRepository implements PoetryDataSource {
  static const _version = 900001;

  static const _config = AppConfig(
    appName: 'پېڅوَل',
    slogan: 'اجمل اند بشپړه شاعري',
    contentVersion: _version,
    minAppVersion: '1.0.0',
  );

  static const _covered = PoetryCollection(
    title: 'د لوست ازموينه — پوښ لري',
    slug: 'qa-reader-covered',
    author: 'مصنوعي QA منځپانګه',
    subtitle: 'د رښتينو شعرونو پر ځای د وسيلې ازموينه',
    description: 'دا ټولګه يوازې د ډيبګ په بڼه کې شته.',
    dedication: 'د کرښو، بندونو او تورو ازموينه',
    introduction: 'دا متن خپور شوی اثر نه دی.\n\nموخه يې يوازې د لوستونکي تخنيکي کتنه ده.',
    publicationInfo: 'QA — نه خپرېږي',
    coverUrl: 'qa-cover:reader',
    poemCount: 4,
    sortOrder: 1,
  );

  static const _plain = PoetryCollection(
    title: 'د لوست ازموينه — بې پوښه',
    slug: 'qa-reader-no-cover',
    author: 'مصنوعي QA منځپانګه',
    description: 'د بې پوښه ټولګې او اوږده لوست ازموينه.',
    poemCount: 3,
    sortOrder: 2,
  );

  static const _summaries = <String, List<PoemSummary>>{
    'qa-reader-covered': [
      PoemSummary(
        id: 9101,
        title: 'مصنوعي غږ ازموينه',
        workType: 'ORIGINAL',
        excerpt: 'پښتو توري: ټ ډ ړ ږ ښ ڼ ې ۍ',
        locked: false,
        hasAudio: true,
        audioDurationSeconds: 8,
        sortOrder: 1,
      ),
      PoemSummary(
        id: 9102,
        workType: 'ORIGINAL',
        excerpt: 'بې سرليکه ازموينيزه لومړۍ کرښه\nدويمه کرښه',
        locked: false,
        hasAudio: false,
        sortOrder: 2,
      ),
      PoemSummary(
        id: 9103,
        title: 'څو بندونه',
        workType: 'ORIGINAL',
        excerpt: 'لومړی بند\n\nدويم بند',
        locked: false,
        hasAudio: false,
        sortOrder: 3,
      ),
      PoemSummary(
        id: 9104,
        title: 'ژباړه — تړلې بېلګه',
        workType: 'TRANSLATION',
        originalAuthor: 'د QA اصلي شاعر',
        translator: 'د QA ژباړن',
        excerpt: 'د تړلي متن لنډه ازموينيزه برخه',
        locked: true,
        hasAudio: false,
        sortOrder: 4,
      ),
    ],
    'qa-reader-no-cover': [
      PoemSummary(
        id: 9201,
        title: 'ازاد نظم',
        workType: 'ORIGINAL',
        excerpt: 'کرښه په خپل اوږدوالي روانه ده',
        locked: false,
        hasAudio: false,
        sortOrder: 1,
      ),
      PoemSummary(
        id: 9202,
        title: 'اوږده ازموينه',
        workType: 'ORIGINAL',
        excerpt: 'د اوږده سکرول ازموينه',
        locked: false,
        hasAudio: false,
        sortOrder: 2,
      ),
      PoemSummary(
        id: 9203,
        title: 'د نښو او اعرابو ازموينه',
        workType: 'ORIGINAL',
        excerpt: 'پېڅوَل، زړۀ، نړۍ، مينهٔ، رؤيا',
        locked: false,
        hasAudio: false,
        sortOrder: 3,
      ),
    ],
  };

  static final Map<int, PoemDetail> _poems = {
    9101: _detail(
      id: 9101,
      slug: _covered.slug,
      title: 'مصنوعي غږ ازموينه',
      body: 'پښتو توري: ټ ډ ړ ږ ښ ڼ ې ۍ\nاعراب: زړۀ، مينهٔ، رؤيا',
      audioAvailable: true,
    ),
    9102: _detail(
      id: 9102,
      slug: _covered.slug,
      body: 'بې سرليکه ازموينيزه لومړۍ کرښه\nدويمه کرښه',
    ),
    9103: _detail(
      id: 9103,
      slug: _covered.slug,
      title: 'څو بندونه',
      body: 'د لومړي بند لومړۍ کرښه\nد لومړي بند دويمه کرښه\n\nد دويم بند لومړۍ کرښه\nد دويم بند دويمه کرښه\n\nد درېيم بند يوازينۍ کرښه',
    ),
    9104: _detail(
      id: 9104,
      slug: _covered.slug,
      title: 'ژباړه — تړلې بېلګه',
      excerpt: 'د تړلي متن لنډه ازموينيزه برخه\nبشپړ متن نه ښودل کېږي.',
      locked: true,
      workType: 'TRANSLATION',
      originalAuthor: 'د QA اصلي شاعر',
      translator: 'د QA ژباړن',
    ),
    9201: _detail(
      id: 9201,
      slug: _plain.slug,
      title: 'ازاد نظم',
      body: 'دا کرښه لنډه ده\nبله کرښه په خپل اوږدوالي روانه ده\n\nيوه تشه\nيو تم\nاو بيا يوه کرښه',
    ),
    9202: _detail(
      id: 9202,
      slug: _plain.slug,
      title: 'اوږده ازموينه',
      body: List.generate(
        48,
        (index) => index > 0 && index % 8 == 0
            ? '\nد ازموينې ${index + 1} کرښه — نوی بند'
            : 'د ازموينې ${index + 1} کرښه — ټ، ړ، ږ، ښ، ڼ، ې، ۍ',
      ).join('\n'),
    ),
    9203: _detail(
      id: 9203,
      slug: _plain.slug,
      title: 'د نښو او اعرابو ازموينه',
      body: 'پېڅوَل\nزړۀ او نړۍ\nمينهٔ او رؤيا\nأ، ؤ، ئ، ځ، څ، ږ، ښ',
    ),
  };

  @override
  Future<CatalogueSnapshot?> loadCachedCatalogue() async => null;

  @override
  Future<CatalogueSnapshot> refreshCatalogue(CatalogueSnapshot? cached) async =>
      const CatalogueSnapshot(
        config: _config,
        collections: [_covered, _plain],
        fromCache: false,
      );

  @override
  Future<CollectionBundle> loadCollection(
    String slug,
    int contentVersion,
  ) async {
    if (contentVersion != _version) throw StateError('Invalid QA version');
    final collection = [_covered, _plain].where((item) => item.slug == slug);
    if (collection.isEmpty) throw StateError('Unknown QA collection');
    return CollectionBundle(
      collection: collection.single,
      poems: _summaries[slug] ?? const [],
    );
  }

  @override
  Future<PoemDetail> loadPoem(int id, int contentVersion) async {
    if (contentVersion != _version) throw StateError('Invalid QA version');
    final poem = _poems[id];
    if (poem == null) throw StateError('Unknown QA poem');
    return poem;
  }

  @override
  Future<AudioAccess> loadAudio(int poemId) async {
    if (poemId != 9101) throw StateError('QA poem has no audio');
    return AudioAccess(
      url: await QaAudioServer.instance.url,
      cacheKey: 'qa-synthetic-tone-v1',
      durationSeconds: 8,
      format: 'wav',
    );
  }
}

PoemDetail _detail({
  required int id,
  required String slug,
  String? title,
  String workType = 'ORIGINAL',
  String? originalAuthor,
  String? translator,
  bool locked = false,
  String? excerpt,
  String? body,
  bool audioAvailable = false,
}) => PoemDetail(
  id: id,
  collectionSlug: slug,
  title: title,
  workType: workType,
  originalAuthor: originalAuthor,
  translator: translator,
  sourceDatePlace: 'ځايي QA — خپرېدونکې منځپانګه نه ده',
  sourceNote: null,
  locked: locked,
  excerpt: excerpt,
  body: locked ? null : body,
  audioAvailable: audioAvailable,
  audioLocked: locked,
  audioDurationSeconds: audioAvailable ? 8 : null,
  audioMetadataUrl: audioAvailable ? 'qa://synthetic-audio' : null,
  audioCacheKey: audioAvailable ? 'qa-synthetic-tone-v1' : null,
  audioFormat: audioAvailable ? 'wav' : null,
  audioLabel: audioAvailable
      ? 'مصنوعي ازموينيز غږ — د اجمل اند ثبت نه دی'
      : null,
);
