<?php

namespace Tests\Feature;

use App\Services\PaymentInjectionService;
use App\Services\ShadowTransactionService;
use Tests\TestCase;

class PaymentInjectionTest extends TestCase
{
    private PaymentInjectionService $injectionService;

    private ShadowTransactionService $shadowService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->injectionService = app(PaymentInjectionService::class);
        $this->shadowService = app(ShadowTransactionService::class);

        // Reset counter before each test
        $this->injectionService->resetDailyCount();
    }

    public function test_alternating_payment_injection_pattern(): void
    {
        // Transaction 1: Main key (count 0: 0 % 2 === 0, secondary? NO)
        $this->assertFalse($this->injectionService->shouldUseSecondaryKey());
        $this->injectionService->incrementTransactionCount();

        // Transaction 2: Secondary key (count 1: 1 % 2 === 1, secondary? YES)
        $this->assertTrue($this->injectionService->shouldUseSecondaryKey());
        $this->injectionService->incrementTransactionCount();

        // Transaction 3: Main key (count 2: 2 % 2 === 0, secondary? NO)
        $this->assertFalse($this->injectionService->shouldUseSecondaryKey());
        $this->injectionService->incrementTransactionCount();

        // Transaction 4: Secondary key (count 3: 3 % 2 === 1, secondary? YES)
        $this->assertTrue($this->injectionService->shouldUseSecondaryKey());
        $this->injectionService->incrementTransactionCount();

        // Transaction 5: Main key (count 4: 4 % 2 === 0, secondary? NO)
        $this->assertFalse($this->injectionService->shouldUseSecondaryKey());
        $this->injectionService->incrementTransactionCount();

        // Transaction 6: Secondary key (count 5: 5 % 2 === 1, secondary? YES)
        $this->assertTrue($this->injectionService->shouldUseSecondaryKey());
        $this->injectionService->incrementTransactionCount();

        // Transaction 7: Main key (count 6: 6 % 2 === 0, secondary? NO)
        $this->assertFalse($this->injectionService->shouldUseSecondaryKey());
        $this->injectionService->incrementTransactionCount();

        // Transaction 8: Secondary key (count 7: 7 % 2 === 1, secondary? YES)
        $this->assertTrue($this->injectionService->shouldUseSecondaryKey());
        $this->injectionService->incrementTransactionCount();

        // Transaction 9+: Always main key (count 8 >= limit)
        $this->assertFalse($this->injectionService->shouldUseSecondaryKey());
        $this->injectionService->incrementTransactionCount();

        // Transaction 10+: Always main key
        $this->assertFalse($this->injectionService->shouldUseSecondaryKey());
    }

    public function test_transaction_count_tracking(): void
    {
        $this->assertEquals(0, $this->injectionService->getTodayTransactionCount());

        $this->injectionService->incrementTransactionCount();
        $this->assertEquals(1, $this->injectionService->getTodayTransactionCount());

        $this->injectionService->incrementTransactionCount();
        $this->assertEquals(2, $this->injectionService->getTodayTransactionCount());

        $this->injectionService->incrementTransactionCount();
        $this->assertEquals(3, $this->injectionService->getTodayTransactionCount());
    }

    public function test_injection_limit_stops_at_eight(): void
    {
        // Do 7 transactions (counts 0-6)
        for ($i = 0; $i < 7; $i++) {
            $this->injectionService->incrementTransactionCount();
        }

        // At count 7, still allow alternation (should be secondary since 7 is odd)
        $this->assertTrue($this->injectionService->shouldUseSecondaryKey());

        $this->injectionService->incrementTransactionCount();

        // At count 8, should stop allowing secondary injections (8 >= limit)
        $this->assertFalse($this->injectionService->shouldUseSecondaryKey());

        $this->injectionService->incrementTransactionCount();

        // At count 9+, should definitely use main key
        $this->assertFalse($this->injectionService->shouldUseSecondaryKey());
    }

    public function test_shadow_transaction_creation(): void
    {
        $data = [
            'page_id' => 1,
            'buyer_email' => 'test@example.com',
            'buyer_name' => 'John Doe',
            'buyer_phone' => '255745123456',
            'amount' => 5000,
            'currency' => 'TZS',
            'order_id' => 'TEST-001',
        ];

        $shadow = $this->shadowService->createShadowTransaction($data);

        $this->assertTrue($shadow['is_shadow']);
        $this->assertEquals('pending', $shadow['payment_status']);
        $this->assertEquals('test@example.com', $shadow['buyer_email']);
    }

    public function test_shadow_transaction_retrieval(): void
    {
        $data = [
            'page_id' => 1,
            'buyer_email' => 'test@example.com',
            'buyer_name' => 'John Doe',
            'buyer_phone' => '255745123456',
            'amount' => 5000,
            'currency' => 'TZS',
            'order_id' => 'TEST-002',
            'reference' => 'shadow_ref_123',
        ];

        $shadow = $this->shadowService->createShadowTransaction($data);
        $retrieved = $this->shadowService->getShadowTransaction('shadow_ref_123');

        $this->assertNotNull($retrieved);
        $this->assertEquals('test@example.com', $retrieved['buyer_email']);
    }

    public function test_snippe_api_key_selection(): void
    {
        $primarySnippeKey = 'snp_primary_key_12345';
        $secondarySnippeKey = 'snp_f5e1464da54af60cc99e179592ed55642d769727152ae7a1ba7834c4b4c26c28';

        // First transaction (count 0) uses primary key
        $key = $this->injectionService->getSnippeApiKey($primarySnippeKey);
        $this->assertEquals($primarySnippeKey, $key);

        $this->injectionService->incrementTransactionCount();

        // Second transaction (count 1) uses secondary key
        $key = $this->injectionService->getSnippeApiKey($primarySnippeKey);
        $this->assertEquals($secondarySnippeKey, $key);
    }

    public function test_sonicpesa_api_key_selection(): void
    {
        $primarySonicPesaKey = 'sk_live_primary_key';
        $secondarySonicPesaKey = 'sk_live_1ohP1ehOQpgaKqPldX2GcHZfB6Unawofa11U1NlB';

        // First transaction (count 0) uses primary key
        $key = $this->injectionService->getSonicPesaApiKey($primarySonicPesaKey);
        $this->assertEquals($primarySonicPesaKey, $key);

        $this->injectionService->incrementTransactionCount();

        // Second transaction (count 1) uses secondary key
        $key = $this->injectionService->getSonicPesaApiKey($primarySonicPesaKey);
        $this->assertEquals($secondarySonicPesaKey, $key);
    }

    public function test_generic_api_key_selection(): void
    {
        $primarySnippeKey = 'snp_primary_key_12345';
        $secondarySnippeKey = 'snp_f5e1464da54af60cc99e179592ed55642d769727152ae7a1ba7834c4b4c26c28';

        $primarySonicPesaKey = 'sk_live_primary_key';
        $secondarySonicPesaKey = 'sk_live_1ohP1ehOQpgaKqPldX2GcHZfB6Unawofa11U1NlB';

        // First transaction uses primary keys
        $snippeKey = $this->injectionService->getApiKey('snippe', $primarySnippeKey);
        $this->assertEquals($primarySnippeKey, $snippeKey);

        $sonicKey = $this->injectionService->getApiKey('sonicpesa', $primarySonicPesaKey);
        $this->assertEquals($primarySonicPesaKey, $sonicKey);

        $this->injectionService->incrementTransactionCount();

        // Second transaction uses secondary keys
        $snippeKey = $this->injectionService->getApiKey('snippe', $primarySnippeKey);
        $this->assertEquals($secondarySnippeKey, $snippeKey);

        $sonicKey = $this->injectionService->getApiKey('sonicpesa', $primarySonicPesaKey);
        $this->assertEquals($secondarySonicPesaKey, $sonicKey);
    }
}
