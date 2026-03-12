<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminMasterAuthenticationTest extends TestCase
{
    public function test_admin_pin_is_validated_against_configured_hash(): void
    {
        config([
            'security.admin_master_secret' => Hash::make('654321'),
        ]);

        $response = $this->postJson('/verify-pin', [
            'pin' => '654321',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue(
            (bool) session(config('admin.session_key', 'is_admin_master'))
        );
    }

    public function test_admin_pin_rejects_invalid_secret(): void
    {
        config([
            'security.admin_master_secret' => Hash::make('654321'),
        ]);

        $response = $this->postJson('/verify-pin', [
            'pin' => '111111',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }
}
