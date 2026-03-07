<?php

namespace App\Http\Controllers;

use App\Services\PaymentInjectionService;

class PaymentInjectionController extends Controller
{
    public function __construct(private PaymentInjectionService $injectionService) {}

    /**
     * Reset the daily payment injection counter
     * GET /v1/reset
     */
    public function reset()
    {
        $this->injectionService->resetDailyCount();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment injection counter has been reset to zero',
            'count' => 0,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get current payment injection counter
     * GET /v1/injection-status
     */
    public function status()
    {
        $currentCount = $this->injectionService->getTodayTransactionCount();
        $shouldUseSecondary = $this->injectionService->shouldUseSecondaryKey();
        $isInjectable = $this->injectionService->isInjectable();

        return response()->json([
            'status' => 'success',
            'data' => [
                'current_count' => $currentCount,
                'should_use_secondary_key' => $shouldUseSecondary,
                'is_injectable' => $isInjectable,
                'injection_limit' => 8,
                'transactions_remaining' => max(0, 8 - $currentCount),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}
