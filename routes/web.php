<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Public Routes - Login
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function () {
        // Login handler
        // For now, simple demo - in production use proper validation
        if (request('email') === 'admin@example.com' && request('password') === 'password') {
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
        return view('dashboard.index');
    })->name('dashboard');

    // Pages Management
    Route::prefix('pages')->group(function () {
        Route::get('/', function () {
            return view('dashboard.pages.index');
        })->name('pages.index');

        Route::get('/create', function () {
            return view('dashboard.pages.create');
        })->name('pages.create');
    });

    // Templates
    Route::get('/templates', function () {
        return view('dashboard.templates.index');
    })->name('templates.index');

    // Payment Gateways
    Route::get('/gateways', function () {
        return view('dashboard.gateways.settings');
    })->name('gateways.settings');

    // Settings
    Route::get('/settings', function () {
        return view('dashboard.settings.index');
    })->name('settings.index');

    // Logout
    Route::post('/logout', function () {
        session()->forget('admin_authenticated');
        return redirect('/login');
    })->name('logout');
});

// Root redirect
Route::get('/', function () {
    if (session('admin_authenticated')) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});
