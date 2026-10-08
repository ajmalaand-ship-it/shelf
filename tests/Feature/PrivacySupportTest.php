<?php
namespace Tests\Feature;

use Tests\TestCase;

class PrivacySupportTest extends TestCase
{
    public function test_policy_discloses_search_provider_processing_and_unresolved_retention(): void
    {
        $this->get('/privacy')->assertOk()
            ->assertSee('Privacy Policy for Shelf, operated by Ajmal Aand.')
            ->assertSee('Shelf is operated personally by Ajmal Aand.')
            ->assertDontSee('published by Hindara')
            ->assertSee('October 8, 2026')->assertSee('search queries')
            ->assertSee('purchase analytics')->assertSee('Shelf reader identifier')
            ->assertSee('Financial retention is still under review')
            ->assertSee('90-day log and backup periods do not apply to financial history')
            ->assertSee('a queued request is not proof')
            ->assertSee('matching emails alone is insufficient')
            ->assertDontSee('We do not use advertising or analytics SDKs');
        $this->get('/account/delete')->assertOk()->assertSee('Request deletion')
            ->assertSee('ajmalaand@gmail.com')->assertSee('one hour');
    }
}
