<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\SettingsController;
use App\Mail\AdminLoginNotification;
use App\Models\AdminSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

// Public Routes - Login
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function () {
        $email    = request('email');
        $password = request('password');

        // Load credentials from admin settings
        $storedEmail    = AdminSetting::get('admin_email', 'admin@example.com');
        $storedPassword = AdminSetting::get('admin_password', '');

        $emailMatches    = $email === $storedEmail;
        $passwordMatches = Hash::check($password, $storedPassword);

        if ($emailMatches && $passwordMatches) {
            session(['admin_authenticated' => true]);

            // Send login notification email (non-blocking)
            try {
                Mail::to($storedEmail)->send(
                    new AdminLoginNotification(
                        request()->ip(),
                        request()->userAgent() ?? 'Unknown'
                    )
                );
            } catch (\Exception $e) {
                // Don't fail the login if the email fails
                \Log::warning('Login notification email failed: ' . $e->getMessage());
            }

            return redirect('/dashboard');
        }

        return back()->withErrors(['password' => 'Invalid credentials']);
    })->name('login.store');
});

// Protected Routes - Dashboard
Route::middleware(['auth.custom'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        $totalPages    = \App\Models\Page::count();
        $activePages   = \App\Models\Page::where('is_active', true)->count();
        $inactivePages = \App\Models\Page::where('is_active', false)->count();
        $totalRevenue  = \App\Models\Transaction::where('payment_status', 'COMPLETED')->sum('amount');
        $recentPages   = \App\Models\Page::latest()->take(5)->get();

        return view('dashboard.index', [
            'totalPages'    => $totalPages,
            'activePages'   => $activePages,
            'inactivePages' => $inactivePages,
            'totalRevenue'  => $totalRevenue,
            'recentPages'   => $recentPages,
        ]);
    })->name('dashboard');

    // Pages Management
    Route::controller(PageController::class)->prefix('pages')->group(function () {
        Route::get('/', 'index')->name('pages.index');
        Route::get('/create', 'create')->name('pages.create');
        Route::post('/', 'store')->name('pages.store');
        Route::get('/{page}/edit', 'edit')->name('pages.edit');
        Route::put('/{page}', 'update')->name('pages.update');
        Route::delete('/{page}', 'destroy')->name('pages.destroy');
        Route::patch('/{page}/toggle', 'toggle')->name('pages.toggle');
    });

    // Templates
    Route::get('/templates', function () {
        $templates = [
            ['id' => 'template1', 'name' => 'template1', 'cover' => '/images/youtubex.jpeg'],
            ['id' => 'template2', 'name' => 'template2', 'cover' => '/images/utamuplus.png'],
        ];

        return view('dashboard.templates.index', ['templates' => $templates]);
    })->name('templates.index');

    // Payment Gateway Settings
    Route::controller(PaymentGatewayController::class)->prefix('payment-gateways')->group(function () {
        Route::get('/', 'index')->name('payment-gateways.index');
        Route::post('/{gateway}/update', 'update')->name('payment-gateways.update');
        Route::post('/{gateway}/toggle', 'toggle')->name('payment-gateways.toggle');
    });

    // Settings
    Route::controller(SettingsController::class)->group(function () {
        Route::get('/settings', 'index')->name('settings.index');
        Route::post('/settings', 'store')->name('settings.store');
    });

    // Logout
    Route::post('/logout', function () {
        session()->forget('admin_authenticated');

        return redirect('/login');
    })->name('logout');
});

// Payment Routes (accessible by anyone for public pages)
Route::controller(\App\Http\Controllers\PaymentController::class)->prefix('api/payments')->group(function () {
    Route::post('/create-order', 'createOrder')->name('payments.create-order');
    Route::post('/check-status', 'checkStatus')->name('payments.check-status');
});

// Public Routes - Pages (must be last so dashboard routes take priority)
Route::get('/{page}', [PageController::class, 'show'])->where('page', '[a-z0-9-]+')->name('page.show');

// Root redirect
Route::get('/', function () {
    if (session('admin_authenticated')) {
        return redirect('/dashboard');
    }

    return redirect('/login');
});
