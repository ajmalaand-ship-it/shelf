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
            ->assertSee('Shelf', false)
            ->assertDontSee('Pitswal')
            ->assertSee('editor@hindara.com')
            ->assertSee('optional display name')
            ->assertSee('password hash')
            ->assertSee('does not collect payment-card details')
            ->assertSee('Automatic financial expiry is not implemented')
            ->assertSee('every 30 days')
            ->assertSee('account-deletion page')
            ->assertSee('Local backups retain 14 copies')
            ->assertSee('RevenueCat')
            ->assertDontSee('no end-user accounts')
            ->assertDontSee('/home/ajmalaand')
            ->assertDontSee('.env');
    }
}
