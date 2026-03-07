<?php

namespace Tests\Feature;

use App\Services\PaymentInjectionService;
use Tests\TestCase;

class PaymentInjectionResetEndpointTest extends TestCase
{
    private PaymentInjectionService $injectionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->injectionService = app(PaymentInjectionService::class);
        $this->injectionService->resetDailyCount();
    }

    public function test_reset_endpoint_resets_counter_to_zero(): void
    {
        // Increment counter to 5
        for ($i = 0; $i < 5; $i++) {
            $this->injectionService->incrementTransactionCount();
        }

        // Verify counter is at 5
        $this->assertEquals(5, $this->injectionService->getTodayTransactionCount());

        // Call reset endpoint
        $response = $this->getJson('/v1/reset');

        // Verify response
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Payment injection counter has been reset to zero',
            'count' => 0,
        ]);

        // Verify counter is actually reset
        $this->assertEquals(0, $this->injectionService->getTodayTransactionCount());
    }

    public function test_status_endpoint_returns_current_status(): void
    {
        // Increment counter to 3
        for ($i = 0; $i < 3; $i++) {
            $this->injectionService->incrementTransactionCount();
        }

        // Call status endpoint
        $response = $this->getJson('/v1/injection-status');

        // Verify response structure
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'current_count' => 3,
                'should_use_secondary_key' => true, // Count 3 is odd, so secondary key
                'is_injectable' => true,
                'injection_limit' => 8,
                'transactions_remaining' => 5,
            ],
        ]);
    }

    public function test_status_endpoint_shows_injections_complete(): void
    {
        // Increment counter to 8 (limit reached)
        for ($i = 0; $i < 8; $i++) {
            $this->injectionService->incrementTransactionCount();
        }

        // Call status endpoint
        $response = $this->getJson('/v1/injection-status');

        // Verify response
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'current_count' => 8,
                'should_use_secondary_key' => false,
                'is_injectable' => false,
                'transactions_remaining' => 0,
            ],
        ]);
    }

    public function test_reset_endpoint_response_includes_timestamp(): void
    {
        $response = $this->getJson('/v1/reset');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'count',
            'timestamp',
        ]);

        // Verify timestamp is ISO8601 format
        $data = $response->json();
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/',
            $data['timestamp']
        );
    }

    public function test_status_endpoint_response_includes_timestamp(): void
    {
        $response = $this->getJson('/v1/injection-status');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'current_count',
                'should_use_secondary_key',
                'is_injectable',
                'injection_limit',
                'transactions_remaining',
                'timestamp',
            ],
        ]);
    }
}
