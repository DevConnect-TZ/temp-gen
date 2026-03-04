@extends('layouts.app')

@section('title', 'Manage Pages')
@section('page_title', 'Manage Pages')

@section('content')
<!-- Header with Create Button -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
    <div>
        <p class="text-gray-600 text-sm">Manage all your generated landing pages</p>
    </div>
    <button class="flex items-center space-x-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        <a href="/pages/create" class="text-white">Create New Page</a>
    </button>
</div>

<!-- Search and Filter Section -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search -->
        <div class="md:col-span-2">
            <label for="search" class="block text-sm font-medium text-gray-900 mb-2">Search Pages</label>
            <div class="relative">
                <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input
                    type="text"
                    id="search"
                    placeholder="Search by title, slug, or template..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                >
            </div>
        </div>

        <!-- Filter by Status -->
        <div>
            <label for="status-filter" class="block text-sm font-medium text-gray-900 mb-2">Filter by Status</label>
            <select
                id="status-filter"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            >
                <option value="">All Pages</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>
</div>

<!-- Pages Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50">
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Page Title</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Slug</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Template</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($pages as $page)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $page->title }}</td>
                    <td class="px-6 py-4 text-sm">
                        <a href="/{{ $page->slug }}" target="_blank" class="text-indigo-600 hover:underline cursor-pointer font-medium">
                            {{ $page->slug }}
                            <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ ucfirst(str_replace('template', 'Template ', $page->template)) }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $page->price ? 'TZS' . number_format($page->price, 2) : 'Free' }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($page->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $page->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-sm text-center">
                        <div class="flex justify-center items-center space-x-2">
                            <a href="{{ route('pages.edit', $page) }}" class="text-indigo-600 hover:text-indigo-900 font-medium hover:underline" title="Edit">Edit</a>
                            <form action="{{ route('pages.toggle', $page) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-{{ $page->is_active ? 'red' : 'green' }}-600 hover:text-{{ $page->is_active ? 'red' : 'green' }}-900 font-medium hover:underline" title="{{ $page->is_active ? 'Deactivate' : 'Activate' }}">
                                    {{ $page->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <form action="{{ route('pages.destroy', $page) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this page? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium hover:underline" title="Delete">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-600">
                        <p class="text-base font-medium">No pages created yet.</p>
                        <p class="text-sm mt-1"><a href="/pages/create" class="text-indigo-600 hover:underline">Create your first page</a></p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pages->count() > 0)
    <!-- Summary -->
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <p class="text-sm text-gray-600">Total pages: <span class="font-medium">{{ $pages->count() }}</span></p>
    </div>
    @endif
</div>
@endsection
