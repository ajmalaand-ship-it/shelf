import 'package:flutter/widgets.dart';

import '../settings/interface_language.dart';

/// Bookstore interface copy; owner approved Pashto/English switching.
/// Source book text is supplied by the server and never translated here.
abstract final class AppStrings {
  static BookstoreStrings of(BuildContext context) => BookstoreStrings(
    InterfaceLanguageScope.of(context)?.isEnglish ?? false,
    isDari:
        InterfaceLanguageScope.of(context)?.language == InterfaceLanguage.dari,
  );
  static const languageToggle = 'EN | پښتو';
  static const chooseLanguage = 'خپله ژبه وټاکئ / Choose your language';
  static const english = 'English';
  static const englishShort = 'EN';
  static const appName = 'Shelf';
  static const store = 'کتابپلورنځی';
  static const search = 'لټون';
  static const library = 'زما کتابتون';
  static const settings = 'سیټینګ';
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
  const BookstoreStrings(this.isEnglish, {this.isDari = false});
  final bool isDari;
  String choose(String en, String ps, String dari) => isEnglish
      ? en
      : isDari
      ? dari
      : ps;
  final bool isEnglish;
  String phrase(String en, String ps) => choose(en, ps, _dariPhrases[en] ?? en);
  static const _dariPhrases = {
    'Purchases checked for this account.': 'خریدهای این حساب بررسی شدند.',
    'Refresh Library': 'تازه کردن کتابخانه',
    'Books you buy will appear here. Browse the Store and read a free sample first.': 'کتاب‌های خریداری‌شده اینجا نمایش داده می‌شوند. نخست نمونهٔ رایگان را در کتاب‌فروشی بخوانید.',
    'No downloaded books. Download a book from All to read offline.': 'کتابی دانلود نشده است. از بخش همه یک کتاب برای خواندن آفلاین دانلود کنید.',
    'Downloaded. Go online at least once every 30 days.':
        'دانلود شد. حداقل هر ۳۰ روز یک‌بار به انترنت وصل شوید.',
    'Download for offline reading': 'دانلود برای خواندن آفلاین',
    'Download removed. You still own the book.':
        'دانلود حذف شد. هنوز مالک کتاب هستید.',
    'Download your books to read offline. Go online at least once every 30 days.': 'کتاب‌ها را برای خواندن آفلاین دانلود کنید. حداقل هر ۳۰ روز یک‌بار به انترنت وصل شوید.',

    'Books change your life': 'کتاب زندگی شما را تغییر می‌دهد',
    'Reading mode': 'شیوهٔ خواندن',
    'Scroll': 'خواندن پیوسته',
    'Pages': 'صفحه‌ها',
    'Decrease text size': 'کوچک کردن متن',
    'Increase text size': 'بزرگ کردن متن',
    'Page color': 'رنگ صفحه',
    'Share': 'اشتراک‌گذاری',
    'Sample — full section locked': 'نمونه — بخش کامل قفل است',
    'Next section unavailable': 'بخش بعدی در دسترس نیست',
    'Reading position could not be saved.': 'موقعیت خواندن ذخیره نشد.',
    'Book text changed. This section starts again.':
        'متن کتاب تغییر کرده است. این بخش از آغاز خوانده می‌شود.',
    'Return to Contents and check book access.':
        'به فهرست برگردید و دسترسی کتاب را بررسی کنید.',
    'Book details': 'دربارهٔ کتاب',
    'Read book': 'خواندن کتاب',
    'Open owned book': 'باز کردن کتاب خریداری‌شده',
    'Purchases unavailable': 'خرید در دسترس نیست',
    'Buy book': 'خرید کتاب',
    'Library refreshed.': 'کتابخانه تازه شد.',
    'Could not finish. Go online and try again.':
        'کار انجام نشد. به انترنت وصل شوید و دوباره کوشش کنید.',
    'Downloads': 'دانلودها',
    'Downloaded': 'دانلودشده',
    'Owned': 'خریداری‌شده',
    'Restore purchases': 'بازیابی خریدها',
    'Refresh': 'تازه کردن',
    'Download': 'دانلود',
    'Remove download': 'حذف دانلود',
    'Downloading…': 'در حال دانلود…',
    'Download removed.': 'دانلود حذف شد.',
    'Book downloaded.': 'کتاب دانلود شد.',
    'Purchases restored.': 'خریدها بازیابی شدند.',
  };
  String get appName => isDari
      ? 'شیلف'
      : isEnglish
      ? 'Shelf'
      : AppStrings.appName;
  String get store => isDari
      ? 'کتاب‌فروشی'
      : isEnglish
      ? 'Store'
      : AppStrings.store;
  String get search => isDari
      ? 'جستجو'
      : isEnglish
      ? 'Search'
      : AppStrings.search;
  String get library => isDari
      ? 'کتابخانهٔ من'
      : isEnglish
      ? 'My Library'
      : AppStrings.library;
  String get settings => isDari
      ? 'تنظیمات'
      : isEnglish
      ? 'Settings'
      : AppStrings.settings;
  String get newBooks => isDari
      ? 'کتاب‌های تازه'
      : isEnglish
      ? 'New books'
      : AppStrings.newBooks;
  String get allBooks => isDari
      ? 'همهٔ کتاب‌ها'
      : isEnglish
      ? 'All books'
      : AppStrings.allBooks;
  String get authors => isDari
      ? 'نویسندگان'
      : isEnglish
      ? 'Authors'
      : AppStrings.authors;
  String get noBooks => isDari
      ? 'هنوز کتابی موجود نیست.'
      : isEnglish
      ? 'No books yet.'
      : AppStrings.noBooks;
  String get noContent => isDari
      ? 'هنوز محتوای این کتاب موجود نیست.'
      : isEnglish
      ? 'This book has no content yet.'
      : AppStrings.noContent;
  String get searchHint => isDari
      ? 'نام کتاب یا نویسنده را بنویسید'
      : isEnglish
      ? 'Search by book or author'
      : AppStrings.searchHint;
  String get searchEmpty => isDari
      ? 'کتاب را با نام، عنوان فرعی یا نویسنده جستجو کنید.'
      : isEnglish
      ? 'Search by book title, subtitle or author.'
      : AppStrings.searchEmpty;
  String get noResults => isDari
      ? 'کتابی مطابق جستجوی شما یافت نشد.'
      : isEnglish
      ? 'No books match your search.'
      : AppStrings.noResults;
  String get clearFilters => isDari
      ? 'پاک کردن جستجو'
      : isEnglish
      ? 'Clear search'
      : AppStrings.clearFilters;
  String get language => isDari
      ? 'زبان'
      : isEnglish
      ? 'Language'
      : AppStrings.language;
  String get category => isDari
      ? 'دسته‌بندی'
      : isEnglish
      ? 'Category'
      : AppStrings.category;
  String get bookType => isDari
      ? 'نوع کتاب'
      : isEnglish
      ? 'Book type'
      : AppStrings.bookType;
  String get all => isDari
      ? 'همه'
      : isEnglish
      ? 'All'
      : AppStrings.all;
  String get poetry => isDari
      ? 'شعر'
      : isEnglish
      ? 'Poetry'
      : AppStrings.poetry;
  String get prose => isDari
      ? 'نثر'
      : isEnglish
      ? 'Prose'
      : AppStrings.prose;
  String get pashto => isDari
      ? 'پښتو'
      : isEnglish
      ? 'Pashto'
      : AppStrings.pashto;
  String get farsi => isDari
      ? 'فارسی'
      : isEnglish
      ? 'Farsi'
      : AppStrings.farsi;
  String get contents => isDari
      ? 'فهرست'
      : isEnglish
      ? 'Contents'
      : AppStrings.contents;
  String get description => isDari
      ? 'دربارهٔ کتاب'
      : isEnglish
      ? 'About the book'
      : AppStrings.description;
  String get dedication => isDari
      ? 'اهدا'
      : isEnglish
      ? 'Dedication'
      : AppStrings.dedication;
  String get introduction => isDari
      ? 'مقدمه'
      : isEnglish
      ? 'Introduction'
      : AppStrings.introduction;
  String get foreword => isDari
      ? 'پیشگفتار'
      : isEnglish
      ? 'Foreword'
      : AppStrings.foreword;
  String get publicationInfo => isDari
      ? 'معلومات نشر'
      : isEnglish
      ? 'Publication details'
      : AppStrings.publicationInfo;
  String get readSample => isDari
      ? 'خواندن نمونه'
      : isEnglish
      ? 'Read sample'
      : AppStrings.readSample;
  String get sample => isDari
      ? 'نمونه'
      : isEnglish
      ? 'Sample'
      : AppStrings.sample;
  String get buySoon => isDari
      ? 'خرید — به‌زودی'
      : isEnglish
      ? 'Buy — coming soon'
      : AppStrings.buySoon;
  String get locked => isDari
      ? 'قفل‌شده'
      : isEnglish
      ? 'Locked'
      : AppStrings.locked;
  String get free => isDari
      ? 'رایگان'
      : isEnglish
      ? 'Free'
      : AppStrings.free;
  String get untitled => isDari
      ? 'بدون عنوان'
      : isEnglish
      ? 'Untitled'
      : AppStrings.untitled;
  String get biography => isDari
      ? 'دربارهٔ نویسنده'
      : isEnglish
      ? 'About the author'
      : AppStrings.biography;
  String get noBiography => isDari
      ? 'هنوز زندگی‌نامهٔ نویسنده افزوده نشده است.'
      : isEnglish
      ? 'An author biography has not been added yet.'
      : AppStrings.noBiography;
  String get librarySoon => isDari
      ? 'کتابخانهٔ شما — به‌زودی'
      : isEnglish
      ? 'Your library — coming soon'
      : AppStrings.librarySoon;
  String get libraryMessage => isDari
      ? 'کتاب‌های خریداری‌شدهٔ شما اینجا نگهداری می‌شوند. حساب و خرید به‌زودی فعال می‌شوند.'
      : isEnglish
      ? 'Your purchased books will be kept here. Accounts and purchases are coming soon.'
      : AppStrings.libraryMessage;
  String get readingPreferences => isDari
      ? 'تنظیمات خواندن'
      : isEnglish
      ? 'Reading preferences'
      : AppStrings.readingPreferences;
  String get font => isDari
      ? 'قلم'
      : isEnglish
      ? 'Font'
      : AppStrings.font;
  String get error => isDari
      ? 'محتوا دریافت نشد.'
      : isEnglish
      ? 'Could not load content.'
      : AppStrings.error;
  String get retry => isDari
      ? 'دوباره کوشش کنید'
      : isEnglish
      ? 'Try again'
      : AppStrings.retry;
  String get offline => isDari
      ? 'محتوای ذخیره‌شده نمایش داده می‌شود.'
      : isEnglish
      ? 'Showing saved content.'
      : AppStrings.offline;
  String get qaNotice => isDari
      ? 'آزمایش خواندن — دیباگ'
      : isEnglish
      ? 'Reading test — debug'
      : AppStrings.qaNotice;
  String get ownerPreview => isDari
      ? 'پیش‌نمایش مالک — محتوای نشرنشده'
      : isEnglish
      ? 'Owner preview — unpublished content'
      : AppStrings.ownerPreview;
  String get draft => isDari
      ? 'مسوده'
      : isEnglish
      ? 'Draft'
      : AppStrings.draft;
  String get ready => isDari
      ? 'آمادهٔ بررسی'
      : isEnglish
      ? 'Ready for review'
      : AppStrings.ready;
  String get published => isDari
      ? 'نشرشده'
      : isEnglish
      ? 'Published'
      : AppStrings.published;
  String get withdrawn => isDari
      ? 'از نشر خارج‌شده'
      : isEnglish
      ? 'Withdrawn'
      : AppStrings.withdrawn;
  String get fontSize => isDari
      ? 'اندازهٔ متن'
      : isEnglish
      ? 'Text size'
      : 'د ليک کچه';
  String get readingFont => isDari
      ? 'قلم خواندن'
      : isEnglish
      ? 'Reading font'
      : 'د شعر ليکدود';
  String get vazirmatnName => isDari
      ? 'وزیرمتن'
      : isEnglish
      ? 'Vazirmatn'
      : 'وزیرمتن';
  String get defaultFontDescription => isDari
      ? 'قلم پیش‌فرض'
      : isEnglish
      ? 'Default font'
      : 'اصلي او د پيل ليکدود';
  String get scheherazadeName => isDari
      ? 'شهرزاد نو'
      : isEnglish
      ? 'Scheherazade New'
      : 'نوی شهرزاد';
  String get naskhDescription => isDari
      ? 'نسخ سنتی'
      : isEnglish
      ? 'Traditional Naskh'
      : 'دوديز نسخ';
  String get nastaliqName => isDari
      ? 'نستعلیق اردو'
      : isEnglish
      ? 'Noto Nastaliq Urdu'
      : 'نوټو نستعلیق اردو';
  String get nastaliqDescription => isDari
      ? 'نستعلیق ادبی'
      : isEnglish
      ? 'Literary Nastaliq'
      : 'ادبي نستعليق';
  String get lightPalette => isDari
      ? 'روشن'
      : isEnglish
      ? 'Light'
      : 'روښانه';
  String get sepiaPalette => isDari
      ? 'کاغذی'
      : isEnglish
      ? 'Sepia'
      : 'سپيا';
  String get darkPalette => isDari
      ? 'تاریک'
      : isEnglish
      ? 'Dark'
      : 'تياره';
  String get interfaceLanguage => isDari
      ? 'زبان برنامه'
      : isEnglish
      ? 'Interface language'
      : 'د اپلېکېشن ژبه';
  String get switchLanguage => isDari
      ? 'تغییر زبان'
      : isEnglish
      ? 'Switch to Pashto'
      : 'انګلیسي ته واړوئ';
  String get languageSaveError => isDari
      ? 'زبان ذخیره نشد. دوباره کوشش کنید.'
      : isEnglish
      ? 'Could not save the language. Please try again.'
      : 'ژبه خوندي نه شوه. بیا هڅه وکړئ.';
  String get privacyPolicy => isDari
      ? 'پالیسی محرمیت'
      : isEnglish
      ? 'Privacy policy'
      : 'د محرمیت تګلاره';
  String get support => isDari
      ? 'کمک'
      : isEnglish
      ? 'Support'
      : 'مرسته';
  String get copyAddress => isDari
      ? 'کاپی آدرس'
      : isEnglish
      ? 'Copy address'
      : 'پته کاپي کړئ';
  String get addressCopied => isDari
      ? 'آدرس کاپی شد'
      : isEnglish
      ? 'Address copied'
      : 'پته کاپي شوه';
  String get openEmail => isDari
      ? 'باز کردن برنامهٔ ایمیل'
      : isEnglish
      ? 'Open email app'
      : 'د برېښنالیک اپ پرانیزئ';
  String get emailUnavailable => isDari
      ? 'برنامهٔ ایمیل باز نشد. آدرس زیر را کاپی کنید.'
      : isEnglish
      ? 'Could not open an email app. Copy the address below.'
      : 'د برېښنالیک اپ پرانیستل نه شو. لاندې پته کاپي کړئ.';
  String get browserUnavailable => isDari
      ? 'مرورگر باز نشد. لینک زیر را کاپی کنید.'
      : isEnglish
      ? 'Could not open a browser. Copy the link below.'
      : 'براوزر پرانیستل نه شو. لاندې لینک کاپي کړئ.';
  String get account => isDari
      ? 'حساب'
      : isEnglish
      ? 'Account'
      : 'حساب';
  String get signIn => isDari
      ? 'ورود'
      : isEnglish
      ? 'Sign in'
      : 'ننوتل';
  String get createAccount => isDari
      ? 'ساختن حساب'
      : isEnglish
      ? 'Create account'
      : 'حساب جوړول';
  String get signOut => isDari
      ? 'خروج'
      : isEnglish
      ? 'Sign out'
      : 'وتل';
  String get forgotPassword => isDari
      ? 'رمز عبور را فراموش کرده‌اید؟'
      : isEnglish
      ? 'Forgot password?'
      : 'پټنوم مو هېر شوی؟';
  String get resetPassword => isDari
      ? 'ارسال ایمیل تغییر رمز'
      : isEnglish
      ? 'Send reset email'
      : 'د پټنوم د بدلولو برېښنالیک ولېږئ';
  String get changePassword => isDari
      ? 'تغییر رمز عبور'
      : isEnglish
      ? 'Change password'
      : 'پټنوم بدلول';
  String get deleteAccount => isDari
      ? 'حذف حساب'
      : isEnglish
      ? 'Delete account'
      : 'حساب ړنګول';
  String get confirmDelete => isDari
      ? 'ارسال ایمیل حذف حساب'
      : isEnglish
      ? 'Send deletion email'
      : 'د ړنګولو برېښنالیک ولېږئ';
  String get cancel => isDari
      ? 'لغو'
      : isEnglish
      ? 'Cancel'
      : 'لغوه';
  String get email => isDari
      ? 'ایمیل'
      : isEnglish
      ? 'Email'
      : 'برېښنالیک';
  String get password => isDari
      ? 'رمز عبور'
      : isEnglish
      ? 'Password'
      : 'پټنوم';
  String get currentPassword => isDari
      ? 'رمز عبور فعلی'
      : isEnglish
      ? 'Current password'
      : 'اوسنی پټنوم';
  String get newPassword => isDari
      ? 'رمز عبور تازه'
      : isEnglish
      ? 'New password'
      : 'نوی پټنوم';
  String get confirmPassword => isDari
      ? 'تکرار رمز عبور'
      : isEnglish
      ? 'Confirm password'
      : 'پټنوم بیا ولیکئ';
  String get optionalName => isDari
      ? 'نام نمایشی (اختیاری)'
      : isEnglish
      ? 'Display name (optional)'
      : 'ښکاره نوم (اختیاري)';
  String get googleSignIn => isDari
      ? 'ادامه با گوگل'
      : isEnglish
      ? 'Continue with Google'
      : 'د ګوګل له لارې ننوتل';
  String get emailVerified => isDari
      ? 'ایمیل تأیید شده'
      : isEnglish
      ? 'Email verified'
      : 'برېښنالیک تایید شوی';
  String get verifyEmail => isDari
      ? 'ایمیل خود را تأیید کنید.'
      : isEnglish
      ? 'Please verify your email.'
      : 'خپل برېښنالیک تایید کړئ.';
  String get resendVerification => isDari
      ? 'ارسال دوبارهٔ ایمیل تأیید'
      : isEnglish
      ? 'Resend verification email'
      : 'د تایید برېښنالیک بیا ولېږئ';
  String get refreshAccount => isDari
      ? 'تازه کردن حساب'
      : isEnglish
      ? 'Refresh account'
      : 'حساب تازه کړئ';
  String get booksWillAppear => isDari
      ? 'کتاب‌های شما اینجا نمایش داده می‌شوند'
      : isEnglish
      ? 'Your books will appear here'
      : 'ستاسو کتابونه به دلته ښکاره شي';
  String get libraryAccountMessage => isDari
      ? 'دیدن کتاب‌ها و خواندن نمونه‌ها رایگان است. خرید کتاب به‌زودی فعال می‌شود.'
      : isEnglish
      ? 'Browsing and samples are free. Book purchases are coming soon.'
      : 'د کتابونو کتل او نمونې وړیا دي. د کتابونو پېرودل به ژر راشي.';
  String get accountsUnavailable => isDari
      ? 'حساب‌ها موقتاً در دسترس نیستند. هنوز می‌توانید کتاب‌ها و نمونه‌ها را بخوانید.'
      : isEnglish
      ? 'Accounts are temporarily unavailable. You can still browse and read samples.'
      : 'حسابونه اوس نه شته. کتابونه او نمونې لا هم لوستلی شئ.';
  String get ownerAccountTesting => isDari
      ? 'آزمایش مالک: فعلاً تنها مالک می‌تواند حساب بسازد.'
      : isEnglish
      ? 'Owner testing: only the owner’s email can create an account for now.'
      : 'د مالک ازموینه: اوس یوازې د مالک په برېښنالیک حساب جوړېږي.';
  String get accountConnectionError => isDari
      ? 'درخواست انجام نشد. اتصال خود را بررسی کنید و دوباره کوشش کنید.'
      : isEnglish
      ? 'Could not complete this request. Check your connection and try again.'
      : 'غوښتنه بشپړه نه شوه. خپله اړیکه وګورئ او بیا هڅه وکړئ.';
  String get emailSent => isDari
      ? 'ایمیل و پوشهٔ اسپم خود را بررسی کنید. اگر آدرس واجد شرایط باشد، لینک تأیید ارسال شده است.'
      : isEnglish
      ? 'Check your email, including spam. If the address is eligible, a confirmation link has been sent.'
      : 'خپل برېښنالیک او سپم وګورئ. که پته وړ وي، د تایید لینک ورته لېږل شوی.';
  String get deleteEmailSent => isDari
      ? 'ایمیل خود را برای تأیید حذف بررسی کنید. حساب تا تأیید شما باقی می‌ماند.'
      : isEnglish
      ? 'Check your email to confirm deletion. Your account remains until you confirm.'
      : 'د ړنګولو د تایید لپاره خپل برېښنالیک وګورئ. تر تایید پورې حساب پاتې کېږي.';
  String get deleteAccountExplanation => isDari
      ? 'لینک تأیید را با ایمیل ارسال می‌کنیم. تأیید آن حساب و معلومات شخصی شما را حذف می‌کند، دسترسی به کتاب‌ها را می‌بندد و همهٔ دستگاه‌ها را خارج می‌کند. سوابق پرداخت بدون نام و ایمیل باقی می‌مانند. حساب تازه کتاب‌ها را خودکار دریافت نمی‌کند. برای کمک با خریدها، پیش از حذف با ما تماس بگیرید.'
      : isEnglish
      ? 'We will email a confirmation link. Confirming it deletes your account and personal information, removes access to your books and signs out all devices. Payment history is kept without your name or email. A new account does not inherit your books. Contact us before deleting if you need help with purchases.'
      : 'موږ د تایید لینک په برېښنالیک لېږو. په تایید سره ستاسو حساب او شخصي معلومات د تل لپاره ړنګېږي او ټول وسایل وځي.';
  String get passwordChanged => isDari
      ? 'رمز عبور تغییر کرد. دوباره وارد شوید.'
      : isEnglish
      ? 'Password changed. Please sign in again.'
      : 'پټنوم بدل شو. بیا ننوځئ.';
  String get passwordRule => isDari
      ? 'حداقل ۱۲ نویسه با حروف بزرگ و کوچک لاتین، یک عدد و یک علامت استفاده کنید (حداکثر ۷۲ بایت).'
      : isEnglish
      ? 'Use at least 12 characters with uppercase and lowercase letters, a number and a symbol (maximum 72 bytes).'
      : 'لږ تر لږه ۱۲ توري، لوی او کوچني لاتین توري، شمېره او نښه وکاروئ (تر ۷۲ بایټونو).';
  String accountError(int status) => switch (status) {
    202 => emailSent,
    401 => isEnglish ? 'Please sign in again.' : 'بیا ننوځئ.',
    403 => ownerAccountTesting,
    409 =>
      isEnglish
          ? 'This Google identity cannot be linked. Use your existing sign-in method.'
          : 'دا ګوګل حساب نه شي تړل کېدای. په پخوانۍ لاره ننوځئ.',
    422 =>
      isEnglish
          ? 'Check your email and password. New passwords must match and meet the password rules.'
          : 'برېښنالیک او پټنوم وګورئ. نوي پټنومونه باید یو شان او له اصولو سره سم وي.',
    429 =>
      isEnglish
          ? 'Too many attempts. Please wait a minute.'
          : 'ډېرې هڅې شوې. یوه دقیقه صبر وکړئ.',
    _ => accountConnectionError,
  };
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
  String translation(String name) => choose(
    'Translation: $name',
    AppStrings.translation(name),
    'ترجمه: $name',
  );
  String originalAuthor(String name) => choose(
    'Original author: $name',
    AppStrings.originalAuthor(name),
    'نویسندهٔ اصلی: $name',
  );
}
