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
        ]);
    }

    /**
     * Save admin settings.
     * POST /settings
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'admin_email'    => 'required|email|max:255',
            'new_password'   => 'nullable|string|min:8|confirmed',
        ]);

        // Save admin email
        AdminSetting::set('admin_email', $validated['admin_email']);

        // Save new password if provided
        if (! empty($validated['new_password'])) {
            AdminSetting::set('admin_password', Hash::make($validated['new_password']));
        }

        return redirect()->route('settings.index')
            ->with('success', 'Settings saved successfully!');
    }
}
