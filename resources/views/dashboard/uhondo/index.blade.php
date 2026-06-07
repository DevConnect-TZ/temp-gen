@extends('layouts.app')

@section('title', 'Uhondo Videos')
@section('page_title', 'Uhondo')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Uhondo Videos</h1>
            <p class="mt-1 text-sm text-gray-600">Upload and manage the videos shown on the Uhondo frontend page.</p>
        </div>
        <a href="{{ route('api.uhondo-videos.index') }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-medium transition">
            View Public Feed
        </a>
    </div>

    @if ($message = session('success'))
        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800 text-sm font-medium">{{ $message }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-800 font-medium text-sm mb-2">There were errors with your submission:</p>
            <ul class="text-red-700 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="uhondoUploadForm" method="POST" action="{{ route('uhondo.store') }}" enctype="multipart/form-data" class="js-upload-progress-form bg-white rounded-xl shadow-sm p-6 border border-gray-200 space-y-6">
        @csrf

        <div>
            <h2 class="text-lg font-bold text-gray-900">Upload Video</h2>
            <p class="text-sm text-gray-600 mt-1">Upload an MP4, WebM, OGG, or MOV video up to 500MB. Thumbnail image is optional.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-900 mb-2">Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    class="w-full px-4 py-3 border {{ $errors->has('title') ? 'border-red-500' : 'border-gray-300' }} rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="Enter video title"
                    required
                >
            </div>

            <div>
                <label for="episode_label" class="block text-sm font-medium text-gray-900 mb-2">Episode Label</label>
                <input
                    type="text"
                    id="episode_label"
                    name="episode_label"
                    value="{{ old('episode_label') }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="S01E01"
                >
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-900 mb-2">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="Optional short description"
                >{{ old('description') }}</textarea>
            </div>

            <div class="space-y-6">
                <div>
                    <label for="display_order" class="block text-sm font-medium text-gray-900 mb-2">Display Order</label>
                    <input
                        type="number"
                        id="display_order"
                        name="display_order"
                        value="{{ old('display_order', 0) }}"
                        min="0"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    >
                </div>

                <label class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <span>
                        <span class="block text-sm font-medium text-gray-900">Active</span>
                        <span class="block text-xs text-gray-600 mt-1">Show this video on the frontend</span>
                    </span>
                    <input type="checkbox" name="is_active" class="w-5 h-5 text-indigo-600 rounded" checked>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label for="thumbnail" class="block text-sm font-medium text-gray-900 mb-2">Thumbnail Image <span class="text-gray-500 font-normal">(optional)</span></label>
                <input
                    type="file"
                    id="thumbnail"
                    name="thumbnail"
                    accept="image/jpeg,image/png,image/webp"
                    class="block w-full text-sm text-gray-700 file:mr-4 file:py-3 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:font-medium hover:file:bg-indigo-100"
                >
                <p class="text-xs text-gray-600 mt-2">Optional JPG, PNG, or WebP up to 10MB.</p>
            </div>

            <div>
                <label for="video" class="block text-sm font-medium text-gray-900 mb-2">Video File</label>
                <input
                    type="file"
                    id="video"
                    name="video"
                    accept="video/*"
                    class="block w-full text-sm text-gray-700 file:mr-4 file:py-3 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:font-medium hover:file:bg-indigo-100"
                    required
                >
                <p class="text-xs text-gray-600 mt-2">MP4, WebM, OGG, or MOV up to 500MB.</p>
            </div>
        </div>

        <div class="upload-progress hidden rounded-lg border border-indigo-100 bg-indigo-50 p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-indigo-900">Uploading...</p>
                <p class="upload-progress-label text-sm font-semibold text-indigo-700">0%</p>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-white">
                <div class="upload-progress-bar h-full w-0 rounded-full bg-indigo-600 transition-all"></div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                Upload Video
            </button>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-bold text-gray-900">Uploaded Videos</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Video</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($videos as $video)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    @if($video->thumbnail_url)
                                        <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }} thumbnail" class="w-20 h-12 flex-none bg-black rounded object-cover">
                                    @else
                                        <div class="w-20 h-12 flex-none bg-gray-900 rounded flex items-center justify-center text-[10px] text-gray-400">No thumbnail</div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900">{{ $video->title }}</p>
                                        <p class="text-xs text-gray-600 mt-1">{{ $video->episode_label ?: 'No episode label' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $video->display_order }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($video->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Hidden</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $video->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-sm text-center">
                                <div class="flex justify-center items-center gap-3">
                                    <a href="{{ route('uhondo.edit', $video) }}" class="text-indigo-600 hover:text-indigo-900 font-medium hover:underline">Edit</a>
                                    <form action="{{ route('uhondo.destroy', $video) }}" method="POST" onsubmit="return confirm('Delete this Uhondo video? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium hover:underline">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-600">
                                <p class="text-base font-medium">No Uhondo videos uploaded yet.</p>
                                <p class="text-sm mt-1">Upload your first video above.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($videos->count() > 0)
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <p class="text-sm text-gray-600">Total videos: <span class="font-medium">{{ $videos->count() }}</span></p>
            </div>
        @endif
    </div>
</div>

<script>
    document.querySelectorAll('.js-upload-progress-form').forEach((form) => {
        const progress = form.querySelector('.upload-progress');
        const bar = form.querySelector('.upload-progress-bar');
        const label = form.querySelector('.upload-progress-label');
        const submitButton = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', (event) => {
            if (!window.XMLHttpRequest || !progress || !bar || !label) {
                return;
            }

            event.preventDefault();

            const xhr = new XMLHttpRequest();
            const formData = new FormData(form);

            progress.classList.remove('hidden');
            bar.style.width = '0%';
            label.textContent = '0%';

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('opacity-70', 'cursor-not-allowed');
            }

            xhr.upload.addEventListener('progress', (progressEvent) => {
                if (!progressEvent.lengthComputable) {
                    return;
                }

                const percent = Math.round((progressEvent.loaded / progressEvent.total) * 100);
                bar.style.width = `${percent}%`;
                label.textContent = `${percent}%`;
            });

            xhr.addEventListener('load', () => {
                if (xhr.status >= 200 && xhr.status < 400) {
                    window.location.href = xhr.responseURL || form.action;
                    return;
                }

                label.textContent = 'Upload failed';
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-70', 'cursor-not-allowed');
                }
            });

            xhr.addEventListener('error', () => {
                label.textContent = 'Upload failed';
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-70', 'cursor-not-allowed');
                }
            });

            xhr.open(form.method, form.action);
            xhr.send(formData);
        });
    });
</script>
@endsection
