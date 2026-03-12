<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppyPayWebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_rejects_invalid_signature(): void
    {
        config([
            'services.appypay.webhook_secret' => 'test-secret',
        ]);

        $payload = json_encode([
            'eventId' => 'evt-invalid-signature',
            'merchantTransactionId' => 'tx-invalid-signature',
        ], JSON_THROW_ON_ERROR);

        $response = $this->call(
            'POST',
            '/api/webhooks/appypay',
            [],
            [],
            [],
            [
                'HTTP_X_APPYPAY_SIGNATURE' => 'invalid',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid signature',
            ]);
    }

    public function test_webhook_rejects_get_requests(): void
    {
        $this->getJson('/api/webhooks/appypay')
            ->assertStatus(405);
    }

    public function test_webhook_replays_are_rejected_with_idempotency(): void
    {
        config([
            'services.appypay.webhook_secret' => 'test-secret',
        ]);

        $payload = json_encode([
            'eventId' => 'evt-replay',
            'merchantTransactionId' => 'tx-replay',
        ], JSON_THROW_ON_ERROR);

        $signature = hash_hmac('sha256', $payload, 'test-secret');

        $firstResponse = $this->call(
            'POST',
            '/api/webhooks/appypay',
            [],
            [],
            [],
            [
                'HTTP_X_APPYPAY_SIGNATURE' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $firstResponse->assertOk()
            ->assertJson([
                'status' => 'ok',
            ]);

        $this->assertDatabaseHas('webhook_events', [
            'provider' => 'appypay',
            'event_id' => 'evt-replay',
        ]);

        $duplicateResponse = $this->call(
            'POST',
            '/api/webhooks/appypay',
            [],
            [],
            [],
            [
                'HTTP_X_APPYPAY_SIGNATURE' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $duplicateResponse->assertStatus(202)
            ->assertJson([
                'status' => 'duplicate',
            ]);
    }
}
