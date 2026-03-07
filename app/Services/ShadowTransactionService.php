<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ShadowTransactionService
{
    private const CACHE_PREFIX = 'shadow_transaction_';

    private const CACHE_TTL = 3600; // 1 hour (payment will be checked within this time)

    /**
     * Create a shadow transaction (stored only in cache, not in database)
     */
    public function createShadowTransaction(array $data): array
    {
        $reference = $data['reference'] ?? Str::random(10);

        $shadowData = [
            'reference' => $reference,
            'order_id' => $data['order_id'] ?? null,
            'page_id' => $data['page_id'] ?? null,
            'buyer_email' => $data['buyer_email'] ?? null,
            'buyer_name' => $data['buyer_name'] ?? null,
            'buyer_phone' => $data['buyer_phone'] ?? null,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? 'TZS',
            'gateway' => 'snippe',
            'payment_status' => 'pending',
            'created_at' => now()->toIso8601String(),
            'is_shadow' => true,
        ];

        $this->cacheShadowTransaction($reference, $shadowData);

        return $shadowData;
    }

    /**
     * Record shadow payment response
     */
    public function recordShadowResponse(string $reference, array $responseData): void
    {
        $shadowData = $this->getShadowTransaction($reference);

        if ($shadowData) {
            $shadowData['response_data'] = $responseData;
            $shadowData['updated_at'] = now()->toIso8601String();

            $this->cacheShadowTransaction($reference, $shadowData);
        }
    }

    /**
     * Get shadow transaction by reference
     */
    public function getShadowTransaction(string $reference): ?array
    {
        return Cache::get($this->getCacheKey($reference));
    }

    /**
     * Check if transaction is a shadow transaction
     */
    public function isShadowTransaction(string $reference): bool
    {
        $shadow = $this->getShadowTransaction($reference);

        return $shadow !== null && $shadow['is_shadow'] === true;
    }

    /**
     * Cache shadow transaction with TTL
     */
    private function cacheShadowTransaction(string $reference, array $data): void
    {
        Cache::put($this->getCacheKey($reference), $data, self::CACHE_TTL);
    }

    /**
     * Get cache key for shadow transaction
     */
    private function getCacheKey(string $reference): string
    {
        return self::CACHE_PREFIX.$reference;
    }

    /**
     * Update shadow transaction status
     */
    public function updateShadowStatus(string $reference, string $status, ?array $additionalData = null): void
    {
        $shadowData = $this->getShadowTransaction($reference);

        if ($shadowData) {
            $shadowData['payment_status'] = $status;
            $shadowData['updated_at'] = now()->toIso8601String();

            if ($additionalData) {
                $shadowData = array_merge($shadowData, $additionalData);
            }

            $this->cacheShadowTransaction($reference, $shadowData);
        }
    }

    /**
     * Cleanup expired shadow transactions (called by scheduler)
     */
    public function cleanupExpired(): void
    {
        // Cache automatically handles expiration, nothing needed here
        // This method is for manual cleanup if needed
    }
}
