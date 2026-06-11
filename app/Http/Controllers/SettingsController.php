<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    /**
     * Show the settings page.
     */
    public function index()
    {
        return view('dashboard.settings.index', [
            'adminEmail' => AdminSetting::get('admin_email', 'admin@example.com'),
            'uhondoUrl' => AdminSetting::get('uhondo_url', 'https://uhondotu.online'),
            'uhondoReturnUrl' => AdminSetting::get('uhondo_return_url', 'https://uhondo.online'),
            'uhondoAccessHours' => AdminSetting::get('uhondo_access_hours', 24),
        ]);
    }

    /**
     * Save admin settings.
     * POST /settings
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'admin_email' => 'required|email|max:255',
            'new_password' => 'nullable|string|min:8|confirmed',
            'uhondo_url' => 'required|url|max:255',
            'uhondo_return_url' => 'required|url|max:255',
            'uhondo_access_hours' => 'required|integer|min:1|max:8760',
        ]);

        // Save admin email
        AdminSetting::set('admin_email', $validated['admin_email']);

        // Save new password if provided
        if (! empty($validated['new_password'])) {
            AdminSetting::set('admin_password', Hash::make($validated['new_password']));
        }

        AdminSetting::set('uhondo_url', rtrim($validated['uhondo_url'], '/'));
        AdminSetting::set('uhondo_return_url', $validated['uhondo_return_url']);
        AdminSetting::set('uhondo_access_hours', $validated['uhondo_access_hours']);

        return redirect()->route('settings.index')
            ->with('success', 'Settings saved successfully!');
    }
}
