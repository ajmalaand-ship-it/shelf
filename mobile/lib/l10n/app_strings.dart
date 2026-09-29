/// Pashto interface copy for the bookstore. D15 will decide language switching.
/// Source book text is supplied by the server and never translated here.
abstract final class AppStrings {
  static const appName = 'Shelf';
  static const store = 'کتابپلورنځی';
  static const search = 'لټون';
  static const library = 'زما کتابتون';
  static const settings = 'امستنې';
  static const newBooks = 'نوي کتابونه';
  static const allBooks = 'ټول کتابونه';
  static const authors = 'لیکوالان';
  static const noBooks = 'تر اوسه کتابونه نشته.';
  static const noContent = 'د کتاب منځپانګه لا نشته.';
  static const searchHint = 'د کتاب یا لیکوال نوم ولیکئ';
  static const searchEmpty = 'کتاب د نوم، فرعي سرلیک یا لیکوال له مخې ولټوئ.';
  static const noResults = 'ستاسو له لټون سره سم کتاب ونه موندل شو.';
  static const clearFilters = 'لټون پاک کړئ';
  static const language = 'ژبه';
  static const category = 'ډولبندي';
  static const bookType = 'د کتاب ډول';
  static const all = 'ټول';
  static const poetry = 'شعر';
  static const prose = 'نثر';
  static const pashto = 'پښتو';
  static const farsi = 'فارسي';
  static const contents = 'لړلیک';
  static const description = 'د کتاب پېژندنه';
  static const dedication = 'ډالۍ';
  static const introduction = 'سريزه';
  static const foreword = 'مخکنۍ خبرې';
  static const publicationInfo = 'د چاپ معلومات';
  static const readSample = 'نمونه ولولئ';
  static const sample = 'نمونه';
  static const buySoon = 'پېرود — ډېر ژر';
  static const locked = 'تړلی';
  static const free = 'وړيا';
  static const untitled = 'بې سرلیکه';
  static const biography = 'د لیکوال پېژندنه';
  static const noBiography = 'د لیکوال پېژندنه لا نه ده ورزیاته شوې.';
  static const librarySoon = 'ستاسو کتابتون — ډېر ژر';
  static const libraryMessage =
      'دلته به ستاسو پېرودل شوي کتابونه خوندي وي. د حساب او پېرود اسانتیاوې ډېر ژر راځي.';
  static const readingPreferences = 'د لوست امستنې';
  static const font = 'لیکبڼه';
  static const error = 'منځپانګه ترلاسه نه شوه.';
  static const retry = 'بيا هڅه وکړئ';
  static const offline = 'ساتل شوې منځپانګه ښودل کېږي.';
  static const qaNotice = 'د لوست ازموينه — ډيبګ';
  static const ownerPreview = 'د مالک کتنه — ناچاپه منځپانګه';
  static const draft = 'مسوده';
  static const ready = 'کتنې ته چمتو';
  static const published = 'خپور';
  static const withdrawn = 'له خپرېدو ایستل شوی';
  static String status(String status) => switch (status) {
    'ready' => ready,
    'published' => published,
    'withdrawn' => withdrawn,
    _ => draft,
  };
  static String languageName(String code) => switch (code) {
    'ps' => pashto,
    'fa' => farsi,
    _ => code,
  };
  static String typeName(String type) => type == 'prose' ? prose : poetry;
  static String translation(String name) => 'ژباړه: $name';
  static String originalAuthor(String name) => 'اصلي لیکوال: $name';
}
