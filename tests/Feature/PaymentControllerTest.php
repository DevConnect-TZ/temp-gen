<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_fastlipa_payment_order(): void
    {
        PaymentGateway::create([
            'name' => 'fastlipa',
            'display_name' => 'FastLipa',
            'api_key' => 'fastlipa-test-token',
            'base_url' => 'https://api.fastlipa.com/api',
            'is_active' => true,
            'description' => 'FastLipa',
        ]);

        $page = Page::create([
            'title' => 'FastLipa Page',
            'slug' => 'fastlipa-page',
            'template' => 'template1',
            'price' => 5000,
            'payment_gateway' => 'fastlipa',
            'is_active' => true,
        ]);

        Mail::fake();

        Http::fake([
            'https://api.fastlipa.com/api/create-transaction' => Http::response([
                'status' => true,
                'message' => 'Payment created',
                'data' => [
                    'tranID' => 'pay_aB3xYz9k',
                    'amount' => 5000,
                    'number' => '255695123456',
                    'network' => 'AIRTEL',
                    'status' => 'PENDING',
                    'time' => '2025-09-01T10:30:00.000000Z',
                ],
            ], 201),
        ]);

        $response = $this->postJson(route('payments.create-order'), [
            'page_id' => $page->id,
            'buyer_phone' => '0695123456',
            'buyer_name' => 'John Doe',
            'buyer_email' => 'john@example.com',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.tranid', 'pay_aB3xYz9k');

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.fastlipa.com/api/create-transaction'
                && $request['number'] === '255695123456'
                && $request['amount'] === 5000
                && $request['name'] === 'John Doe';
        });

        $this->assertDatabaseHas('transactions', [
            'page_id' => $page->id,
            'gateway' => 'fastlipa',
            'order_id' => 'pay_aB3xYz9k',
            'payment_status' => 'PENDING',
            'msisdn' => '255695123456',
        ]);
    }

    public function test_it_checks_a_fastlipa_payment_status(): void
    {
        PaymentGateway::create([
            'name' => 'fastlipa',
            'display_name' => 'FastLipa',
            'api_key' => 'fastlipa-test-token',
            'base_url' => 'https://api.fastlipa.com/api',
            'is_active' => true,
            'description' => 'FastLipa',
        ]);

        $page = Page::create([
            'title' => 'FastLipa Page',
            'slug' => 'fastlipa-status-page',
            'template' => 'template1',
            'price' => 5000,
            'payment_gateway' => 'fastlipa',
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'page_id' => $page->id,
            'buyer_email' => 'john@example.com',
            'buyer_name' => 'John Doe',
            'buyer_phone' => '255695123456',
            'amount' => 5000,
            'currency' => 'TZS',
            'gateway' => 'fastlipa',
            'payment_status' => 'PENDING',
            'order_id' => 'pay_aB3xYz9k',
        ]);

        Mail::fake();

        Http::fake([
            'https://api.fastlipa.com/api/status-transaction*' => Http::response([
                'status' => true,
                'message' => 'Payment status retrieved',
                'data' => [
                    'tranid' => 'pay_aB3xYz9k',
                    'payment_status' => 'COMPLETED',
                    'amount' => 5000,
                    'network' => 'AIRTEL',
                    'time' => '2025-09-01T10:30:00.000000Z',
                ],
            ]),
        ]);

        $response = $this->postJson(route('payments.check-status'), [
            'transaction_id' => $transaction->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('payment_status', 'COMPLETED');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'payment_status' => 'COMPLETED',
            'transaction_id' => 'pay_aB3xYz9k',
            'channel' => 'AIRTEL',
        ]);

        Mail::assertNothingSent();
    }
}
