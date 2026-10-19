@php
$base = rtrim(config('app.url'), '/');
$path = $page === 'home' ? '' : '/'.$page;
$canonical = $base.'/'.$locale.$path;
$entry = $text['pages'][$page];
@endphp
<!doctype html>
<html lang="{{ $locale }}" dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $entry[0] }} — Shelf · شیلف</title>
<meta name="description" content="{{ $entry[1] }}">
<link rel="canonical" href="{{ $canonical }}">
@foreach($languages as $code => $copy)
<link rel="alternate" hreflang="{{ $code }}" href="{{ $base.'/'.$code.$path }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $base.'/ps'.$path }}">
<meta property="og:type" content="website"><meta property="og:site_name" content="Shelf — شیلف">
<meta property="og:title" content="{{ $entry[0] }} — Shelf"><meta property="og:description" content="{{ $entry[1] }}">
<meta property="og:url" content="{{ $canonical }}"><meta property="og:image" content="{{ $base }}/website/shelf-social.png">
<meta property="og:image:width" content="1024"><meta property="og:image:height" content="1024">
<meta name="theme-color" content="#f7f3eb"><link rel="icon" href="/favicon.ico">
<link rel="stylesheet" href="/website/site.css">
</head>
<body>
<a class="skip" href="#main">{{ $text['skip'] }}</a>
<header class="site-header wrap">
<a class="brand" href="/{{ $locale }}" aria-label="Shelf — شیلف"><img src="/website/shelf-logo.png" width="322" height="80" alt="Shelf — شیلف"></a>
<nav class="main-nav" aria-label="{{ $text['nav']['home'] }}">
@foreach(['about','how-it-works','download','support'] as $target)
<a href="/{{ $locale }}/{{ $target }}" @if($page === $target) aria-current="page" @endif>{{ $text['nav'][$target] }}</a>
@endforeach
</nav>
<nav class="languages" aria-label="{{ $text['language'] }}">
@foreach($languages as $code => $copy)
<a lang="{{ $code }}" href="/{{ $code }}{{ $path }}" hreflang="{{ $code }}" @if($locale === $code) aria-current="true" @endif>{{ $copy['name'] }}</a>
@endforeach
</nav>
</header>
<main id="main">
@if($page === 'home')
<section class="hero wrap">
<div class="hero-copy"><p class="eyebrow">{{ $text['eyebrow'] }}</p><h1>{{ $text['headline'] }}</h1>
<p class="intro">{{ $text['intro'] }}</p>
<div class="actions"><a class="button" href="/{{ $locale }}/download">{{ $text['cta'] }} <span aria-hidden="true">↗</span></a><a class="text-link" href="/{{ $locale }}/how-it-works">{{ $text['learn'] }}</a></div>
<p class="availability"><span class="dot" aria-hidden="true"></span>{{ $text['platform'] }} · {{ $text['soon'] }}</p></div>
<div class="reading-art" aria-hidden="true"><div class="orbit"></div><div class="book-back"></div><div class="book"><span class="book-edition">S H E L F</span><span class="book-mark">شیلف</span><span class="book-rule"></span><span class="book-slogan" lang="ps" dir="rtl">کتاب مو ژوند بدلوي</span><span class="book-bottom">{{ $text['artSub'] }}</span></div><span class="art-caption">{{ $text['artTitle'] }}</span></div>
</section>
<div class="slogan-band"><p lang="ps" dir="rtl">کتاب مو ژوند بدلوي</p></div>
<section class="wrap journey"><p class="eyebrow">{{ $text['edition'] }}</p><h2>{{ $text['stepsTitle'] }}</h2><div class="steps">
@foreach($text['steps'] as $step)<article><span class="step-number" aria-hidden="true">0{{ $loop->iteration }}</span><h3>{{ $step[0] }}</h3><p>{{ $step[1] }}</p></article>@endforeach
</div></section>
@else
<section class="page-head wrap"><p class="eyebrow">{{ $text['nav'][$page] }}</p><h1>{{ $entry[0] }}</h1><p class="intro">{{ $entry[1] }}</p></section>
<section class="wrap prose" aria-label="{{ $text['nav'][$page] }}">
@foreach($entry[2] as $section)<article><h2>{{ $section[0] }}</h2><p>{{ $section[1] }}</p></article>@endforeach
@if($page === 'support')<div class="support-actions"><a class="button" href="mailto:editor@hindara.com"><bdi>editor@hindara.com</bdi></a><a href="{{ route('account.delete') }}">{{ $text['delete'] }} <span lang="en">(English)</span></a><a href="{{ route('privacy') }}">{{ $text['privacy'] }} <span lang="en">(English)</span></a></div>@endif
</section>
@endif
@if(in_array($page, ['home', 'download']))
<section class="wrap download-panel"><div><p class="eyebrow">{{ $text['soon'] }}</p><h2>{{ $text['nav']['download'] }}</h2><p>{{ $text['notice'] }}</p></div><div class="platforms"><div><strong lang="en" dir="ltr">Android · Google Play</strong><span>{{ $text['soon'] }}</span></div><div><strong lang="en" dir="ltr">iPhone · App Store</strong><span>{{ $text['soon'] }}</span></div></div></section>
@endif
</main>
<footer class="site-footer wrap"><div><strong>Shelf · شیلف</strong><p>{{ $text['footer'] }}</p><small>© {{ date('Y') }} <bdi>Shelf — شیلف</bdi></small></div><nav aria-label="{{ $text['nav']['support'] }}">
<a href="/{{ $locale }}/about">{{ $text['nav']['about'] }}</a><a href="/{{ $locale }}/support">{{ $text['nav']['support'] }}</a><a href="/{{ $locale }}/terms">{{ $text['nav']['terms'] }}</a><a href="{{ route('privacy') }}">{{ $text['privacy'] }} <span lang="en">(English)</span></a><a href="{{ route('account.delete') }}">{{ $text['delete'] }} <span lang="en">(English)</span></a>
</nav></footer>
</body></html>
