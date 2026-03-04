@extends('layouts.app')

@section('title', 'Payment Gateways')
@section('page_title', 'Payment Gateway Settings')

@section('content')
<div>
    <p class="text-gray-600 text-sm mb-8">Configure and manage your payment gateways</p>

    <!-- Payment Gateways Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Stripe Gateway -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-200">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M16.465 9.07H9.5v2.5h5.45c-.275 1.6-.925 2.85-2.825 3.6V19h2.875c2.65-2.45 4.125-6.05 4.125-9.93z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Stripe</h3>
                        <p class="text-sm text-gray-600">Fast & secure payments</p>
                    </div>
                </div>

                <!-- Enable/Disable Toggle -->
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="stripeEnable" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
            </div>

            <!-- Form -->
            <form class="space-y-6">
                <!-- Public Key -->
                <div>
                    <label for="stripe-public" class="block text-sm font-medium text-gray-900 mb-2">Public Key</label>
                    <input
                        type="password"
                        id="stripe-public"
                        placeholder="pk_live_..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition font-mono text-sm"
                    >
                    <p class="text-xs text-gray-600 mt-1">Found in your Stripe Dashboard → Developers → API Keys</p>
                </div>

                <!-- Secret Key -->
                <div>
                    <label for="stripe-secret" class="block text-sm font-medium text-gray-900 mb-2">Secret Key</label>
                    <input
                        type="password"
                        id="stripe-secret"
                        placeholder="sk_live_..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition font-mono text-sm"
                    >
                    <p class="text-xs text-gray-600 mt-1">Keep this key secure and never share it publicly</p>
                </div>

                <!-- Status Badge -->
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-700 font-medium">✓ Gateway is properly configured and active</p>
                </div>

                <!-- Save Button -->
                <button
                    type="button"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Save Changes
                </button>
            </form>
        </div>

        <!-- PayPal Gateway -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-200">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 12c0 1.657 1.343 3 3 3s3-1.343 3-3-1.343-3-3-3-3 1.343-3 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">PayPal</h3>
                        <p class="text-sm text-gray-600">Popular & trusted payments</p>
                    </div>
                </div>

                <!-- Enable/Disable Toggle -->
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="paypalEnable" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
            </div>

            <!-- Form -->
            <form class="space-y-6">
                <!-- Client ID -->
                <div>
                    <label for="paypal-client" class="block text-sm font-medium text-gray-900 mb-2">Client ID</label>
                    <input
                        type="password"
                        id="paypal-client"
                        placeholder="AXZ..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition font-mono text-sm"
                    >
                    <p class="text-xs text-gray-600 mt-1">Found in your PayPal Dashboard → Apps & Credentials</p>
                </div>

                <!-- Secret Key -->
                <div>
                    <label for="paypal-secret" class="block text-sm font-medium text-gray-900 mb-2">Secret Key</label>
                    <input
                        type="password"
                        id="paypal-secret"
                        placeholder="EJX..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition font-mono text-sm"
                    >
                    <p class="text-xs text-gray-600 mt-1">Keep this key secure and never share it publicly</p>
                </div>

                <!-- Status Badge -->
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-700 font-medium">⚠ Gateway configuration incomplete. Please add your credentials.</p>
                </div>

                <!-- Save Button -->
                <button
                    type="button"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    <!-- Configuration Info Section -->
    <div class="mt-12 bg-gray-50 rounded-xl border border-gray-200 p-8">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Need Help Setting Up?</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Stripe Help -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-3">Stripe Setup Guide</h3>
                <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                    <li>Log in to your Stripe account</li>
                    <li>Go to Developers → API Keys</li>
                    <li>Copy your Publishable Key and Secret Key</li>
                    <li>Paste them in the fields above</li>
                    <li>Save your changes</li>
                </ol>
                <a href="https://stripe.com" target="_blank" class="text-indigo-600 hover:underline font-medium text-sm mt-4 inline-block">
                    Visit Stripe Dashboard →
                </a>
            </div>

            <!-- PayPal Help -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-3">PayPal Setup Guide</h3>
                <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                    <li>Log in to your PayPal account</li>
                    <li>Go to Apps & Credentials</li>
                    <li>Copy your Client ID and Secret</li>
                    <li>Paste them in the fields above</li>
                    <li>Save your changes</li>
                </ol>
                <a href="https://developer.paypal.com" target="_blank" class="text-indigo-600 hover:underline font-medium text-sm mt-4 inline-block">
                    Visit PayPal Developer →
                </a>
            </div>
        </div>
    </div>

    <!-- Security Note -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex gap-4">
            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h3 class="font-semibold text-blue-900 mb-1">Security Note</h3>
                <p class="text-sm text-blue-700">
                    Your payment gateway credentials are encrypted and stored securely. Never share your API keys or secrets with anyone. 
                    If you suspect any unauthorized access, regenerate your keys immediately in your payment gateway dashboard.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
