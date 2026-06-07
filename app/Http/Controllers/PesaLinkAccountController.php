<?php

namespace App\Http\Controllers;

use App\Models\PesaLinkAccount;
use Illuminate\Http\Request;

class PesaLinkAccountController extends Controller
{
    /**
     * Display PesaLink sub-accounts.
     * GET /pesalink-accounts
     */
    public function index()
    {
        $accounts = PesaLinkAccount::withCount(['transactions as completed_transactions_count' => function ($query) {
            $query->where('payment_status', 'COMPLETED');
        }])->withSum(['transactions as total_revenue' => function ($query) {
            $query->where('payment_status', 'COMPLETED');
        }], 'amount')->get();

        return view('dashboard.pesalink-accounts.index', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * Store a new PesaLink sub-account.
     * POST /pesalink-accounts
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_key' => 'required|string|min:10',
            'base_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        PesaLinkAccount::create($validated);

        return redirect()->back()
            ->with('success', 'PesaLink sub-account created successfully.');
    }

    /**
     * Update a PesaLink sub-account.
     * POST /pesalink-accounts/{account}/update
     */
    public function update(Request $request, PesaLinkAccount $account)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_key' => 'required|string|min:10',
            'base_url' => 'nullable|url',
        ]);

        $account->update($validated);

        return redirect()->back()
            ->with('success', 'PesaLink sub-account updated successfully.');
    }

    /**
     * Toggle PesaLink sub-account active status.
     * POST /pesalink-accounts/{account}/toggle
     */
    public function toggle(PesaLinkAccount $account)
    {
        $account->update(['is_active' => ! $account->is_active]);

        $status = $account->is_active ? 'enabled' : 'disabled';

        return redirect()->back()
            ->with('success', $account->name.' has been '.$status.'.');
    }

    /**
     * Delete a PesaLink sub-account.
     * DELETE /pesalink-accounts/{account}
     */
    public function destroy(PesaLinkAccount $account)
    {
        $account->delete();

        return redirect()->back()
            ->with('success', 'PesaLink sub-account deleted successfully.');
    }
}
