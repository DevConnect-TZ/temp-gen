@extends('layouts.auth')

@section('title', 'Admin Login')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
        <!-- Card -->
        <div class="bg-white rounded-xl shadow-sm p-8 sm:p-10">
            <!-- Logo & Title -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-lg mb-4">
                    <span class="text-2xl font-bold text-indigo-600">LG</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">LandingHub</h1>
                <p class="text-gray-600">Admin Dashboard</p>
            </div>

            <!-- Form -->
            <form method="POST" action="/login" class="space-y-6">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-900 mb-2">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        placeholder="mail@example.com"
                    >
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-900 mb-2">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        class="w-4 h-4 border-gray-300 rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                    >
                    <label for="remember" class="ml-2 text-sm font-medium text-gray-700 cursor-pointer">
                        Remember me
                    </label>
                </div>

                <!-- Error Message -->
                @if ($errors->any())
                    <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                        <p class="text-sm text-red-700 font-medium">Login Failed</p>
                        <p class="text-sm text-red-600 mt-1">Please check your credentials and try again.</p>
                    </div>
                @endif

                

                <!-- Login Button -->
                <button
                    type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Sign In
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-center text-sm text-gray-600">
                    © 2026 powered by. <a href="https://devconnecttz.site">DevconnectTz</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
