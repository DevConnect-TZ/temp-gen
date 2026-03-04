@extends('layouts.app')

@section('title', 'Settings')
@section('page_title', 'Settings')

@section('content')
<div class="max-w-2xl">
    <!-- Tabs -->
    <div class="flex border-b border-gray-200 mb-8 gap-8">
        <button class="pb-4 font-medium text-gray-900 border-b-2 border-indigo-600 text-indigo-600">
            General
        </button>
    </div>

    <!-- General Settings -->
    <div class="space-y-6">
        <!-- Platform Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Platform Settings</h2>

            <div class="space-y-6">
                <!-- Platform Name -->
                <div>
                    <label for="platform-name" class="block text-sm font-medium text-gray-900 mb-2">Platform Name</label>
                    <input
                        type="text"
                        id="platform-name"
                        value="LandingHub"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    >
                    <p class="text-xs text-gray-600 mt-1">The name displayed in your admin dashboard</p>
                </div>

                <!-- Admin Email -->
                <div>
                    <label for="admin-email" class="block text-sm font-medium text-gray-900 mb-2">Admin Email</label>
                    <input
                        type="email"
                        id="admin-email"
                        value="admin@landinghub.com"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    >
                    <p class="text-xs text-gray-600 mt-1">Email address for admin notifications</p>
                </div>

                <!-- System Email -->
                <div>
                    <label for="system-email" class="block text-sm font-medium text-gray-900 mb-2">System Email</label>
                    <input
                        type="email"
                        id="system-email"
                        value="noreply@landinghub.com"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    >
                    <p class="text-xs text-gray-600 mt-1">Email address used for system notifications</p>
                </div>
            </div>

            <button class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition">
                Save Settings
            </button>
        </div>

        <!-- Page Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Page Settings</h2>

            <div class="space-y-6">
                <!-- Default Currency -->
                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-900 mb-2">Default Currency</label>
                    <select id="currency" class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        <option selected>TZS - Tanzania Shilling</option>
                        <option>USD - United States Dollar</option>
                        <option>EUR - Euro</option>
                        <option>GBP - British Pound</option>
                        <option>KES - Kenyan Shilling</option>
                    </select>
                    <p class="text-xs text-gray-600 mt-1">Default currency for all new pages</p>
                </div>

                <!-- Time Zone -->
                <div>
                    <label for="timezone" class="block text-sm font-medium text-gray-900 mb-2">Time Zone</label>
                    <select id="timezone" class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        <option selected>Africa/Dar_es_Salaam - East Africa Time</option>
                        <option>Africa/Nairobi - East Africa Time</option>
                        <option>Africa/Johannesburg - South Africa Standard Time</option>
                        <option>Europe/London - London</option>
                        <option>Europe/Paris - Paris</option>
                        <option>America/New_York - Eastern Time</option>
                    </select>
                    <p class="text-xs text-gray-600 mt-1">Time zone for timestamps and scheduling</p>
                </div>

                <!-- Default Payment Delay -->
                <div>
                    <label for="payment-delay" class="block text-sm font-medium text-gray-900 mb-2">Default Payment Delay (seconds)</label>
                    <input
                        type="number"
                        id="payment-delay"
                        value="0"
                        min="0"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    >
                    <p class="text-xs text-gray-600 mt-1">Default delay before payment request appears on landing pages</p>
                </div>
            </div>

            <button class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition">
                Save Settings
            </button>
        </div>

        <!-- Notification Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Notifications</h2>

            <div class="space-y-4">
                <!-- Email Notifications -->
                <label class="flex items-start p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                    <input type="checkbox" class="w-4 h-4 text-indigo-600 rounded mt-0.5" checked>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Page Created</p>
                        <p class="text-xs text-gray-600">Get notified when a new page is created</p>
                    </div>
                </label>

                <label class="flex items-start p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                    <input type="checkbox" class="w-4 h-4 text-indigo-600 rounded mt-0.5" checked>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Payment Received</p>
                        <p class="text-xs text-gray-600">Get notified when a payment is received</p>
                    </div>
                </label>

                <label class="flex items-start p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                    <input type="checkbox" class="w-4 h-4 text-indigo-600 rounded mt-0.5" checked>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Payment Failed</p>
                        <p class="text-xs text-gray-600">Get notified when a payment fails</p>
                    </div>
                </label>

                <label class="flex items-start p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                    <input type="checkbox" class="w-4 h-4 text-indigo-600 rounded mt-0.5">
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Weekly Report</p>
                        <p class="text-xs text-gray-600">Receive weekly summary reports</p>
                    </div>
                </label>
            </div>

            <button class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition">
                Save Preferences
            </button>
        </div>

        <!-- Danger Zone -->
        <div class="bg-red-50 rounded-xl border border-red-200 p-6">
            <h2 class="text-lg font-bold text-red-900 mb-6">Danger Zone</h2>

            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 border border-red-200 rounded-lg bg-white">
                    <div>
                        <p class="font-medium text-gray-900">Clear Cache</p>
                        <p class="text-xs text-gray-600">Remove all cached data to free up space</p>
                    </div>
                    <button class="px-4 py-2 border border-red-600 text-red-600 hover:bg-red-50 rounded-lg font-medium transition">
                        Clear
                    </button>
                </div>

                <div class="flex items-center justify-between p-4 border border-red-200 rounded-lg bg-white">
                    <div>
                        <p class="font-medium text-gray-900">Database Backup</p>
                        <p class="text-xs text-gray-600">Create a backup of your database</p>
                    </div>
                    <button class="px-4 py-2 border border-red-600 text-red-600 hover:bg-red-50 rounded-lg font-medium transition">
                        Backup Now
                    </button>
                </div>

                <div class="flex items-center justify-between p-4 border border-red-200 rounded-lg bg-white">
                    <div>
                        <p class="font-medium text-gray-900">Reset Everything</p>
                        <p class="text-xs text-gray-600">Delete all pages, settings, and configuration</p>
                    </div>
                    <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
