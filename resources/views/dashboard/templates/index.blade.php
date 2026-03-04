@extends('layouts.app')

@section('title', 'Manage Templates')
@section('page_title', 'Manage Templates')

@section('content')
<div>
    <p class="text-gray-600 text-sm mb-8">Choose and customize templates for your landing pages</p>

    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Template 1: Modern Minimalist -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
            <!-- Thumbnail -->
            <div class="bg-gradient-to-b from-gray-200 to-gray-100 h-48 flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2H4V5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v12a2 2 0 002 2h12a2 2 0 002-2V7"/>
                </svg>
            </div>

            <!-- Content -->
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Modern Minimalist</h3>
                <p class="text-sm text-gray-600 mb-4">Clean & simple design for professional pages</p>

                <!-- Gateway Info -->
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <p class="text-xs text-gray-600 mb-1">Default Gateway: <span class="font-medium text-gray-900">Stripe</span></p>
                    <p class="text-xs text-gray-600">Default Price: <span class="font-medium text-gray-900">$29.99</span></p>
                </div>

                <!-- Status Toggle -->
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-medium text-gray-900">Status</p>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <!-- Edit Button -->
                <button class="w-full px-4 py-2 border border-indigo-600 text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium transition">
                    Customize
                </button>
            </div>
        </div>

        <!-- Template 2: Bold & Bright -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
            <!-- Thumbnail -->
            <div class="bg-gradient-to-b from-gray-200 to-gray-100 h-48 flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2H4V5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v12a2 2 0 002 2h12a2 2 0 002-2V7"/>
                </svg>
            </div>

            <!-- Content -->
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Bold & Bright</h3>
                <p class="text-sm text-gray-600 mb-4">Vibrant colors for eye-catching campaigns</p>

                <!-- Gateway Info -->
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <p class="text-xs text-gray-600 mb-1">Default Gateway: <span class="font-medium text-gray-900">PayPal</span></p>
                    <p class="text-xs text-gray-600">Default Price: <span class="font-medium text-gray-900">$39.99</span></p>
                </div>

                <!-- Status Toggle -->
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-medium text-gray-900">Status</p>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <!-- Edit Button -->
                <button class="w-full px-4 py-2 border border-indigo-600 text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium transition">
                    Customize
                </button>
            </div>
        </div>

        <!-- Template 3: Dark Premium -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
            <!-- Thumbnail -->
            <div class="bg-gradient-to-b from-gray-200 to-gray-100 h-48 flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2H4V5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v12a2 2 0 002 2h12a2 2 0 002-2V7"/>
                </svg>
            </div>

            <!-- Content -->
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Dark Premium</h3>
                <p class="text-sm text-gray-600 mb-4">Elegant & sophisticated design</p>

                <!-- Gateway Info -->
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <p class="text-xs text-gray-600 mb-1">Default Gateway: <span class="font-medium text-gray-900">Stripe</span></p>
                    <p class="text-xs text-gray-600">Default Price: <span class="font-medium text-gray-900">$49.99</span></p>
                </div>

                <!-- Status Toggle -->
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-medium text-gray-900">Status</p>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <!-- Edit Button -->
                <button class="w-full px-4 py-2 border border-indigo-600 text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium transition">
                    Customize
                </button>
            </div>
        </div>

        <!-- Template 4: Eco Friendly -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
            <!-- Thumbnail -->
            <div class="bg-gradient-to-b from-gray-200 to-gray-100 h-48 flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2H4V5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v12a2 2 0 002 2h12a2 2 0 002-2V7"/>
                </svg>
            </div>

            <!-- Content -->
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Eco Friendly</h3>
                <p class="text-sm text-gray-600 mb-4">Nature-inspired design with green tones</p>

                <!-- Gateway Info -->
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <p class="text-xs text-gray-600 mb-1">Default Gateway: <span class="font-medium text-gray-900">PayPal</span></p>
                    <p class="text-xs text-gray-600">Default Price: <span class="font-medium text-gray-900">$24.99</span></p>
                </div>

                <!-- Status Toggle -->
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-medium text-gray-900">Status</p>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <!-- Edit Button -->
                <button class="w-full px-4 py-2 border border-indigo-600 text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium transition">
                    Customize
                </button>
            </div>
        </div>

        <!-- Template 5: Tech Startup -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
            <!-- Thumbnail -->
            <div class="bg-gradient-to-b from-gray-200 to-gray-100 h-48 flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2H4V5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v12a2 2 0 002 2h12a2 2 0 002-2V7"/>
                </svg>
            </div>

            <!-- Content -->
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Tech Startup</h3>
                <p class="text-sm text-gray-600 mb-4">Modern design perfect for tech companies</p>

                <!-- Gateway Info -->
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <p class="text-xs text-gray-600 mb-1">Default Gateway: <span class="font-medium text-gray-900">Stripe</span></p>
                    <p class="text-xs text-gray-600">Default Price: <span class="font-medium text-gray-900">$59.99</span></p>
                </div>

                <!-- Status Toggle -->
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-medium text-gray-900">Status</p>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <!-- Edit Button -->
                <button class="w-full px-4 py-2 border border-indigo-600 text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium transition">
                    Customize
                </button>
            </div>
        </div>

        <!-- Template 6: Corporate -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
            <!-- Thumbnail -->
            <div class="bg-gradient-to-b from-gray-200 to-gray-100 h-48 flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2H4V5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v12a2 2 0 002 2h12a2 2 0 002-2V7"/>
                </svg>
            </div>

            <!-- Content -->
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Corporate</h3>
                <p class="text-sm text-gray-600 mb-4">Professional corporate landing page</p>

                <!-- Gateway Info -->
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <p class="text-xs text-gray-600 mb-1">Default Gateway: <span class="font-medium text-gray-900">PayPal</span></p>
                    <p class="text-xs text-gray-600">Default Price: <span class="font-medium text-gray-900">$44.99</span></p>
                </div>

                <!-- Status Toggle -->
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-medium text-gray-900">Status</p>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <!-- Edit Button -->
                <button class="w-full px-4 py-2 border border-indigo-600 text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium transition">
                    Customize
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
