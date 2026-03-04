@extends('layouts.app')

@section('title', 'Create New Page')
@section('page_title', 'Create New Page')

@section('content')
<form method="POST" action="/pages" class="space-y-8 max-w-4xl">
    @csrf

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
                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            >
            <p class="text-xs text-gray-600 mt-1">This title will appear in the page header</p>
        </div>

        <!-- Auto-generated Slug -->
        <div class="mb-6">
            <label for="slug" class="block text-sm font-medium text-gray-900 mb-2">Page Slug</label>
            <div class="flex gap-2">
                <input
                    type="text"
                    id="slug"
                    name="slug"
                    readonly
                    placeholder="auto-generated-slug"
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-gray-600 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                >
                <button
                    type="button"
                    class="px-4 py-3 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-medium transition"
                >
                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m16.338 0h.134a8 8 0 00-15.842.856m0 0v5"/>
                    </svg>
                </button>
            </div>
            <p class="text-xs text-gray-600 mt-1">URL-friendly identifier for your page</p>
        </div>
    </div>

    <!-- Template Selection Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Select Template</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Template 1 -->
            <label class="cursor-pointer group">
                <input type="radio" name="template" value="template1" class="hidden" checked>
                <div class="border-2 border-indigo-600 rounded-lg overflow-hidden transition group-hover:shadow-lg">
                    <div class="bg-gradient-to-b from-gray-900 to-gray-800 h-32 flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="p-4 bg-white">
                        <p class="font-medium text-gray-900">YouTube Template</p>
                        <p class="text-xs text-gray-600 mt-1">Video streaming platform style</p>
                    </div>
                </div>
            </label>

            <!-- Template 2 -->
            <label class="cursor-pointer group">
                <input type="radio" name="template" value="template2" class="hidden">
                <div class="border-2 border-gray-300 rounded-lg overflow-hidden transition hover:border-indigo-400 group-hover:shadow-lg">
                    <div class="bg-gradient-to-b from-red-900 to-red-800 h-32 flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16a1 1 0 001 1h8a1 1 0 001-1V4m0 0H4m12 0h4"/>
                        </svg>
                    </div>
                    <div class="p-4 bg-white">
                        <p class="font-medium text-gray-900">Netflix Template</p>
                        <p class="text-xs text-gray-600 mt-1">Streaming entertainment style</p>
                    </div>
                </div>
            </label>
        </div>
    </div>

    <!-- Video Upload Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Background Video</h2>

        <!-- Drag & Drop Area -->
        <div
            id="dragDropZone"
            class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-indigo-500 hover:bg-indigo-50 transition cursor-pointer"
        >
            <input type="file" id="videoFile" name="video" accept="video/*" class="hidden">

            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>

            <p class="text-base font-medium text-gray-900 mb-1">Drag and drop your video here</p>
            <p class="text-sm text-gray-600 mb-4">or click to browse</p>
            <p class="text-xs text-gray-500">MP4, WebM, OGG (Max 500MB)</p>

            <div id="videoPreview" class="mt-4 hidden">
                <p class="text-sm font-medium text-green-600">✓ Video selected</p>
            </div>
        </div>
    </div>

    <!-- Payment Settings Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Payment Settings</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Price Input -->
            <div>
                <label for="price" class="block text-sm font-medium text-gray-900 mb-2">Price</label>
                <div class="relative">
                    <span class="absolute left-4 top-3 text-gray-600">$</span>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        placeholder="0.00"
                        step="0.01"
                        min="0"
                        class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    >
                </div>
                <p class="text-xs text-gray-600 mt-1">Set the price for accessing this page</p>
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
                <!-- Stripe -->
                <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition cursor-pointer">
                    <input type="radio" name="gateway" value="stripe" class="w-4 h-4 text-indigo-600 checked" checked>
                    <span class="ml-3 flex items-center space-x-3 flex-1">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor" class="text-indigo-600">
                            <path d="M16.465 9.07H9.5v2.5h5.45c-.275 1.6-.925 2.85-2.825 3.6V19h2.875c2.65-2.45 4.125-6.05 4.125-9.93z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Stripe</p>
                            <p class="text-xs text-gray-600">Fast, secure payments</p>
                        </div>
                    </span>
                </label>

                <!-- PayPal -->
                <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition cursor-pointer">
                    <input type="radio" name="gateway" value="paypal" class="w-4 h-4 text-indigo-600">
                    <span class="ml-3 flex items-center space-x-3 flex-1">
                        <svg class="w-6 h-6 text-blue-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 12c0 1.657 1.343 3 3 3s3-1.343 3-3-1.343-3-3-3-3 1.343-3 3z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">PayPal</p>
                            <p class="text-xs text-gray-600">Popular & trusted</p>
                        </div>
                    </span>
                </label>
            </div>
        </div>
    </div>

    <!-- Status Section -->
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
    const dragDropZone = document.getElementById('dragDropZone');
    const videoFile = document.getElementById('videoFile');
    const videoPreview = document.getElementById('videoPreview');

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
        videoFile.files = e.dataTransfer.files;
        if (videoFile.files.length > 0) {
            videoPreview.classList.remove('hidden');
        }
    });

    videoFile.addEventListener('change', () => {
        if (videoFile.files.length > 0) {
            videoPreview.classList.remove('hidden');
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
</script>
@endsection
