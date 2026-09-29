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
            ->assertSee('no sales, payment-card or refund records')
            ->assertSee('account-deletion page')
            ->assertSee('14 most recent daily backups')
            ->assertDontSee('RevenueCat')
            ->assertDontSee('no end-user accounts')
            ->assertDontSee('/home/ajmalaand')
            ->assertDontSee('.env');
    }
}
