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

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
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

            <!-- Template 3: Custom Build -->
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

            <div id="videoPreview" class="mt-4 hidden">
                <p class="text-sm font-medium text-green-600">✓ Video selected</p>
            </div>
        </div>

        @if ($errors->has('video'))
            <p class="text-red-600 text-xs mt-2">{{ $errors->first('video') }}</p>
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
    const templateRadios = document.querySelectorAll('.template-radio');
    const templateCards = document.querySelectorAll('.template-card');
    const videoSection = document.getElementById('videoSection');
    const dragDropZone = document.getElementById('dragDropZone');
    const videoFile = document.getElementById('videoFile');
    const videoPreview = document.getElementById('videoPreview');

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

    // Show/hide video section based on template selection
    function updateFormVisibility() {
        const selectedTemplate = document.querySelector('.template-radio:checked');
        const isPreset = selectedTemplate?.dataset.isPreset === 'true';
        
        if (isPreset) {
            videoSection.classList.add('hidden');
            videoFile.removeAttribute('required');
        } else {
            videoSection.classList.remove('hidden');
            videoFile.setAttribute('required', 'required');
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

    // Initialize on page load
    updateFormVisibility();
</script>
@endsection
