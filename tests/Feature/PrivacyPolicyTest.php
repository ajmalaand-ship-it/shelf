<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyPolicyTest extends TestCase
{
    public function test_privacy_policy_is_public_and_contains_approved_identity_and_disclosures(): void
    {
        $response = $this->get('/privacy/');

        $response
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('پېڅوَل — Pitswal', false)
            ->assertSee('اجمل اند بشپړه شاعري', false)
            ->assertSee('Hindara')
            ->assertSee('ajmalaand@gmail.com')
            ->assertSee('does not currently display advertising')
            ->assertSee('does not sell or rent personal data')
            ->assertSee('no end-user accounts')
            ->assertSee('Google Play Billing')
            ->assertSee('RevenueCat')
            ->assertSee('anonymous RevenueCat App User ID')
            ->assertDontSee('/home/ajmalaand')
            ->assertDontSee('.env');
    }
}
