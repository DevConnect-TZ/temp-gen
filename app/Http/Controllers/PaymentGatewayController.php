<?php

namespace App\Http\Controllers;

use App\Mail\ApiKeyChangedNotification;
use App\Models\AdminSetting;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PaymentGatewayController extends Controller
{
    /**
     * Display payment gateway settings.
     * GET /payment-gateways
     */
    public function index()
    {
        $gateways = PaymentGateway::all();

        return view('dashboard.payment-gateways.index', [
            'gateways' => $gateways,
        ]);
    }

    /**
     * Update payment gateway settings.
     * POST /payment-gateways/{gateway}/update
     */
    public function update(Request $request, PaymentGateway $gateway)
    {
        $validated = $request->validate([
            'api_key' => 'required|string|min:10',
            'webhook_url' => 'nullable|url',
            'is_active' => 'required|boolean',
        ]);

        $gateway->fill($validated);

        if ($gateway->isDirty('api_key')) {
            // Re-fetch since it could be changed
            $adminEmail = AdminSetting::get('admin_email');
            
            if ($adminEmail) {
                try {
                    Mail::to($adminEmail)->send(new ApiKeyChangedNotification($gateway, $request->ip()));
                } catch (\Exception $e) {
                    \Log::warning('API key change notification email failed: ' . $e->getMessage());
                }
            }
        }

        $gateway->save();

        return redirect()->back()
            ->with('success', $gateway->display_name.' settings updated successfully!');
    }

    /**
     * Toggle gateway active status.
     * POST /payment-gateways/{gateway}/toggle
     */
    public function toggle(PaymentGateway $gateway)
    {
        $gateway->update(['is_active' => ! $gateway->is_active]);

        $status = $gateway->is_active ? 'enabled' : 'disabled';

        return redirect()->back()
            ->with('success', $gateway->display_name.' has been '.$status.'.');
    }
}
