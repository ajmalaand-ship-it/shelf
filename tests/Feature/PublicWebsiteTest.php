<?php
namespace Tests\Feature;

use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    public function test_all_localized_pages_have_unique_seo_and_same_page_language_navigation(): void
    {
        config(['app.url' => 'https://shelf.example']);
        $titles = [];
        foreach (['ps', 'fa', 'en'] as $locale) {
            foreach (['', '/about', '/how-it-works', '/download', '/support', '/terms'] as $page) {
                $body = $this->get('/'.$locale.$page)->assertOk()
                    ->assertSee('lang="'.$locale.'"', false)
                    ->assertSee('dir="'.($locale === 'en' ? 'ltr' : 'rtl').'"', false)
                    ->assertSee('rel="canonical" href="https://shelf.example/'.$locale.$page.'"', false)
                    ->assertSee('property="og:image"', false)
                    ->assertSee('Shelf — شیلف')->assertDontSee('ajmalaand@gmail.com')->getContent();
                foreach (['ps', 'fa', 'en'] as $other) {
                    $this->assertStringContainsString('href="/'.$other.$page.'"', $body);
                    $this->assertStringContainsString('hreflang="'.$other.'"', $body);
                }
                preg_match('/<title>(.*?)<\/title>/u', $body, $match);
                $titles[] = $match[1];
                $this->assertStringNotContainsString('play.google.com/store/apps', $body);
                $this->assertStringNotContainsString('apps.apple.com/', $body);
            }
        }
        $this->assertCount(18, array_unique($titles));
        $this->get('/')->assertOk()->assertSee('lang="ps"', false);
        $this->get('/de')->assertNotFound();
        $this->get('/en/not-a-page')->assertNotFound();
    }

    public function test_sitemap_robots_and_existing_public_and_protected_routes(): void
    {
        config(['app.url' => 'https://shelf.example']);
        $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();
        $sitemap = simplexml_load_string($xml);
        $this->assertNotFalse($sitemap);
        $this->assertCount(19, $sitemap->url);
        $this->assertStringNotContainsString('/api/', $xml);
        $this->assertStringNotContainsString('/account/', $xml);
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: https://shelf.example/sitemap.xml')->assertSee('Disallow: /admin');
        $this->get('/privacy')->assertOk()->assertSee('Privacy Policy')->assertSee('editor@hindara.com');
        $this->get('/account/delete')->assertOk()->assertSee('Request deletion')->assertSee('editor@hindara.com');
        $this->getJson('/api/library')->assertUnauthorized();
        $this->get('/admin')->assertRedirect();
    }
}
