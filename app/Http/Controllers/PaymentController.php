<?php

namespace App\Http\Controllers;

use App\Mail\TransactionSuccessNotification;
use App\Models\AdminSetting;
use App\Models\Page;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    private const SONICPESA_API_URL = 'https://api.sonicpesa.com/api/v1/payment';

    private const SNIPPE_API_URL = 'https://api.snippe.sh/v1';

    /**
     * Create a payment order with gateway (SonicPesa or Snippe).
     * POST /api/payments/create-order
     */
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'page_id'      => 'required|exists:pages,id',
            'buyer_phone'  => 'required|string|min:9|max:15',
            'buyer_name'   => 'nullable|string|max:100',
            'buyer_email'  => 'nullable|email',
        ]);

        $page = Page::findOrFail($validated['page_id']);

        // Normalize phone number to Tanzania format (255XXXXXXXXX)
        $phone = $this->normalizePhoneNumber($validated['buyer_phone']);

        if (! $phone) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid phone number format. Please enter a valid Tanzania number.',
            ], 400);
        }

        // Determine which gateway to use
        $gateway = strtolower($page->payment_gateway);

        if ($gateway === 'sonicpesa') {
            return $this->createSonicPesaOrder($page, $phone, $validated);
        } elseif ($gateway === 'snippe') {
            return $this->createSnippeOrder($page, $phone, $validated);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Unsupported payment gateway: '.$gateway,
        ], 400);
    }

    /**
     * Create a SonicPesa payment order
     */
    private function createSonicPesaOrder(Page $page, string $phone, array $data)
    {
        // Get SonicPesa gateway config from database
        $gatewayConfig = PaymentGateway::where('name', 'sonicpesa')->first();

        if (! $gatewayConfig || ! $gatewayConfig->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => 'SonicPesa gateway is not configured or inactive.',
            ], 400);
        }

        // Create transaction record
        $transaction = Transaction::create([
            'page_id'        => $page->id,
            'buyer_email'    => $data['buyer_email'] ?? 'customer@example.com',
            'buyer_name'     => $data['buyer_name'] ?? 'Customer',
            'buyer_phone'    => $phone,
            'amount'         => $page->price,
            'currency'       => 'TZS',
            'gateway'        => 'sonicpesa',
            'payment_status' => 'PENDING',
            'order_id'       => 'pending_'.time(),
        ]);

        try {
            // Call SonicPesa API using the admin-configured API key
            $response = Http::withHeaders([
                'X-API-KEY' => $gatewayConfig->api_key,
            ])->post(self::SONICPESA_API_URL.'/create_order', [
                'buyer_email' => $transaction->buyer_email,
                'buyer_name'  => $transaction->buyer_name,
                'buyer_phone' => $phone,
                'amount'      => (int) $page->price,
                'currency'    => 'TZS',
                'link_url'    => null,
            ]);

            if ($response->failed()) {
                $transaction->update(['payment_status' => 'FAILED']);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Failed to create payment order',
                    'error'   => $response->json('message'),
                ], 400);
            }

            $responseData = $response->json();

            if ($responseData['status'] !== 'success') {
                $transaction->update(['payment_status' => 'FAILED']);

                return response()->json([
                    'status'  => 'error',
                    'message' => $responseData['message'] ?? 'Payment order creation failed',
                ], 400);
            }

            $orderId       = $responseData['data']['order_id'];
            $reference     = $responseData['data']['reference'] ?? null;
            $transactionId = $responseData['data']['transid'] ?? null;
            $msisdn        = $responseData['data']['msisdn'] ?? null;

            // Update transaction with SonicPesa response
            $transaction->update([
                'order_id'       => $orderId,
                'reference'      => $reference,
                'transaction_id' => $transactionId,
                'msisdn'         => $msisdn,
                'response_data'  => $responseData,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Payment order created successfully',
                'data'    => [
                    'transaction_id' => $transaction->id,
                    'order_id'       => $orderId,
                    'amount'         => $responseData['data']['amount'],
                    'currency'       => $responseData['data']['currency'],
                ],
            ]);
        } catch (\Exception $e) {
            $transaction->update(['payment_status' => 'FAILED']);

            return response()->json([
                'status'  => 'error',
                'message' => 'Error creating payment order: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a Snippe payment order
     */
    private function createSnippeOrder(Page $page, string $phone, array $data)
    {
        // Get Snippe gateway config from database
        $gatewayConfig = PaymentGateway::where('name', 'snippe')->first();

        if (! $gatewayConfig || ! $gatewayConfig->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Snippe gateway is not configured or inactive.',
            ], 400);
        }

        // Generate unique order ID
        $orderId = 'ORD-'.uniqid().'-'.time();

        // Create transaction record
        $transaction = Transaction::create([
            'page_id'        => $page->id,
            'buyer_email'    => $data['buyer_email'] ?? 'customer@example.com',
            'buyer_name'     => $data['buyer_name'] ?? 'Customer',
            'buyer_phone'    => $phone,
            'amount'         => $page->price,
            'currency'       => 'TZS',
            'gateway'        => 'snippe',
            'payment_status' => 'pending',
            'order_id'       => $orderId,
        ]);

        try {
            // Call Snippe API using the admin-configured API key
            $nameParts = explode(' ', $transaction->buyer_name);
            $firstName = $nameParts[0] ?? 'Customer';
            $lastName  = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : 'User';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$gatewayConfig->api_key,
            ])->post(self::SNIPPE_API_URL.'/payments', [
                'payment_type' => 'mobile',
                'details'      => [
                    'amount'   => (int) $page->price,
                    'currency' => 'TZS',
                ],
                'phone_number' => $phone,
                'customer'     => [
                    'firstname' => $firstName,
                    'lastname'  => $lastName,
                    'email'     => $transaction->buyer_email,
                ],
                'webhook_url' => $gatewayConfig->webhook_url ?? 'https://example.com/webhook',
                'metadata'    => [
                    'order_id' => $orderId,
                ],
            ]);

            if ($response->failed()) {
                $transaction->update(['payment_status' => 'failed']);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Failed to create payment order',
                    'error'   => $response->json('message'),
                ], 400);
            }

            $responseData = $response->json();

            if ($responseData['status'] !== 'success') {
                $transaction->update(['payment_status' => 'failed']);

                return response()->json([
                    'status'  => 'error',
                    'message' => $responseData['message'] ?? 'Payment order creation failed',
                ], 400);
            }

            $reference = $responseData['data']['reference'];

            // Update transaction with Snippe response
            $transaction->update([
                'reference'      => $reference,
                'payment_status' => $responseData['data']['status'],
                'response_data'  => $responseData,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Payment order created successfully',
                'data'    => [
                    'transaction_id' => $transaction->id,
                    'reference'      => $reference,
                    'amount'         => $responseData['data']['amount'],
                    'currency'       => $responseData['data']['amount']['currency'],
                ],
            ]);
        } catch (\Exception $e) {
            $transaction->update(['payment_status' => 'failed']);

            return response()->json([
                'status'  => 'error',
                'message' => 'Error creating payment order: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Normalize phone number to Tanzania format (255XXXXXXXXX)
     */
    private function normalizePhoneNumber(string $phone): ?string
    {
        // Remove all non-digit characters except leading +
        $cleaned = preg_replace('/[^\d+]/', '', $phone);

        // Remove leading +
        if (str_starts_with($cleaned, '+')) {
            $cleaned = substr($cleaned, 1);
        }

        // If starts with 0, replace with 255
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '255'.substr($cleaned, 1);
        }

        // If already starts with 255, keep it
        if (! str_starts_with($cleaned, '255')) {
            // Assume it's a local number, prepend 255
            if (strlen($cleaned) === 9) {
                $cleaned = '255'.$cleaned;
            } else {
                return null; // Invalid format
            }
        }

        // Validate format: 255 followed by 9 digits
        if (preg_match('/^255\d{9}$/', $cleaned)) {
            return $cleaned;
        }

        return null;
    }

    /**
     * Check payment order status.
     * POST /api/payments/check-status
     */
    public function checkStatus(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
        ]);

        $transaction = Transaction::findOrFail($validated['transaction_id']);

        // Determine which gateway to use
        $gateway = strtolower($transaction->gateway);

        if ($gateway === 'sonicpesa') {
            return $this->checkSonicPesaStatus($transaction);
        } elseif ($gateway === 'snippe') {
            return $this->checkSnippeStatus($transaction);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Unsupported payment gateway: '.$gateway,
        ], 400);
    }

    /**
     * Check SonicPesa payment status
     */
    private function checkSonicPesaStatus(Transaction $transaction)
    {
        // Get SonicPesa gateway config from database
        $gatewayConfig = PaymentGateway::where('name', 'sonicpesa')->first();

        if (! $gatewayConfig) {
            return response()->json([
                'status'  => 'error',
                'message' => 'SonicPesa gateway is not configured.',
            ], 400);
        }

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $gatewayConfig->api_key,
            ])->post(self::SONICPESA_API_URL.'/order_status', [
                'order_id' => $transaction->order_id,
            ]);

            if ($response->failed()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Failed to check payment status',
                ], 400);
            }

            $responseData = $response->json();

            if ($responseData['status'] !== 'success') {
                return response()->json([
                    'status'  => 'error',
                    'message' => $responseData['message'] ?? 'Status check failed',
                ], 400);
            }

            // Check if already completed to avoid duplicate emails
            $wasAlreadyCompleted = $transaction->payment_status === 'COMPLETED';

            // Update transaction with latest status
            $paymentStatus = $responseData['data']['payment_status'];
            $transaction->update([
                'payment_status' => $paymentStatus,
                'transaction_id' => $responseData['data']['transid'] ?? $transaction->transaction_id,
                'channel'        => $responseData['data']['channel'] ?? $transaction->channel,
                'msisdn'         => $responseData['data']['msisdn'] ?? $transaction->msisdn,
                'response_data'  => $responseData,
                'completed_at'   => $paymentStatus === 'COMPLETED' ? now() : null,
            ]);

            // Send admin email notification on completion (only if it just transitioned)
            if ($paymentStatus === 'COMPLETED' && ! $wasAlreadyCompleted) {
                try {
                    $adminEmail = AdminSetting::get('admin_email');
                    if ($adminEmail) {
                        $transaction->load('page');
                        Mail::to($adminEmail)->send(new TransactionSuccessNotification($transaction));
                    }
                } catch (\Exception $e) {
                    \Log::warning('Transaction notification email failed: ' . $e->getMessage());
                }
            }

            return response()->json([
                'status'         => 'success',
                'payment_status' => $paymentStatus,
                'data'           => $responseData['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error checking payment status: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check Snippe payment status
     */
    private function checkSnippeStatus(Transaction $transaction)
    {
        // Get Snippe gateway config from database
        $gatewayConfig = PaymentGateway::where('name', 'snippe')->first();

        if (! $gatewayConfig) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Snippe gateway is not configured.',
            ], 400);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$gatewayConfig->api_key,
            ])->get(self::SNIPPE_API_URL.'/payments/'.$transaction->reference);

            if ($response->failed()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Failed to check payment status',
                ], 400);
            }

            $responseData = $response->json();

            if ($responseData['status'] !== 'success') {
                return response()->json([
                    'status'  => 'error',
                    'message' => $responseData['message'] ?? 'Status check failed',
                ], 400);
            }

            // Check if already completed to avoid duplicate emails
            $wasAlreadyCompleted = in_array(strtolower($transaction->payment_status), ['completed', 'success']);

            // Update transaction with latest status
            $paymentStatus = strtolower($responseData['data']['status']);
            $transactionStatus = match ($paymentStatus) {
                'completed' => 'completed',
                'pending'   => 'pending',
                'canceled'  => 'canceled',
                default     => 'pending',
            };

            $transaction->update([
                'payment_status' => $transactionStatus,
                'transaction_id' => $responseData['data']['external_reference'] ?? $transaction->transaction_id,
                'channel'        => $responseData['data']['channel']['provider'] ?? $transaction->channel,
                'msisdn'         => $responseData['data']['customer']['phone'] ?? $transaction->msisdn,
                'response_data'  => $responseData,
                'completed_at'   => $transactionStatus === 'completed' ? ($responseData['data']['completed_at'] ?? now()) : null,
            ]);

            // Send admin email notification on completion (only if it just transitioned)
            if ($transactionStatus === 'completed' && ! $wasAlreadyCompleted) {
                try {
                    $adminEmail = AdminSetting::get('admin_email');
                    if ($adminEmail) {
                        $transaction->load('page');
                        Mail::to($adminEmail)->send(new TransactionSuccessNotification($transaction));
                    }
                } catch (\Exception $e) {
                    \Log::warning('Transaction notification email failed: ' . $e->getMessage());
                }
            }

            return response()->json([
                'status'         => 'success',
                'payment_status' => $transactionStatus,
                'data'           => $responseData['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error checking payment status: '.$e->getMessage(),
            ], 500);
        }
    }
}
