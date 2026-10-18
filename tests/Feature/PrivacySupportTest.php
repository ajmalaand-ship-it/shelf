<?php
namespace Tests\Feature;

use Tests\TestCase;

class PrivacySupportTest extends TestCase
{
    public function test_policy_discloses_search_provider_processing_and_approved_retention(): void
    {
        $this->get('/privacy')->assertOk()
            ->assertSee('Privacy Policy for Shelf, operated by Ajmal Aand.')
            ->assertSee('Shelf is operated personally by Ajmal Aand.')
            ->assertDontSee('published by Hindara')
            ->assertSee('October 16, 2026')->assertSee('search queries')
            ->assertSee('purchase analytics')->assertSee('Shelf reader identifier')
            ->assertSee('three years after the relevant tax return is actually filed')
            ->assertSee('Automatic financial expiry is not implemented')
            ->assertDontSee('Financial retention is still under review')
            ->assertSee('90-day log and backup periods do not apply to financial history')
            ->assertSee('a queued request is not proof')
            ->assertSee('matching emails alone is insufficient')
            ->assertDontSee('We do not use advertising or analytics SDKs');
        $this->get('/account/delete')->assertOk()->assertSee('Request deletion')
            ->assertSee('ajmalaand@gmail.com')->assertSee('one hour')->assertSee('three years after the relevant tax return is actually filed')
            ->assertSee('Automatic financial expiry is not implemented')->assertSee('Apple deleted-account purchase recovery is not yet available');
    }
}
