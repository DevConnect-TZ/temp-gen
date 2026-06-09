<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PaymentGateway;
use App\Models\PesaLinkAccount;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PesaLinkPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_pesalink_payment_order_with_sub_account(): void
    {
        $account = PesaLinkAccount::create([
            'name' => 'Sub-Account A',
            'api_key' => 'sub-account-api-key',
            'base_url' => 'https://pesalink.online/api',
            'is_active' => true,
        ]);

        PaymentGateway::create([
            'name' => 'pesalink',
            'display_name' => 'PesaLink',
            'api_key' => 'main-gateway-key',
            'base_url' => 'https://pesalink.online/api',
            'is_active' => true,
            'description' => 'PesaLink',
        ]);

        $page = Page::create([
            'title' => 'PesaLink Page',
            'slug' => 'pesalink-sub-page',
            'template' => 'template1',
            'price' => 3000,
            'payment_gateway' => 'pesalink',
            'is_active' => true,
            'pesalink_account_id' => $account->id,
        ]);

        Mail::fake();

        Http::fake([
            'https://pesalink.online/api/create-transaction' => Http::response([
                'status' => 'success',
                'data' => [
                    'tranID' => 'pl_sub_xyz99',
                    'amount' => 3000,
                    'number' => '255712345678',
                    'network' => 'VODACOM',
                    'status' => 'PENDING',
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('payments.create-order'), [
            'page_id' => $page->id,
            'buyer_phone' => '0712345678',
            'buyer_name' => 'Jane Doe',
            'buyer_email' => 'jane@example.com',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.tranid', 'pl_sub_xyz99');

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://pesalink.online/api/create-transaction'
                && $request->hasHeader('Authorization')
                && str_contains($request->header('Authorization')[0], 'sub-account-api-key')
                && $request['number'] === '255712345678'
                && $request['amount'] === 3000;
        });

        $this->assertDatabaseHas('transactions', [
            'page_id' => $page->id,
            'gateway' => 'pesalink',
            'order_id' => 'pl_sub_xyz99',
            'payment_status' => 'PENDING',
            'pesalink_account_id' => $account->id,
        ]);
    }

    public function test_it_falls_back_to_gateway_api_key_when_no_sub_account(): void
    {
        PaymentGateway::create([
            'name' => 'pesalink',
            'display_name' => 'PesaLink',
            'api_key' => 'main-gateway-key-only',
            'base_url' => 'https://pesalink.online/api',
            'is_active' => true,
            'description' => 'PesaLink',
        ]);

        $page = Page::create([
            'title' => 'PesaLink No Sub',
            'slug' => 'pesalink-no-sub',
            'template' => 'template1',
            'price' => 2000,
            'payment_gateway' => 'pesalink',
            'is_active' => true,
            'pesalink_account_id' => null,
        ]);

        Mail::fake();

        Http::fake([
            'https://pesalink.online/api/create-transaction' => Http::response([
                'status' => 'success',
                'data' => [
                    'tranID' => 'pl_main_abc12',
                    'amount' => 2000,
                    'number' => '255787654321',
                    'network' => 'TIGO',
                    'status' => 'PENDING',
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('payments.create-order'), [
            'page_id' => $page->id,
            'buyer_phone' => '0787654321',
            'buyer_name' => 'No Sub',
            'buyer_email' => 'nosub@example.com',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://pesalink.online/api/create-transaction'
                && str_contains($request->header('Authorization')[0], 'main-gateway-key-only');
        });

        $this->assertDatabaseHas('transactions', [
            'gateway' => 'pesalink',
            'pesalink_account_id' => null,
        ]);
    }

    public function test_it_checks_pesalink_payment_status_with_sub_account(): void
    {
        $account = PesaLinkAccount::create([
            'name' => 'Sub-Account B',
            'api_key' => 'sub-account-b-key',
            'base_url' => 'https://pesalink.online/api',
            'is_active' => true,
        ]);

        PaymentGateway::create([
            'name' => 'pesalink',
            'display_name' => 'PesaLink',
            'api_key' => 'gateway-key-b',
            'base_url' => 'https://pesalink.online/api',
            'is_active' => true,
            'description' => 'PesaLink',
        ]);

        $page = Page::create([
            'title' => 'PesaLink Status',
            'slug' => 'pesalink-status',
            'template' => 'template1',
            'price' => 4000,
            'payment_gateway' => 'pesalink',
            'is_active' => true,
            'pesalink_account_id' => $account->id,
        ]);

        $transaction = Transaction::create([
            'page_id' => $page->id,
            'buyer_email' => 'status@example.com',
            'buyer_name' => 'Status Test',
            'buyer_phone' => '255765432109',
            'amount' => 4000,
            'currency' => 'TZS',
            'gateway' => 'pesalink',
            'payment_status' => 'PENDING',
            'order_id' => 'pl_stat_444',
            'pesalink_account_id' => $account->id,
        ]);

        Mail::fake();

        Http::fake([
            'https://pesalink.online/api/status-transaction*' => Http::response([
                'status' => 'success',
                'data' => [
                    'tranid' => 'pl_stat_444',
                    'payment_status' => 'COMPLETED',
                    'amount' => 4000,
                    'network' => 'VODACOM',
                ],
            ]),
        ]);

        $response = $this->postJson(route('payments.check-status'), [
            'transaction_id' => $transaction->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('payment_status', 'COMPLETED');

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://pesalink.online/api/status-transaction?tranid=pl_stat_444'
                && str_contains($request->header('Authorization')[0], 'sub-account-b-key');
        });

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'payment_status' => 'COMPLETED',
            'pesalink_account_id' => $account->id,
        ]);

        Mail::assertNothingSent();
    }

    public function test_it_rejects_pesalink_order_when_sub_account_is_inactive(): void
    {
        $account = PesaLinkAccount::create([
            'name' => 'Inactive Sub',
            'api_key' => 'inactive-sub-key',
            'base_url' => 'https://pesalink.online/api',
            'is_active' => false,
        ]);

        PaymentGateway::create([
            'name' => 'pesalink',
            'display_name' => 'PesaLink',
            'api_key' => 'main-gateway-key',
            'base_url' => 'https://pesalink.online/api',
            'is_active' => true,
            'description' => 'PesaLink',
        ]);

        $page = Page::create([
            'title' => 'PesaLink Inactive',
            'slug' => 'pesalink-inactive',
            'template' => 'template1',
            'price' => 1000,
            'payment_gateway' => 'pesalink',
            'is_active' => true,
            'pesalink_account_id' => $account->id,
        ]);

        Http::fake();

        $response = $this->postJson(route('payments.create-order'), [
            'page_id' => $page->id,
            'buyer_phone' => '0711111111',
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('message', 'PesaLink sub-account is not found or inactive.');

        $this->assertDatabaseMissing('transactions', [
            'gateway' => 'pesalink',
            'pesalink_account_id' => $account->id,
        ]);
    }
}
