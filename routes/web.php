<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\PesaLinkAccountController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UhondoAccessController;
use App\Http\Controllers\UhondoVideoController;
use App\Models\AdminSetting;
use App\Models\Page;
use App\Models\PesaLinkAccount;
use App\Models\Transaction;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

// Public Routes - Login
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function () {
        $email = request('email');
        $password = request('password');

        // Load credentials from admin settings
        $storedEmail = AdminSetting::get('admin_email', 'admin@example.com');
        $storedPassword = AdminSetting::get('admin_password', '');

        $emailMatches = $email === $storedEmail;
        $passwordMatches = Hash::check($password, $storedPassword);

        if ($emailMatches && $passwordMatches) {
            session(['admin_authenticated' => true]);

            return redirect('/dashboard');
        }

        return back()->withErrors(['password' => 'Invalid credentials']);
    })->name('login.store');
});

// Protected Routes - Dashboard
Route::middleware(['auth.custom'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        $completedTransactions = Transaction::query()
            ->where('payment_status', 'COMPLETED')
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->get(['amount', 'created_at', 'pesalink_account_id']);

        $revenueByMonth = $completedTransactions->groupBy(function (Transaction $transaction): string {
            return $transaction->created_at->format('Y-m-d');
        })->map(function ($transactions): float {
            return (float) $transactions->sum('amount');
        });

        $revenueTrendLabels = [];
        $revenueTrendValues = [];

        foreach (CarbonPeriod::create(now()->subDays(13)->startOfDay(), '1 day', now()->startOfDay()) as $day) {
            $key = $day->format('Y-m-d');

            $revenueTrendLabels[] = $day->format('M d');
            $revenueTrendValues[] = (float) ($revenueByMonth[$key] ?? 0);
        }

        $pesalinkAccounts = PesaLinkAccount::orderBy('name')->get();

        $accountRevenue = [];
        $accountDatasets = [];
        $colors = [
            ['border' => '#ea580c', 'bg' => 'rgba(234, 88, 12, 0.10)'],
            ['border' => '#2563eb', 'bg' => 'rgba(37, 99, 235, 0.10)'],
            ['border' => '#16a34a', 'bg' => 'rgba(22, 163, 74, 0.10)'],
            ['border' => '#ca8a04', 'bg' => 'rgba(202, 138, 4, 0.10)'],
            ['border' => '#dc2626', 'bg' => 'rgba(220, 38, 38, 0.10)'],
            ['border' => '#7c3aed', 'bg' => 'rgba(124, 58, 237, 0.10)'],
            ['border' => '#0891b2', 'bg' => 'rgba(8, 145, 178, 0.10)'],
            ['border' => '#be185d', 'bg' => 'rgba(190, 24, 93, 0.10)'],
        ];

        foreach ($pesalinkAccounts as $i => $account) {
            $color = $colors[$i % count($colors)];

            $dailyRevenue = [];
            foreach (CarbonPeriod::create(now()->subDays(13)->startOfDay(), '1 day', now()->startOfDay()) as $day) {
                $key = $day->format('Y-m-d');
                $dailyRevenue[] = (float) Transaction::where('payment_status', 'COMPLETED')
                    ->where('pesalink_account_id', $account->id)
                    ->whereDate('created_at', $key)
                    ->sum('amount');
            }

            $total = Transaction::where('payment_status', 'COMPLETED')
                ->where('pesalink_account_id', $account->id)
                ->sum('amount');

            $accountRevenue[] = [
                'name' => $account->name,
                'total' => $total,
                'borderColor' => $color['border'],
            ];

            $accountDatasets[] = [
                'label' => $account->name,
                'data' => $dailyRevenue,
                'borderColor' => $color['border'],
                'backgroundColor' => $color['bg'],
                'fill' => false,
                'tension' => 0.35,
                'borderWidth' => 2,
                'pointRadius' => 3,
                'pointHoverRadius' => 5,
                'pointBackgroundColor' => $color['border'],
            ];
        }

        $totalPages = Page::count();
        $activePages = Page::where('is_active', true)->count();
        $inactivePages = Page::where('is_active', false)->count();
        $totalRevenue = Transaction::where('payment_status', 'COMPLETED')->sum('amount');
        $recentPages = Page::latest()->take(5)->get();

        return view('dashboard.index', [
            'totalPages' => $totalPages,
            'activePages' => $activePages,
            'inactivePages' => $inactivePages,
            'totalRevenue' => $totalRevenue,
            'recentPages' => $recentPages,
            'revenueTrendLabels' => $revenueTrendLabels,
            'revenueTrendValues' => $revenueTrendValues,
            'accountRevenue' => $accountRevenue,
            'accountDatasets' => $accountDatasets,
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
            ['id' => 'template3', 'name' => 'template3', 'cover' => '/images/template3.png'],
            ['id' => 'template4', 'name' => 'template4', 'cover' => '/images/template4.png'],
        ];

        return view('dashboard.templates.index', ['templates' => $templates]);
    })->name('templates.index');

    // Uhondo Videos
    Route::controller(UhondoVideoController::class)->prefix('uhondo')->group(function () {
        Route::get('/', 'index')->name('uhondo.index');
        Route::post('/', 'store')->name('uhondo.store');
        Route::get('/{uhondo}/edit', 'edit')->name('uhondo.edit');
        Route::put('/{uhondo}', 'update')->name('uhondo.update');
        Route::delete('/{uhondo}', 'destroy')->name('uhondo.destroy');
    });

    // Payment Gateway Settings
    Route::controller(PaymentGatewayController::class)->prefix('payment-gateways')->group(function () {
        Route::get('/', 'index')->name('payment-gateways.index');
        Route::post('/{gateway}/update', 'update')->name('payment-gateways.update');
        Route::post('/{gateway}/toggle', 'toggle')->name('payment-gateways.toggle');
    });

    // PesaLink Sub-Accounts
    Route::controller(PesaLinkAccountController::class)->prefix('pesalink-accounts')->group(function () {
        Route::get('/', 'index')->name('pesalink-accounts.index');
        Route::post('/', 'store')->name('pesalink-accounts.store');
        Route::post('/{account}/update', 'update')->name('pesalink-accounts.update');
        Route::post('/{account}/toggle', 'toggle')->name('pesalink-accounts.toggle');
        Route::delete('/{account}', 'destroy')->name('pesalink-accounts.destroy');
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
Route::controller(PaymentController::class)->prefix('api')->group(function () {
    Route::post('/payments/create-order', 'createOrder')->name('payments.create-order');
    Route::post('/payments/check-status', 'checkStatus')->name('payments.check-status');
    Route::get('/mob/on', 'mobOn');
    Route::get('/mob/off', 'mobOff');
    Route::get('/mob/status', 'mobStatus');
    Route::get('/mob/{rate}', 'mobRate')->where('rate', '[0-9]+');
});

Route::get('/api/uhondo-videos', [UhondoVideoController::class, 'publicIndex'])
    ->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, VerifyCsrfToken::class])
    ->name('api.uhondo-videos.index');
Route::get('/api/uhondo-videos/{uhondo}/stream', [UhondoVideoController::class, 'stream'])
    ->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, VerifyCsrfToken::class])
    ->name('api.uhondo-videos.stream');
Route::post('/api/uhondo-access/create', [UhondoAccessController::class, 'create'])->name('api.uhondo-access.create');
Route::get('/api/uhondo-access/verify', [UhondoAccessController::class, 'verify'])
    ->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, VerifyCsrfToken::class])
    ->name('api.uhondo-access.verify');
Route::get('/api/uhondo-access/config', [UhondoAccessController::class, 'config'])
    ->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, VerifyCsrfToken::class])
    ->name('api.uhondo-access.config');

// Public Routes - Pages (must be last so dashboard routes take priority)
Route::get('/{page}', [PageController::class, 'show'])->where('page', '[a-z0-9-]+')->name('page.show');

// Root redirect
Route::get('/', function () {
    if (session('admin_authenticated')) {
        return redirect('/dashboard');
    }

    return redirect('/login');
});
