<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class UhondoAccessController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_id' => ['required', 'string'],
        ]);

        $transactionId = $validated['transaction_id'];

        if (str_starts_with((string) $transactionId, 'SECRET_')) {
            $expiresAt = now()->addHours($this->accessHours());
            $token = Crypt::encryptString(json_encode([
                'transaction_id' => $transactionId,
                'page_id' => 0,
                'expires_at' => $expiresAt->timestamp,
            ]));

            return $this->corsJson([
                'status' => 'success',
                'access_url' => $this->appendToken($this->uhondoUrl(), $token),
                'expires_at' => $expiresAt->toIso8601String(),
            ]);
        }

        $transaction = Transaction::find($transactionId);

        if (! $transaction || ! $this->isCompleted($transaction)) {
            return $this->corsJson([
                'status' => 'error',
                'message' => 'Payment has not been completed.',
                'redirect_url' => $this->returnUrl(),
            ], 403);
        }

        $expiresAt = now()->addHours($this->accessHours());
        $token = Crypt::encryptString(json_encode([
            'transaction_id' => $transaction->id,
            'page_id' => $transaction->page_id,
            'expires_at' => $expiresAt->timestamp,
        ]));

        return $this->corsJson([
            'status' => 'success',
            'access_url' => $this->appendToken($this->uhondoUrl(), $token),
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $token = (string) $request->query('token', '');

        if ($token === '') {
            return $this->invalidAccessResponse();
        }

        try {
            $payload = json_decode(Crypt::decryptString($token), true, flags: JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return $this->invalidAccessResponse();
        }

        $expiresAt = (int) ($payload['expires_at'] ?? 0);
        $transactionId = $payload['transaction_id'] ?? 0;

        if ($expiresAt < now()->timestamp) {
            return $this->invalidAccessResponse();
        }

        // SECRET_ transactions have no database record — the encrypted token is the proof
        if (is_string($transactionId) && str_starts_with($transactionId, 'SECRET_')) {
            return $this->corsJson([
                'status' => 'success',
                'allowed' => true,
                'redirect_url' => $this->returnUrl(),
                'expires_at' => now()->setTimestamp($expiresAt)->toIso8601String(),
            ]);
        }

        $transaction = Transaction::find((int) $transactionId);

        if (! $transaction || ! $this->isCompleted($transaction)) {
            return $this->invalidAccessResponse();
        }

        return $this->corsJson([
            'status' => 'success',
            'allowed' => true,
            'redirect_url' => $this->returnUrl(),
            'expires_at' => now()->setTimestamp($expiresAt)->toIso8601String(),
        ]);
    }

    public function config(): JsonResponse
    {
        return $this->corsJson([
            'status' => 'success',
            'redirect_url' => $this->returnUrl(),
            'uhondo_url' => $this->uhondoUrl(),
        ]);
    }

    private function invalidAccessResponse(): JsonResponse
    {
        return $this->corsJson([
            'status' => 'error',
            'allowed' => false,
            'redirect_url' => $this->returnUrl(),
        ], 403);
    }

    private function isCompleted(Transaction $transaction): bool
    {
        return in_array(Str::lower((string) $transaction->payment_status), ['completed', 'success'], true);
    }

    private function appendToken(string $url, string $token): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.http_build_query(['access' => $token]);
    }

    private function uhondoUrl(): string
    {
        return rtrim((string) $this->setting('uhondo_url', 'https://uhondotu.online'), '/');
    }

    private function returnUrl(): string
    {
        return (string) $this->setting('uhondo_return_url', 'https://uhondo.online');
    }

    private function accessHours(): int
    {
        return max((int) $this->setting('uhondo_access_hours', 24), 1);
    }

    private function setting(string $key, mixed $default): mixed
    {
        try {
            return AdminSetting::get($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }

    private function corsJson(array $payload, int $status = 200): JsonResponse
    {
        return response()
            ->json($payload, $status)
            ->header('Access-Control-Allow-Origin', '*');
    }
}
