<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class PublicWebsiteController extends Controller
{
    public const LANGUAGES = ['ps', 'fa', 'en'];
    public const PAGES = ['home', 'about', 'how-it-works', 'download', 'support', 'terms'];

    public function page(string $locale = 'ps', string $page = 'home'): Response
    {
        abort_unless(in_array($locale, self::LANGUAGES, true) && in_array($page, self::PAGES, true), 404);
        $copy = json_decode(file_get_contents(resource_path('content/website.json')), true, 512, JSON_THROW_ON_ERROR);
        return response()->view('website.page', ['locale' => $locale, 'page' => $page,
            'text' => $copy[$locale], 'languages' => $copy]);
    }

    public function sitemap(): Response
    {
        return response()->view('website.sitemap', ['locales' => self::LANGUAGES, 'pages' => self::PAGES])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $base = rtrim(config('app.url'), '/');
        return response("User-agent: *\nDisallow: /admin\nDisallow: /api/\nDisallow: /account/\nDisallow: /media/\nSitemap: {$base}/sitemap.xml\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
