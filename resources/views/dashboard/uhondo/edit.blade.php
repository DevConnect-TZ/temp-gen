@extends('layouts.app')

@section('title', 'Edit Uhondo Video')
@section('page_title', 'Edit: ' . $video->title)

@section('content')
<form method="POST" action="{{ route('uhondo.update', $video) }}" enctype="multipart/form-data" class="space-y-8 max-w-4xl">
    @csrf
    @method('PUT')

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

    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 space-y-6">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Video Details</h2>
            <p class="text-sm text-gray-600 mt-1">Update how this video appears on the Uhondo frontend page.</p>
        </div>

        <div class="rounded-lg overflow-hidden bg-black border border-gray-200">
            <video src="{{ $video->video_url }}" class="w-full max-h-96 bg-black" controls preload="metadata"></video>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-900 mb-2">Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title', $video->title) }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    required
                >
            </div>

            <div>
                <label for="episode_label" class="block text-sm font-medium text-gray-900 mb-2">Episode Label</label>
                <input
                    type="text"
                    id="episode_label"
                    name="episode_label"
                    value="{{ old('episode_label', $video->episode_label) }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="S01E01"
                >
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-900 mb-2">Description</label>
            <textarea
                id="description"
                name="description"
                rows="4"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            >{{ old('description', $video->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="display_order" class="block text-sm font-medium text-gray-900 mb-2">Display Order</label>
                <input
                    type="number"
                    id="display_order"
                    name="display_order"
                    value="{{ old('display_order', $video->display_order) }}"
                    min="0"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                >
            </div>

            <label class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <span>
                    <span class="block text-sm font-medium text-gray-900">Active</span>
                    <span class="block text-xs text-gray-600 mt-1">Show this video on the frontend</span>
                </span>
                <input type="checkbox" name="is_active" class="w-5 h-5 text-indigo-600 rounded" {{ old('is_active', $video->is_active) ? 'checked' : '' }}>
            </label>
        </div>

        <div>
            <label for="video" class="block text-sm font-medium text-gray-900 mb-2">Replace Video File</label>
            <input
                type="file"
                id="video"
                name="video"
                accept="video/*"
                class="block w-full text-sm text-gray-700 file:mr-4 file:py-3 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:font-medium hover:file:bg-indigo-100"
            >
            <p class="text-xs text-gray-600 mt-2">Leave empty to keep the current uploaded video.</p>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-6 rounded-lg transition">
            Save Changes
        </button>
        <a href="{{ route('uhondo.index') }}" class="flex-1 border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-3 px-6 rounded-lg transition text-center">
            Cancel
        </a>
    </div>
</form>
@endsection
