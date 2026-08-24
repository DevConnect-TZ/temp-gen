@extends('layouts.app')

@section('title', 'Create New Page')
@section('page_title', 'Create New Page')

@section('content')
<form method="POST" action="/pages" enctype="multipart/form-data" class="space-y-8 max-w-4xl">
    @csrf

    <!-- Display Validation Errors -->
    @if ($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <p class="text-red-800 font-medium text-sm mb-2">There were errors with your submission:</p>
        <ul class="text-red-700 text-sm space-y-1">
            @foreach ($errors->all() as $error)
                <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Page Title Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Page Information</h2>

        <!-- Page Title -->
        <div class="mb-6">
            <label for="title" class="block text-sm font-medium text-gray-900 mb-2">Page Title</label>
            <input
                type="text"
                id="title"
                name="title"
                placeholder="Enter page title"
                value="{{ old('title') }}"
                class="w-full px-4 py-3 border {{ $errors->has('title') ? 'border-red-500' : 'border-gray-300' }} rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            >
            @if ($errors->has('title'))
                <p class="text-red-600 text-xs mt-1">{{ $errors->first('title') }}</p>
            @else
                <p class="text-xs text-gray-600 mt-1">This title will appear in the page header</p>
            @endif
        </div>

        <!-- Auto-generated Slug -->
        <div class="mb-6">
            <label for="slug" class="block text-sm font-medium text-gray-900 mb-2">Page Slug (Auto-generated)</label>
            <div class="flex gap-2">
                <input
                    type="text"
                    id="slug"
                    readonly
                    placeholder="auto-generated-slug"
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-gray-600 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                >
            </div>
            <p class="text-xs text-gray-600 mt-1">URL-friendly identifier (auto-generated from title)</p>
        </div>
    </div>

    <!-- Template Selection Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Select Template</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Template 1: YouTubeX -->
            <label class="cursor-pointer group">
                <input type="radio" name="template" value="template1" class="hidden template-radio" data-is-preset="true" {{ old('template') === 'template1' || (!old('template') && !$errors->any()) ? 'checked' : '' }}>
                <div class="template-card border-2 border-indigo-600 rounded-lg overflow-hidden transition group-hover:shadow-lg">
                    <!-- Preview Image -->
                    <div class="h-40 bg-gray-900 overflow-hidden flex items-center justify-center">
                        <img src="/images/youtubex.jpeg" alt="YouTubeX Template Preview" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<svg class=\"w-12 h-12 text-gray-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z\"/><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M21 12a9 9 0 11-18 0 9 9 0 0118 0z\"/></svg>'">
                    </div>
                    <!-- Template Info -->
                    <div class="p-4 bg-white">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium text-gray-900">YouTubeX Template</p>
                                <p class="text-xs text-gray-600 mt-1">Video streaming platform</p>
                            </div>
                            <div class="template-check hidden">
                                <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </label>

            <!-- Template 2: UTAMU+ -->
            <label class="cursor-pointer group">
                <input type="radio" name="template" value="template2" class="hidden template-radio" data-is-preset="true" {{ old('template') === 'template2' ? 'checked' : '' }}>
                <div class="template-card border-2 border-gray-300 rounded-lg overflow-hidden transition hover:border-indigo-400 group-hover:shadow-lg">
                    <!-- Preview Image -->
                    <div class="h-40 bg-gray-900 overflow-hidden flex items-center justify-center">
                        <img src="/images/utamuplus.png" alt="UTAMU+ Template Preview" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<svg class=\"w-12 h-12 text-gray-400\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M7 4v16a1 1 0 001 1h8a1 1 0 001-1V4m0 0H4m12 0h4\"/></svg>'">
                    </div>
                    <!-- Template Info -->
                    <div class="p-4 bg-white">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium text-gray-900">UTAMU+ Template</p>
                                <p class="text-xs text-gray-600 mt-1">Premium content platform</p>
                            </div>
                            <div class="template-check hidden">
                                <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </label>

            <!-- Template 3: MAUTAMU -->
            <label class="cursor-pointer group">
                <input type="radio" name="template" value="template3" class="hidden template-radio" data-is-preset="true" {{ old('template') === 'template3' ? 'checked' : '' }}>
                <div class="template-card border-2 border-gray-300 rounded-lg overflow-hidden transition hover:border-indigo-400 group-hover:shadow-lg">
                    <div class="h-40 bg-gray-900 overflow-hidden flex items-center justify-center">
                        <img src="/images/template3.png" alt="MAUTAMU Template Preview" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<svg class=\"w-12 h-12 text-gray-400\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z\"/></svg>'">
                    </div>
                    <div class="p-4 bg-white">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium text-gray-900">MAUTAMU Template</p>
                                <p class="text-xs text-gray-600 mt-1">Gallery-style video grid</p>
                            </div>
                            <div class="template-check hidden">
                                <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </label>

            <!-- Template 4: WhatsApp Group -->
            <label class="cursor-pointer group">
                <input type="radio" name="template" value="template4" class="hidden template-radio" data-is-preset="true" {{ old('template') === 'template4' ? 'checked' : '' }}>
                <div class="template-card border-2 border-gray-300 rounded-lg overflow-hidden transition hover:border-indigo-400 group-hover:shadow-lg">
                    <div class="h-40 bg-gray-900 overflow-hidden flex items-center justify-center">
                        <img src="/images/template4.png" alt="WhatsApp Group Template Preview" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<svg class=\"w-12 h-12 text-gray-400\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z\"/></svg>'">
                    </div>
                    <div class="p-4 bg-white">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium text-gray-900">WhatsApp Group Template</p>
                                <p class="text-xs text-gray-600 mt-1">WhatsApp-style chat landing page</p>
                            </div>
                            <div class="template-check hidden">
                                <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </label>

            <!-- Template 5: TikTok Live -->
            <label class="cursor-pointer group">
                <input type="radio" name="template" value="template5" class="hidden template-radio" data-is-preset="false" {{ old('template') === 'template5' ? 'checked' : '' }}>
                <div class="template-card border-2 border-gray-300 rounded-lg overflow-hidden transition hover:border-indigo-400 group-hover:shadow-lg">
                    <div class="h-40 bg-gray-900 overflow-hidden flex items-center justify-center">
                        <img src="/images/tiktoklive.png" alt="TikTok Live Template Preview" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<svg class=\"w-12 h-12 text-gray-400\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z\"/><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M21 12a9 9 0 11-18 0 9 9 0 0118 0z\"/></svg>'">
                    </div>
                    <div class="p-4 bg-white">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium text-gray-900">TikTok Live Template</p>
                                <p class="text-xs text-gray-600 mt-1">TikTok-style live with uploaded video</p>
                            </div>
                            <div class="template-check hidden">
                                <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </label>

            <!-- Template 5: Custom Build -->
            <label class="cursor-pointer group">
                <input type="radio" name="template" value="custom" class="hidden template-radio" data-is-preset="false" {{ old('template') === 'custom' ? 'checked' : '' }}>
                <div class="template-card border-2 border-gray-300 rounded-lg overflow-hidden transition hover:border-indigo-400 group-hover:shadow-lg">
                    <!-- Custom Build Icon -->
                    <div class="h-40 bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-indigo-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <p class="text-xs text-indigo-600 font-medium">Upload Video</p>
                        </div>
                    </div>
                    <!-- Template Info -->
                    <div class="p-4 bg-white">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium text-gray-900">Custom Build</p>
                                <p class="text-xs text-gray-600 mt-1">Create with your own video</p>
                            </div>
                            <div class="template-check hidden">
                                <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </label>
        </div>
        @if ($errors->has('template'))
            <p class="text-red-600 text-xs mt-3">{{ $errors->first('template') }}</p>
        @endif
    </div>

    <!-- Video Upload Section (only for custom template) -->
    <div id="videoSection" class="hidden bg-white rounded-xl shadow-sm p-6 border {{ $errors->has('video') ? 'border-red-500' : 'border-gray-200' }}">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Background Video</h2>

        <input type="hidden" id="videoPathInput" name="video_path" value="{{ old('video_path') }}">

        <!-- Drag & Drop Area -->
        <div
            id="dragDropZone"
            class="border-2 border-dashed {{ $errors->has('video') ? 'border-red-500 bg-red-50' : 'border-gray-300' }} rounded-lg p-12 text-center hover:border-indigo-500 hover:bg-indigo-50 transition cursor-pointer"
        >
            <input type="file" id="videoFile" name="video" accept="video/*" class="hidden">

            <svg class="w-12 h-12 {{ $errors->has('video') ? 'text-red-400' : 'text-gray-400' }} mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>

            <p class="text-base font-medium text-gray-900 mb-1">Drag and drop your video here</p>
            <p class="text-sm text-gray-600 mb-4">or click to browse</p>
            <p class="text-xs text-gray-500">MP4, WebM, OGG (Max 500MB)</p>

            <div id="videoDetails" class="mt-4 hidden text-left bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p id="videoFileName" class="text-sm font-medium text-gray-900 truncate"></p>
                        <p id="videoFileSize" class="text-xs text-gray-600 mt-0.5"></p>
                    </div>
                    <button type="button" id="videoRemoveBtn" class="text-xs font-medium text-red-600 hover:text-red-800 flex-shrink-0">Remove</button>
                </div>
                <div id="videoMetaInfo" class="mt-2 hidden text-xs text-gray-600"></div>
                <div id="videoInspectStatus" class="mt-2 hidden text-xs font-medium"></div>
            </div>

            <!-- Upload Progress -->
            <div id="uploadProgress" class="mt-6 hidden">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-gray-900">
                        <span id="uploadStage">Checking video...</span>
                    </p>
                    <p class="text-sm font-bold text-indigo-600" id="uploadPercent">0%</p>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div id="uploadProgressBar" class="bg-indigo-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <div class="flex items-center justify-between mt-2">
                    <p class="text-xs text-gray-600" id="uploadStats">Preparing upload...</p>
                    <button type="button" id="uploadCancelBtn" class="text-xs font-medium text-red-600 hover:text-red-800">Cancel</button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Keep this page open while the video uploads</p>
            </div>
        </div>

        @if ($errors->has('video'))
            <p class="text-red-600 text-xs mt-2">{{ $errors->first('video') }}</p>
        @endif
    </div>

    <!-- Account Name Section (only for TikTok Live template) -->
    <div id="accountNameSection" class="hidden bg-white rounded-xl shadow-sm p-6 border {{ $errors->has('account_name') ? 'border-red-500' : 'border-gray-200' }}">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Live Account Name</h2>

        <label for="account_name" class="block text-sm font-medium text-gray-900 mb-2">Account Name</label>
        <input
            type="text"
            id="account_name"
            name="account_name"
            placeholder="e.g. @juma_live"
            value="{{ old('account_name') }}"
            class="w-full px-4 py-3 border {{ $errors->has('account_name') ? 'border-red-500' : 'border-gray-300' }} rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
        >
        @if ($errors->has('account_name'))
            <p class="text-red-600 text-xs mt-1">{{ $errors->first('account_name') }}</p>
        @else
            <p class="text-xs text-gray-600 mt-1">Shown at the top-right of the TikTok LIVE screen</p>
        @endif
    </div>

    <!-- Payment Settings Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Payment Settings</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Price Input -->
            <div>
                <label for="price" class="block text-sm font-medium text-gray-900 mb-2">Price (TZS)</label>
                <div class="relative">
                    <span class="absolute left-4 top-3 text-gray-600">TZS</span>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        placeholder="0.00"
                        step="0.01"
                        min="0"
                        value="{{ old('price') }}"
                        class="w-full pl-12 pr-4 py-3 border {{ $errors->has('price') ? 'border-red-500' : 'border-gray-300' }} rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    >
                </div>
                @if ($errors->has('price'))
                    <p class="text-red-600 text-xs mt-1">{{ $errors->first('price') }}</p>
                @else
                    <p class="text-xs text-gray-600 mt-1">Set the price in Tanzanian Shilling (TZS) for accessing this page</p>
                @endif
            </div>

            <!-- Payment Delay -->
            <div>
                <label for="delay" class="block text-sm font-medium text-gray-900 mb-2">Payment Delay (seconds)</label>
                <input
                    type="number"
                    id="delay"
                    name="payment_delay"
                    placeholder="0"
                    min="0"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                >
                <p class="text-xs text-gray-600 mt-1">Delay payment request by N seconds</p>
            </div>
        </div>

        <!-- Payment Gateway Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-900 mb-4">Payment Gateway</label>

            <div class="space-y-3">
                <!-- SonicPesa -->
                <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition cursor-pointer">
                    <input type="radio" name="payment_gateway" value="sonicpesa" class="w-4 h-4 text-indigo-600" {{ old('payment_gateway') === 'sonicpesa' || (!old('payment_gateway') && !$errors->any()) ? 'checked' : '' }}>
                    <span class="ml-3 flex items-center space-x-3 flex-1">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">SonicPesa</p>
                            <p class="text-xs text-gray-600">Mobile money USSD payment</p>
                        </div>
                    </span>
                </label>

                <!-- Snippe -->
                <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition cursor-pointer">
                    <input type="radio" name="payment_gateway" value="snippe" class="w-4 h-4 text-indigo-600" {{ old('payment_gateway') === 'snippe' ? 'checked' : '' }}>
                    <span class="ml-3 flex items-center space-x-3 flex-1">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Snippe</p>
                            <p class="text-xs text-gray-600">Alternative payment gateway</p>
                        </div>
                    </span>
                </label>

                <!-- FastLipa -->
                <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition cursor-pointer">
                    <input type="radio" name="payment_gateway" value="fastlipa" class="w-4 h-4 text-emerald-600" {{ old('payment_gateway') === 'fastlipa' ? 'checked' : '' }}>
                    <span class="ml-3 flex items-center space-x-3 flex-1">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">FastLipa</p>
                            <p class="text-xs text-gray-600">Mobile money payments</p>
                        </div>
                    </span>
                </label>

                <!-- Mobilipa -->
                <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition cursor-pointer">
                    <input type="radio" name="payment_gateway" value="mobilipa" class="w-4 h-4 text-lime-600" {{ old('payment_gateway') === 'mobilipa' ? 'checked' : '' }}>
                    <span class="ml-3 flex items-center space-x-3 flex-1">
                        <svg class="w-6 h-6 text-lime-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Mobilipa</p>
                            <p class="text-xs text-gray-600">Mobile money USSD payments</p>
                        </div>
                    </span>
                </label>
                <!-- PesaLink -->
                <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition cursor-pointer">
                    <input type="radio" name="payment_gateway" value="pesalink" class="w-4 h-4 text-orange-600" {{ old('payment_gateway') === 'pesalink' ? 'checked' : '' }}>
                    <span class="ml-3 flex items-center space-x-3 flex-1">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">PesaLink</p>
                            <p class="text-xs text-gray-600">Mobile money payments via PesaLink</p>
                        </div>
                    </span>
                </label>
            </div>

            <!-- PesaLink Account Selection (shown only when PesaLink is selected) -->
            <div id="pesalinkAccountSection" class="mt-6 {{ old('payment_gateway') === 'pesalink' ? '' : 'hidden' }}">
                <label for="pesalink_account_id" class="block text-sm font-medium text-gray-900 mb-2">PesaLink Sub-Account</label>
                <select name="pesalink_account_id" id="pesalink_account_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    <option value="">Select a PesaLink account...</option>
                    @foreach(\App\Models\PesaLinkAccount::where('is_active', true)->get() as $account)
                        <option value="{{ $account->id }}" {{ old('pesalink_account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-600 mt-1">Choose which PesaLink API account to use for payments on this page</p>
            </div>
        </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Page Status</h2>
                <p class="text-sm text-gray-600 mt-1">Activate this page immediately upon creation</p>
            </div>

            <!-- Toggle Switch -->
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="active" name="is_active" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-4 pt-6">
        <button
            type="submit"
            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-6 rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            Create Page
        </button>
        <button
            type="button"
            class="flex-1 border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-3 px-6 rounded-lg transition duration-200"
        >
            Cancel
        </button>
    </div>
</form>

<script>
    const templateRadios = document.querySelectorAll('.template-radio');
    const templateCards = document.querySelectorAll('.template-card');
    const videoSection = document.getElementById('videoSection');
    const accountNameSection = document.getElementById('accountNameSection');
    const dragDropZone = document.getElementById('dragDropZone');
    const videoFile = document.getElementById('videoFile');
    const videoPathInput = document.getElementById('videoPathInput');
    const videoDetails = document.getElementById('videoDetails');
    const videoFileName = document.getElementById('videoFileName');
    const videoFileSize = document.getElementById('videoFileSize');
    const videoMetaInfo = document.getElementById('videoMetaInfo');
    const videoInspectStatus = document.getElementById('videoInspectStatus');
    const videoRemoveBtn = document.getElementById('videoRemoveBtn');
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadStage = document.getElementById('uploadStage');
    const uploadPercent = document.getElementById('uploadPercent');
    const uploadProgressBar = document.getElementById('uploadProgressBar');
    const uploadStats = document.getElementById('uploadStats');
    const uploadCancelBtn = document.getElementById('uploadCancelBtn');
    const pageForm = document.querySelector('form[action="/pages"]');
    const uploadVideoUrl = @json(route('pages.upload-video'));

    const MAX_VIDEO_SIZE = 500 * 1024 * 1024;
    const ALLOWED_EXTENSIONS = ['mp4', 'webm', 'ogv'];
    let activeUpload = null;
    let uploadInProgress = false;

    // Update visual selection state
    function updateTemplateSelection() {
        const selectedTemplate = document.querySelector('.template-radio:checked');

        templateCards.forEach((card, index) => {
            const radio = templateRadios[index];
            const checkIcon = card.querySelector('.template-check');

            if (radio.checked) {
                // Add selected state
                card.classList.remove('border-gray-300');
                card.classList.add('border-indigo-600', 'shadow-lg');
                checkIcon.classList.remove('hidden');
            } else {
                // Remove selected state
                card.classList.remove('border-indigo-600', 'shadow-lg');
                card.classList.add('border-gray-300');
                checkIcon.classList.add('hidden');
            }
        });
    }

    // Show/hide video + account name sections based on template selection
    function updateFormVisibility() {
        const selectedTemplate = document.querySelector('.template-radio:checked');
        const isPreset = selectedTemplate?.dataset.isPreset === 'true';
        const isTikTok = selectedTemplate?.value === 'template5';

        if (isPreset) {
            videoSection.classList.add('hidden');
        } else {
            videoSection.classList.remove('hidden');
        }

        if (isTikTok) {
            accountNameSection.classList.remove('hidden');
        } else {
            accountNameSection.classList.add('hidden');
        }

        updateTemplateSelection();
    }

    templateRadios.forEach(radio => {
        radio.addEventListener('change', updateFormVisibility);
    });

    // Video drag & drop
    dragDropZone.addEventListener('click', () => videoFile.click());

    dragDropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dragDropZone.classList.add('border-indigo-500', 'bg-indigo-50');
    });

    dragDropZone.addEventListener('dragleave', () => {
        dragDropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
    });

    dragDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dragDropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
        if (e.dataTransfer.files.length > 0) {
            videoFile.files = e.dataTransfer.files;
            handleVideoSelected();
        }
    });

    videoFile.addEventListener('change', handleVideoSelected);

    videoRemoveBtn.addEventListener('click', () => {
        if (activeUpload) {
            activeUpload.abort();
        }
        activeUpload = null;
        videoFile.value = '';
        videoPathInput.value = '';
        hideUploadUI();
        resetVideoUI();
    });

    function resetVideoUI() {
        videoDetails.classList.add('hidden');
        videoMetaInfo.classList.add('hidden');
        videoInspectStatus.classList.add('hidden');
        videoInspectStatus.className = 'mt-2 hidden text-xs font-medium';
        uploadInProgress = false;
    }

    function formatBytes(bytes) {
        if (bytes >= 1024 * 1024 * 1024) {
            return (bytes / (1024 * 1024 * 1024)).toFixed(2) + ' GB';
        }
        if (bytes >= 1024 * 1024) {
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }
        return (bytes / 1024).toFixed(0) + ' KB';
    }

    function formatDuration(seconds) {
        if (!isFinite(seconds)) {
            return 'unknown';
        }
        const mins = Math.floor(seconds / 60);
        const secs = Math.round(seconds % 60);
        return mins + ':' + String(secs).padStart(2, '0');
    }

    function setInspectStatus(text, type) {
        videoInspectStatus.classList.remove('hidden');
        if (type === 'error') {
            videoInspectStatus.className = 'mt-2 text-xs font-medium text-red-600';
        } else if (type === 'ok') {
            videoInspectStatus.className = 'mt-2 text-xs font-medium text-green-600';
        } else {
            videoInspectStatus.className = 'mt-2 text-xs font-medium text-gray-600';
        }
        videoInspectStatus.textContent = text;
    }

    /**
     * Verify the browser can actually decode and play the selected file by
     * loading its metadata, so we know it will play before uploading.
     */
    function verifyVideoIsPlayable(file) {
        return new Promise((resolve) => {
            const objectUrl = URL.createObjectURL(file);
            const probe = document.createElement('video');
            probe.preload = 'metadata';
            probe.muted = true;

            const cleanup = () => URL.revokeObjectURL(objectUrl);
            const timeout = setTimeout(() => {
                cleanup();
                resolve({ ok: false, error: 'Could not read the video file. It may be corrupted.' });
            }, 20000);

            probe.addEventListener('loadedmetadata', () => {
                clearTimeout(timeout);
                const meta = {
                    ok: probe.videoWidth > 0 && probe.duration > 0,
                    duration: probe.duration,
                    width: probe.videoWidth,
                    height: probe.videoHeight,
                };
                cleanup();
                resolve(meta.ok ? meta : { ...meta, error: 'The video has no playable track.' });
            });

            probe.addEventListener('error', () => {
                clearTimeout(timeout);
                cleanup();
                resolve({ ok: false, error: 'This file cannot be played by the browser. Please upload a valid MP4, WebM or OGV video.' });
            });

            probe.src = objectUrl;
        });
    }

    async function handleVideoSelected() {
        resetVideoUI();

        if (videoFile.files.length === 0) {
            return;
        }

        const file = videoFile.files[0];
        const extension = file.name.split('.').pop().toLowerCase();

        videoDetails.classList.remove('hidden');
        videoFileName.textContent = file.name;
        videoFileSize.textContent = formatBytes(file.size);

        if (!ALLOWED_EXTENSIONS.includes(extension)) {
            setInspectStatus('✗ Unsupported format ".' + extension + '". Allowed: MP4, WebM, OGV.', 'error');
            return;
        }

        if (file.size === 0) {
            setInspectStatus('✗ The selected file is empty.', 'error');
            return;
        }

        if (file.size > MAX_VIDEO_SIZE) {
            setInspectStatus('✗ File is ' + formatBytes(file.size) + '. Maximum allowed size is 500 MB.', 'error');
            return;
        }

        setInspectStatus('Checking video...', 'checking');

        const probe = await verifyVideoIsPlayable(file);

        if (!probe.ok) {
            setInspectStatus('✗ ' + probe.error, 'error');
            return;
        }

        videoMetaInfo.classList.remove('hidden');
        videoMetaInfo.textContent = 'Duration ' + formatDuration(probe.duration) + ' • ' + probe.width + '×' + probe.height;
        setInspectStatus('✓ Video verified - uploading now...', 'ok');

        startVideoUpload(file);
    }

    /**
     * Upload the selected video immediately so the upload progress is shown
     * right after the file is chosen. On success the returned path is stored
     * in the hidden video_path field referenced by the page form.
     */
    function startVideoUpload(file) {
        const csrfToken = pageForm.querySelector('input[name="_token"]').value;
        const formData = new FormData();
        formData.append('video', file);

        uploadInProgress = true;
        resetUploadBar();
        showUploadUI();

        const xhr = new XMLHttpRequest();
        activeUpload = xhr;
        const startTime = Date.now();

        xhr.open('POST', uploadVideoUrl);
        xhr.responseType = 'json';
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

        xhr.upload.addEventListener('progress', (event) => updateUploadProgress(event, startTime));
        xhr.addEventListener('load', () => {
            uploadInProgress = false;
            activeUpload = null;

            if (xhr.status === 200 && xhr.response?.status === 'success') {
                videoPathInput.value = xhr.response.data.video_path;
                uploadStage.textContent = 'Upload complete';
                uploadPercent.textContent = '100%';
                uploadProgressBar.style.width = '100%';
                uploadStats.textContent = formatBytes(file.size) + ' uploaded - video ready to play';
                setInspectStatus('✓ Video uploaded successfully', 'ok');
            } else if (xhr.status === 422) {
                const message = Object.values(xhr.response?.errors || {}).flat().join(' ')
                    || (xhr.response?.message || 'The video was rejected. Please try another file.');
                showUploadError(message);
                setInspectStatus('✗ Upload failed - select another video or try again', 'error');
            } else {
                showUploadError(xhr.response?.message || 'Upload failed (HTTP ' + xhr.status + '). Please try again.');
                setInspectStatus('✗ Upload failed - select another video or try again', 'error');
            }
        });
        xhr.addEventListener('error', () => {
            uploadInProgress = false;
            activeUpload = null;
            showUploadError('Network error during upload. Please check your connection and try again.');
            setInspectStatus('✗ Upload failed - select another video or try again', 'error');
        });
        xhr.addEventListener('abort', () => {
            uploadInProgress = false;
            activeUpload = null;
            showUploadError('Upload cancelled.');
            setInspectStatus('✗ Upload cancelled - select the video again to retry', 'error');
        });

        xhr.send(formData);
    }

    function isCustomTemplateSelected() {
        const selected = document.querySelector('.template-radio:checked');
        return selected && (selected.value === 'custom' || selected.value === 'template5');
    }

    function showUploadUI() {
        uploadProgress.classList.remove('hidden');
        uploadStage.textContent = 'Uploading video...';
        uploadPercent.textContent = '0%';
        uploadProgressBar.style.width = '0%';
        uploadStats.textContent = 'Preparing upload...';
    }

    function hideUploadUI() {
        uploadProgress.classList.add('hidden');
    }

    function updateUploadProgress(event, startTime) {
        if (!event.lengthComputable) {
            return;
        }

        const percent = Math.round((event.loaded / event.total) * 100);
        const uploaded = formatBytes(event.loaded);
        const total = formatBytes(event.total);
        const elapsedSeconds = Math.max((Date.now() - startTime) / 1000, 0.5);
        const speed = event.loaded / elapsedSeconds;
        const remainingBytes = Math.max(event.total - event.loaded, 0);
        const etaSeconds = speed > 0 ? remainingBytes / speed : 0;
        const etaMinutes = Math.floor(etaSeconds / 60);
        const eta = etaMinutes + ':' + String(Math.round(etaSeconds % 60)).padStart(2, '0');

        uploadPercent.textContent = percent + '%';
        uploadProgressBar.style.width = percent + '%';
        uploadStats.textContent = uploaded + ' of ' + total + ' • ' + formatBytes(speed) + '/s • ETA ' + eta;
    }

    function showUploadError(message) {
        uploadStage.textContent = 'Upload failed';
        uploadPercent.textContent = '';
        uploadProgressBar.classList.remove('bg-indigo-600');
        uploadProgressBar.classList.add('bg-red-600');
        uploadStats.textContent = message;
    }

    function resetUploadBar() {
        uploadProgressBar.classList.remove('bg-red-600');
        uploadProgressBar.classList.add('bg-indigo-600');
    }

    uploadCancelBtn.addEventListener('click', () => {
        if (activeUpload) {
            activeUpload.abort();
        }
    });

    pageForm.addEventListener('submit', (event) => {
        if (!isCustomTemplateSelected()) {
            return;
        }

        if (uploadInProgress) {
            event.preventDefault();
            setInspectStatus('Please wait for the video upload to finish.', 'error');
            uploadProgress.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        if (!videoPathInput.value) {
            event.preventDefault();
            setInspectStatus('Please select and upload a video before creating the page.', 'error');
            videoSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // Auto-generate slug
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');

    titleInput.addEventListener('input', () => {
        const slug = titleInput.value
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
        slugInput.value = slug;
    });

    // Initialize on page load
    updateFormVisibility();

    // Toggle PesaLink account selector based on gateway selection
    const gatewayRadios = document.querySelectorAll('input[name="payment_gateway"]');
    const pesalinkAccountSection = document.getElementById('pesalinkAccountSection');

    function togglePesaLinkAccount() {
        const selectedGateway = document.querySelector('input[name="payment_gateway"]:checked');
        if (selectedGateway && selectedGateway.value === 'pesalink') {
            pesalinkAccountSection.classList.remove('hidden');
        } else {
            pesalinkAccountSection.classList.add('hidden');
        }
    }

    gatewayRadios.forEach(radio => {
        radio.addEventListener('change', togglePesaLinkAccount);
    });
</script>
@endsection
