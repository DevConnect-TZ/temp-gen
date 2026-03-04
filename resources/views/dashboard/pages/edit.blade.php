@extends('layouts.app')

@section('title', 'Edit Page')
@section('page_title', 'Edit: ' . $page->title)

@section('content')
<form method="POST" action="{{ route('pages.update', $page) }}" class="space-y-8 max-w-4xl">
    @csrf
    @method('PUT')

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
                value="{{ old('title', $page->title) }}"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            >
            <p class="text-xs text-gray-600 mt-1">This title will appear in the page header</p>
        </div>

        <!-- Page Slug (Display Only) -->
        <div class="mb-6">
            <label for="slug" class="block text-sm font-medium text-gray-900 mb-2">Page Slug</label>
            <div class="flex gap-2">
                <input
                    type="text"
                    id="slug"
                    readonly
                    value="{{ $page->slug }}"
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-gray-600 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                >
            </div>
            <p class="text-xs text-gray-600 mt-1">URL: <strong>/{{ $page->slug }}</strong></p>
        </div>
    </div>

    <!-- Template Information Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Template Information</h2>

        <!-- Template Preview -->
        <div class="mb-6 border border-gray-200 rounded-lg overflow-hidden">
            <div class="h-40 bg-gray-900 flex items-center justify-center">
                @if($page->template === 'template1')
                    <img src="/images/youtubex.jpeg" alt="YouTubeX Template" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    <svg class="w-12 h-12 text-gray-600 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @elseif($page->template === 'template2')
                    <img src="/images/utamuplus.png" alt="UTAMU+ Template" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    <svg class="w-12 h-12 text-gray-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16a1 1 0 001 1h8a1 1 0 001-1V4m0 0H4m12 0h4"/>
                    </svg>
                @else
                    <div class="text-center">
                        <svg class="w-12 h-12 text-indigo-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <p class="text-xs text-indigo-600 font-medium">Custom Template</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Template Details -->
        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-600">Template Type</p>
            <p class="text-lg font-medium text-gray-900 mt-1">
                @if($page->template === 'template1')
                    YouTubeX Template
                @elseif($page->template === 'template2')
                    UTAMU+ Template
                @else
                    Custom Template
                @endif
            </p>
            <p class="text-xs text-gray-600 mt-2">
                @if($page->template === 'custom')
                    Custom template with uploaded video
                @else
                    Pre-built template (cannot be changed)
                @endif
            </p>
        </div>
    </div>

    <!-- Video Upload Section (only for custom template) -->
    @if($page->template === 'custom')
    <div id="videoSection" class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Background Video</h2>

        @if($page->video_path)
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm font-medium text-green-800">✓ Video currently uploaded</p>
            <p class="text-xs text-green-700 mt-1">Upload a new video to replace the current one</p>
        </div>
        @endif

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
                <p class="text-sm font-medium text-green-600">✓ New video selected</p>
            </div>
        </div>
    </div>
    @endif

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
                        value="{{ old('price', $page->price ?? '') }}"
                        class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    >
                </div>
                <p class="text-xs text-gray-600 mt-1">Set the price in Tanzanian Shilling (TZS) for accessing this page</p>
            </div>

            <!-- Payment Gateway Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Payment Gateway</label>
                <select
                    name="payment_gateway"
                    id="payment_gateway"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                >
                    <option value="">None</option>
                    <option value="sonicpesa" {{ old('payment_gateway', $page->payment_gateway) === 'sonicpesa' ? 'selected' : '' }}>SonicPesa</option>
                    <option value="snippe" {{ old('payment_gateway', $page->payment_gateway) === 'snippe' ? 'selected' : '' }}>Snippe</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Status Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Page Status</h2>
                <p class="text-sm text-gray-600 mt-1">Activate or deactivate this page</p>
            </div>

            <!-- Toggle Switch -->
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="active" name="is_active" class="sr-only peer" {{ $page->is_active ? 'checked' : '' }}>
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
            Save Changes
        </button>
        <a
            href="{{ route('pages.index') }}"
            class="flex-1 border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-3 px-6 rounded-lg transition duration-200 text-center"
        >
            Cancel
        </a>
    </div>
</form>

<script>
    const dragDropZone = document.getElementById('dragDropZone');
    const videoFile = document.getElementById('videoFile');
    const videoPreview = document.getElementById('videoPreview');

    @if($page->template === 'custom')
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
    @endif
</script>
@endsection
