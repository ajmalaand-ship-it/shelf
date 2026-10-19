{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach($pages as $page)
@foreach($locales as $locale)
<url><loc>{{ rtrim(config('app.url'), '/') }}/{{ $locale }}{{ $page === 'home' ? '' : '/'.$page }}</loc>
@foreach($locales as $alternate)
<xhtml:link rel="alternate" hreflang="{{ $alternate }}" href="{{ rtrim(config('app.url'), '/') }}/{{ $alternate }}{{ $page === 'home' ? '' : '/'.$page }}" />
@endforeach
</url>
@endforeach
@endforeach
<url><loc>{{ rtrim(config('app.url'), '/') }}/privacy</loc></url>
</urlset>
