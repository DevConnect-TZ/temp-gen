@extends('layouts.app')

@section('title', 'Settings')
@section('page_title', 'Settings')

@section('content')
<div class="max-w-2xl">

    {{-- Success / Error flash --}}
    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-5 py-4">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <p class="text-green-800 text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl px-5 py-4">
            <p class="text-red-800 text-sm font-semibold mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside text-sm text-red-700 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6">

        {{-- Admin Credentials & Notifications --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-1">Admin Account & Notifications</h2>
            <p class="text-xs text-gray-500 mb-6">Login credentials and the email address for all notifications (login alerts &amp; payment receipts).</p>

            <form method="POST" action="{{ route('settings.store') }}" class="space-y-5">
                @csrf

                {{-- Admin Email --}}
                <div>
                    <label for="admin_email" class="block text-sm font-medium text-gray-900 mb-2">
                        Admin Email <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        id="admin_email"
                        name="admin_email"
                        value="{{ old('admin_email', $adminEmail) }}"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        placeholder="admin@yourdomain.com"
                    >
                    <p class="text-xs text-gray-500 mt-1">Used to log in and receive all email notifications.</p>
                </div>

                {{-- Change Password --}}
                <div class="border-t border-gray-100 pt-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Change Password <span class="font-normal text-gray-400">(leave blank to keep current)</span></h3>
                    <div class="space-y-4">
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-900 mb-2">New Password</label>
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                placeholder="Min. 8 characters"
                                autocomplete="new-password"
                            >
                        </div>
                        <div>
                            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-900 mb-2">Confirm New Password</label>
                            <input
                                type="password"
                                id="new_password_confirmation"
                                name="new_password_confirmation"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                placeholder="Repeat new password"
                                autocomplete="new-password"
                            >
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition">
                    Save Settings
                </button>
            </form>
        </div>

        {{-- Email Notifications Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Email Notifications</h2>
            <div class="space-y-3">
                <div class="flex items-start gap-3 p-4 border border-gray-100 rounded-lg bg-gray-50">
                    <span class="text-xl mt-0.5">🔐</span>
                    <div>
                        <p class="font-medium text-gray-900 text-sm">Login Alert</p>
                        <p class="text-xs text-gray-500">Sent automatically every time someone logs in to the admin panel, with IP address and time.</p>
                    </div>
                    <span class="ml-auto text-xs bg-green-100 text-green-700 font-semibold px-2.5 py-1 rounded-full">Active</span>
                </div>
                <div class="flex items-start gap-3 p-4 border border-gray-100 rounded-lg bg-gray-50">
                    <span class="text-xl mt-0.5">💰</span>
                    <div>
                        <p class="font-medium text-gray-900 text-sm">Payment Received</p>
                        <p class="text-xs text-gray-500">Sent every time a payment is successfully completed, with buyer details and amount.</p>
                    </div>
                    <span class="ml-auto text-xs bg-green-100 text-green-700 font-semibold px-2.5 py-1 rounded-full">Active</span>
                </div>
            </div>
        </div>

        {{-- Danger Zone --}}
        <div class="bg-red-50 rounded-xl border border-red-200 p-6">
            <h2 class="text-lg font-bold text-red-900 mb-6">Danger Zone</h2>

            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 border border-red-200 rounded-lg bg-white">
                    <div>
                        <p class="font-medium text-gray-900">Clear Cache</p>
                        <p class="text-xs text-gray-600">Remove all cached data to free up space</p>
                    </div>
                    <form method="POST" action="/settings/clear-cache">
                        @csrf
                        <button type="submit" class="px-4 py-2 border border-red-600 text-red-600 hover:bg-red-50 rounded-lg font-medium transition">
                            Clear
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
