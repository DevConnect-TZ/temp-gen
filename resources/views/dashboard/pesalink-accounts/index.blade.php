@extends('layouts.app')

@section('title', 'PesaLink Accounts')
@section('page_title', 'PesaLink')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">PesaLink Sub-Accounts</h1>
            <p class="mt-2 text-gray-600">Manage PesaLink API key sub-accounts for tracking revenue per account</p>
        </div>

        @if ($message = session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
                {{ $message }}
            </div>
        @endif

        <div class="mb-8 bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-orange-600 to-orange-700 px-6 py-4">
                <h2 class="text-xl font-bold text-white">Add New PesaLink Account</h2>
            </div>
            <form action="{{ route('pesalink-accounts.store') }}" method="POST" class="p-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Account Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="e.g., Main Account" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="new_api_key" class="block text-sm font-semibold text-gray-700 mb-2">API Key</label>
                        <div class="relative">
                            <input type="password" id="new_api_key" name="api_key" value="{{ old('api_key') }}" class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent font-mono text-sm" placeholder="Enter API key" required>
                            <button type="button" onclick="togglePassword('new_api_key')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 text-sm font-medium cursor-pointer">Show</button>
                        </div>
                        @error('api_key')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="base_url" class="block text-sm font-semibold text-gray-700 mb-2">Base URL (optional)</label>
                        <input type="url" id="base_url" name="base_url" value="{{ old('base_url') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="https://pesalink.online/api">
                        @error('base_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <button type="submit" style="padding: 12px 48px; background: #ea580c; color: #fff; font-weight: 600; font-size: 16px; border: none; border-radius: 8px; cursor: pointer;">
                    Add Account
                </button>
            </form>
        </div>

        @if($accounts->isNotEmpty())
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($accounts as $account)
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-600 to-orange-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-white">{{ $account->name }}</h2>
                        <form action="{{ route('pesalink-accounts.toggle', $account) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-lg font-semibold text-sm transition-all {{ $account->is_active ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-gray-500 hover:bg-gray-600 text-white' }}">
                                {{ $account->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </div>
                </div>

                <form action="{{ route('pesalink-accounts.update', $account) }}" method="POST" class="p-6 space-y-5">
                    @csrf
                    <div>
                        <label for="name_{{ $account->id }}" class="block text-sm font-semibold text-gray-700 mb-2">Account Name</label>
                        <input type="text" id="name_{{ $account->id }}" name="name" value="{{ $account->name }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label for="api_key_{{ $account->id }}" class="block text-sm font-semibold text-gray-700 mb-2">API Key</label>
                        <div class="relative">
                            <input type="password" id="api_key_{{ $account->id }}" name="api_key" value="{{ $account->api_key }}" class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent font-mono text-sm" required>
                            <button type="button" onclick="togglePassword('api_key_{{ $account->id }}')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 text-sm font-medium cursor-pointer">Show</button>
                        </div>
                    </div>
                    <div>
                        <label for="base_url_{{ $account->id }}" class="block text-sm font-semibold text-gray-700 mb-2">Base URL (optional)</label>
                        <input type="url" id="base_url_{{ $account->id }}" name="base_url" value="{{ $account->base_url }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-gray-200">
                        <button type="submit" class="flex-1 px-4 py-3 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transition-all">
                            Save
                        </button>
                    </div>
                </form>

                <form action="{{ route('pesalink-accounts.destroy', $account) }}" method="POST" class="px-6 pb-4" onsubmit="return confirm('Delete this account?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-all">
                        Delete
                    </button>
                </form>

                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                    <div class="flex gap-6 text-xs text-gray-500">
                        <span>{{ $account->completed_transactions_count }} completed</span>
                        <span>TZS {{ number_format($account->total_revenue ?? 0, 0) }}</span>
                        <span>Updated: {{ $account->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p class="text-yellow-800 font-semibold">No PesaLink sub-accounts configured</p>
            <p class="text-yellow-700 text-sm mt-1">Add your first account above</p>
        </div>
        @endif

        <div class="mt-8">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                Back to Dashboard
            </a>
        </div>
    </div>
</div>

<script>
    function togglePassword(id) {
        var f = document.getElementById(id);
        var b = f.nextElementSibling;
        if (f.type === 'password') {
            f.type = 'text';
            b.textContent = 'Hide';
        } else {
            f.type = 'password';
            b.textContent = 'Show';
        }
    }
</script>
@endsection
