import 'package:flutter/widgets.dart';

import '../settings/interface_language.dart';

/// Bookstore interface copy; owner approved Pashto/English switching.
/// Source book text is supplied by the server and never translated here.
abstract final class AppStrings {
  static BookstoreStrings of(BuildContext context) =>
      BookstoreStrings(InterfaceLanguageScope.of(context)?.isEnglish ?? false);
  static const languageToggle = 'EN | پښتو';
  static const chooseLanguage = 'خپله ژبه وټاکئ / Choose your language';
  static const english = 'English';
  static const englishShort = 'EN';
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

class BookstoreStrings {
  const BookstoreStrings(this.isEnglish);
  final bool isEnglish;
  String get appName => isEnglish ? 'Shelf' : AppStrings.appName;
  String get store => isEnglish ? 'Store' : AppStrings.store;
  String get search => isEnglish ? 'Search' : AppStrings.search;
  String get library => isEnglish ? 'My Library' : AppStrings.library;
  String get settings => isEnglish ? 'Settings' : AppStrings.settings;
  String get newBooks => isEnglish ? 'New books' : AppStrings.newBooks;
  String get allBooks => isEnglish ? 'All books' : AppStrings.allBooks;
  String get authors => isEnglish ? 'Authors' : AppStrings.authors;
  String get noBooks => isEnglish ? 'No books yet.' : AppStrings.noBooks;
  String get noContent =>
      isEnglish ? 'This book has no content yet.' : AppStrings.noContent;
  String get searchHint =>
      isEnglish ? 'Search by book or author' : AppStrings.searchHint;
  String get searchEmpty => isEnglish
      ? 'Search by book title, subtitle or author.'
      : AppStrings.searchEmpty;
  String get noResults =>
      isEnglish ? 'No books match your search.' : AppStrings.noResults;
  String get clearFilters =>
      isEnglish ? 'Clear search' : AppStrings.clearFilters;
  String get language => isEnglish ? 'Language' : AppStrings.language;
  String get category => isEnglish ? 'Category' : AppStrings.category;
  String get bookType => isEnglish ? 'Book type' : AppStrings.bookType;
  String get all => isEnglish ? 'All' : AppStrings.all;
  String get poetry => isEnglish ? 'Poetry' : AppStrings.poetry;
  String get prose => isEnglish ? 'Prose' : AppStrings.prose;
  String get pashto => isEnglish ? 'Pashto' : AppStrings.pashto;
  String get farsi => isEnglish ? 'Farsi' : AppStrings.farsi;
  String get contents => isEnglish ? 'Contents' : AppStrings.contents;
  String get description =>
      isEnglish ? 'About the book' : AppStrings.description;
  String get dedication => isEnglish ? 'Dedication' : AppStrings.dedication;
  String get introduction =>
      isEnglish ? 'Introduction' : AppStrings.introduction;
  String get foreword => isEnglish ? 'Foreword' : AppStrings.foreword;
  String get publicationInfo =>
      isEnglish ? 'Publication details' : AppStrings.publicationInfo;
  String get readSample => isEnglish ? 'Read sample' : AppStrings.readSample;
  String get sample => isEnglish ? 'Sample' : AppStrings.sample;
  String get buySoon => isEnglish ? 'Buy — coming soon' : AppStrings.buySoon;
  String get locked => isEnglish ? 'Locked' : AppStrings.locked;
  String get free => isEnglish ? 'Free' : AppStrings.free;
  String get untitled => isEnglish ? 'Untitled' : AppStrings.untitled;
  String get biography => isEnglish ? 'About the author' : AppStrings.biography;
  String get noBiography => isEnglish
      ? 'An author biography has not been added yet.'
      : AppStrings.noBiography;
  String get librarySoon =>
      isEnglish ? 'Your library — coming soon' : AppStrings.librarySoon;
  String get libraryMessage => isEnglish
      ? 'Your purchased books will be kept here. Accounts and purchases are coming soon.'
      : AppStrings.libraryMessage;
  String get readingPreferences =>
      isEnglish ? 'Reading preferences' : AppStrings.readingPreferences;
  String get font => isEnglish ? 'Font' : AppStrings.font;
  String get error => isEnglish ? 'Could not load content.' : AppStrings.error;
  String get retry => isEnglish ? 'Try again' : AppStrings.retry;
  String get offline =>
      isEnglish ? 'Showing saved content.' : AppStrings.offline;
  String get qaNotice =>
      isEnglish ? 'Reading test — debug' : AppStrings.qaNotice;
  String get ownerPreview => isEnglish
      ? 'Owner preview — unpublished content'
      : AppStrings.ownerPreview;
  String get draft => isEnglish ? 'Draft' : AppStrings.draft;
  String get ready => isEnglish ? 'Ready for review' : AppStrings.ready;
  String get published => isEnglish ? 'Published' : AppStrings.published;
  String get withdrawn => isEnglish ? 'Withdrawn' : AppStrings.withdrawn;
  String get fontSize => isEnglish ? 'Text size' : 'د ليک کچه';
  String get readingFont => isEnglish ? 'Reading font' : 'د شعر ليکدود';
  String get vazirmatnName => isEnglish ? 'Vazirmatn' : 'وزيرمتن — Vazirmatn';
  String get defaultFontDescription =>
      isEnglish ? 'Default font' : 'اصلي او د پيل ليکدود';
  String get scheherazadeName =>
      isEnglish ? 'Scheherazade New' : 'شهرزاد نو — Scheherazade New';
  String get naskhDescription => isEnglish ? 'Traditional Naskh' : 'دوديز نسخ';
  String get nastaliqName =>
      isEnglish ? 'Noto Nastaliq Urdu' : 'نوټو نستعليق — Noto Nastaliq Urdu';
  String get nastaliqDescription =>
      isEnglish ? 'Literary Nastaliq' : 'ادبي نستعليق';
  String get lightPalette => isEnglish ? 'Light' : 'روښانه';
  String get sepiaPalette => isEnglish ? 'Sepia' : 'سپيا';
  String get darkPalette => isEnglish ? 'Dark' : 'تياره';
  String get interfaceLanguage =>
      isEnglish ? 'Interface language' : 'د اپلېکېشن ژبه';
  String get switchLanguage =>
      isEnglish ? 'Switch to Pashto' : 'انګلیسي ته واړوئ';
  String get languageSaveError => isEnglish
      ? 'Could not save the language. Please try again.'
      : 'ژبه خوندي نه شوه. بیا هڅه وکړئ.';
  String status(String value) => switch (value) {
    'ready' => ready,
    'published' => published,
    'withdrawn' => withdrawn,
    _ => draft,
  };
  String languageName(String code) => switch (code) {
    'ps' => pashto,
    'fa' => farsi,
    _ => code,
  };
  String typeName(String value) => value == 'prose' ? prose : poetry;
  String translation(String name) =>
      isEnglish ? 'Translation: $name' : AppStrings.translation(name);
  String originalAuthor(String name) =>
      isEnglish ? 'Original author: $name' : AppStrings.originalAuthor(name);
}
