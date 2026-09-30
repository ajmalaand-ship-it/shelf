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
            ->assertSee('ajmalaand@gmail.com')
            ->assertSee('optional display name')
            ->assertSee('password hash')
            ->assertSee('does not collect payment-card details')
            ->assertSee('accounting history is retained')
            ->assertSee('every 30 days')
            ->assertSee('account-deletion page')
            ->assertSee('14 most recent daily backups')
            ->assertSee('RevenueCat')
            ->assertDontSee('no end-user accounts')
            ->assertDontSee('/home/ajmalaand')
            ->assertDontSee('.env');
    }
}
