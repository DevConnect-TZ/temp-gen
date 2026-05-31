<?php

namespace Tests\Feature;

use App\Mail\TransactionSuccessNotification;
use App\Models\Page;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MobilipaPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_mobilipa_payment_order(): void
    {
        PaymentGateway::create([
            'name' => 'mobilipa',
            'display_name' => 'Mobilipa',
            'api_key' => 'sk_live_mobilipa_test',
            'base_url' => 'https://api.mobilipa.store',
            'is_active' => true,
            'description' => 'Mobilipa',
        ]);

        $page = Page::create([
            'title' => 'Mobilipa Page',
            'slug' => 'mobilipa-page',
            'template' => 'template1',
            'price' => 10000,
            'payment_gateway' => 'mobilipa',
            'is_active' => true,
        ]);

        Mail::fake();

        Http::fake([
            'https://api.mobilipa.store/v1/payment/create_order' => Http::response([
                'status' => 'success',
                'message' => 'Payment order created successfully! Push USSD sent to your phone.',
                'data' => [
                    'order_id' => 'sp_696a7e8c101e3',
                    'reference' => 'S20376448003',
                    'amount' => 10000,
                    'currency' => 'TZS',
                    'payment_status' => 'PENDING',
                    'status' => 'PENDING',
                    'creation_date' => '2026-01-16 21:09:49',
                    'msisdn' => '255695123456',
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
        $response->assertJsonPath('data.order_id', 'sp_696a7e8c101e3');
        $response->assertJsonPath('data.reference', 'S20376448003');

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.mobilipa.store/v1/payment/create_order'
                && $request['buyer_phone'] === '255695123456'
                && $request['amount'] === 10000
                && $request['currency'] === 'TZS'
                && $request['buyer_email'] === 'john@example.com'
                && $request['buyer_name'] === 'John Doe';
        });

        $this->assertDatabaseHas('transactions', [
            'page_id' => $page->id,
            'gateway' => 'mobilipa',
            'order_id' => 'sp_696a7e8c101e3',
            'reference' => 'S20376448003',
            'payment_status' => 'PENDING',
            'msisdn' => '255695123456',
        ]);
    }

    public function test_it_checks_a_mobilipa_payment_status(): void
    {
        PaymentGateway::create([
            'name' => 'mobilipa',
            'display_name' => 'Mobilipa',
            'api_key' => 'sk_live_mobilipa_test',
            'base_url' => 'https://api.mobilipa.store',
            'is_active' => true,
            'description' => 'Mobilipa',
        ]);

        $page = Page::create([
            'title' => 'Mobilipa Page',
            'slug' => 'mobilipa-status-page',
            'template' => 'template1',
            'price' => 10000,
            'payment_gateway' => 'mobilipa',
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'page_id' => $page->id,
            'buyer_email' => 'john@example.com',
            'buyer_name' => 'John Doe',
            'buyer_phone' => '255695123456',
            'amount' => 10000,
            'currency' => 'TZS',
            'gateway' => 'mobilipa',
            'payment_status' => 'PENDING',
            'order_id' => 'sp_696a7e8c101e3',
        ]);

        Mail::fake();

        Http::fake([
            'https://api.mobilipa.store/v1/payment/status' => Http::response([
                'status' => 'success',
                'message' => 'Order status retrieved successfully!',
                'data' => [
                    'order_id' => 'sp_696a7e8c101e3',
                    'payment_status' => 'COMPLETED',
                    'transid' => '807399829307',
                    'reference' => '1540671137',
                    'amount' => '10000',
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
            'transaction_id' => '807399829307',
            'reference' => '1540671137',
        ]);

        Mail::assertSent(TransactionSuccessNotification::class);
    }

    public function test_it_handles_mobilipa_cancelled_status(): void
    {
        PaymentGateway::create([
            'name' => 'mobilipa',
            'display_name' => 'Mobilipa',
            'api_key' => 'sk_live_mobilipa_test',
            'base_url' => 'https://api.mobilipa.store',
            'is_active' => true,
            'description' => 'Mobilipa',
        ]);

        $page = Page::create([
            'title' => 'Mobilipa Page',
            'slug' => 'mobilipa-cancelled-page',
            'template' => 'template1',
            'price' => 10000,
            'payment_gateway' => 'mobilipa',
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'page_id' => $page->id,
            'buyer_email' => 'john@example.com',
            'buyer_name' => 'John Doe',
            'buyer_phone' => '255695123456',
            'amount' => 10000,
            'currency' => 'TZS',
            'gateway' => 'mobilipa',
            'payment_status' => 'PENDING',
            'order_id' => 'sp_696a7e8c101e3',
        ]);

        Mail::fake();

        Http::fake([
            'https://api.mobilipa.store/v1/payment/status' => Http::response([
                'status' => 'success',
                'message' => 'Order status retrieved successfully!',
                'data' => [
                    'order_id' => 'sp_696a7e8c101e3',
                    'payment_status' => 'CANCELLED',
                    'transid' => '807399829307',
                    'reference' => '1540671137',
                    'amount' => '10000',
                ],
            ]),
        ]);

        $response = $this->postJson(route('payments.check-status'), [
            'transaction_id' => $transaction->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('payment_status', 'CANCELLED');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'payment_status' => 'CANCELLED',
        ]);

        Mail::assertNothingSent();
    }
}
