@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Payment Gateway Settings</h1>
            <p class="mt-2 text-gray-600">Configure your payment gateway API keys and settings</p>
        </div>

        <!-- Success Message -->
        @if ($message = session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                <i class="fas fa-check-circle text-green-600"></i>
                <p class="text-green-800">{{ $message }}</p>
            </div>
        @endif

        <!-- Gateways Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @forelse ($gateways as $gateway)
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <!-- Gateway Header -->
                    <div class="bg-gradient-to-r {{ $gateway->name === 'sonicpesa' ? 'from-red-600 to-red-700' : 'from-blue-600 to-blue-700' }} px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-white">{{ $gateway->display_name }}</h2>
                                <p class="text-sm opacity-90 mt-1">{{ $gateway->description }}</p>
                            </div>
                            <form action="{{ route('payment-gateways.toggle', $gateway) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-lg font-semibold text-sm transition-all {{ $gateway->is_active ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-500 hover:bg-gray-600 text-white' }}">
                                    {{ $gateway->is_active ? '✓ Active' : 'Inactive' }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Gateway Form -->
                    <form action="{{ route('payment-gateways.update', $gateway) }}" method="POST" class="p-6 space-y-5">
                        @csrf

                        <!-- API Key Field -->
                        <div>
                            <label for="api_key_{{ $gateway->id }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                API Key
                            </label>
                            <div class="relative">
                                <input
                                    type="password"
                                    id="api_key_{{ $gateway->id }}"
                                    name="api_key"
                                    value="{{ $gateway->api_key }}"
                                    class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-{{ $gateway->name === 'sonicpesa' ? 'red' : 'blue' }}-500 focus:border-transparent font-mono text-sm"
                                    placeholder="Enter your API key"
                                    required
                                />
                                <button
                                    type="button"
                                    onclick="togglePassword('api_key_{{ $gateway->id }}')"
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 z-10 flex items-center justify-center w-10 h-10 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all cursor-pointer"
                                >
                                    <i class="fas fa-eye text-lg"></i>
                                </button>
                            </div>
                            @error('api_key')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Webhook URL Field (Snippe Only) -->
                        @if ($gateway->name === 'snippe')
                            <div>
                                <label for="webhook_url_{{ $gateway->id }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Webhook URL
                                </label>
                                <input
                                    type="url"
                                    id="webhook_url_{{ $gateway->id }}"
                                    name="webhook_url"
                                    value="{{ $gateway->webhook_url }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="https://example.com/webhook"
                                />
                                @error('webhook_url')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <!-- Is Active Hidden Field -->
                        <input type="hidden" name="is_active" value="{{ $gateway->is_active ? '1' : '0' }}" />

                        <!-- Submit Button -->
                        <div class="flex gap-3 pt-4 border-t border-gray-200">
                            <button
                                type="submit"
                                class="flex-1 px-4 py-3 bg-{{ $gateway->name === 'sonicpesa' ? 'red' : 'blue' }}-600 hover:bg-{{ $gateway->name === 'sonicpesa' ? 'red' : 'blue' }}-700 text-white font-semibold rounded-lg transition-all"
                            >
                                <i class="fas fa-save mr-2"></i> Save Settings
                            </button>
                            <a
                                href="{{ route('dashboard') }}"
                                class="px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-all"
                            >
                                Cancel
                            </a>
                        </div>
                    </form>

                    <!-- Last Updated -->
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-xs text-gray-500">
                        Last updated: {{ $gateway->updated_at->diffForHumans() }}
                    </div>
                </div>
            @empty
                <div class="col-span-2 bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                    <i class="fas fa-exclamation-circle text-yellow-600 text-3xl mb-3"></i>
                    <p class="text-yellow-800 font-semibold">No payment gateways configured</p>
                </div>
            @endforelse
        </div>

        <!-- Back Link -->
        <div class="mt-8">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = event.target.closest('button').querySelector('i');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
