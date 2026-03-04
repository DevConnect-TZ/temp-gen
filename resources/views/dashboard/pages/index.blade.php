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
        <span>Create New Page</span>
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
                <!-- Page Row 1 -->
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">Summer Promo Campaign</td>
                    <td class="px-6 py-4 text-sm">
                        <a href="https://landinghub.test/summer-promo-2026" target="_blank" class="text-indigo-600 hover:underline cursor-pointer font-medium">
                            summer-promo-2026
                            <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">Modern Minimalist</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">$29.99</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">Mar 1, 2026</td>
                    <td class="px-6 py-4 text-sm text-center">
                        <div class="flex justify-center items-center space-x-2">
                            <button class="text-indigo-600 hover:text-indigo-900 font-medium hover:underline" title="Edit">Edit</button>
                            <button class="text-red-600 hover:text-red-900 font-medium hover:underline" title="Delete">Delete</button>
                            <div class="relative group">
                                <button class="text-gray-500 hover:text-gray-900">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                    </svg>
                                </button>
                                <div class="absolute right-0 mt-0 w-48 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-10">
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">View Stats</button>
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Disable</button>
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Duplicate</button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- Page Row 2 -->
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">Q1 Product Launch</td>
                    <td class="px-6 py-4 text-sm">
                        <a href="https://landinghub.test/q1-product-launch" target="_blank" class="text-indigo-600 hover:underline cursor-pointer font-medium">
                            q1-product-launch
                            <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">Bold & Bright</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">$49.99</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">Feb 28, 2026</td>
                    <td class="px-6 py-4 text-sm text-center">
                        <div class="flex justify-center items-center space-x-2">
                            <button class="text-indigo-600 hover:text-indigo-900 font-medium hover:underline" title="Edit">Edit</button>
                            <button class="text-red-600 hover:text-red-900 font-medium hover:underline" title="Delete">Delete</button>
                            <div class="relative group">
                                <button class="text-gray-500 hover:text-gray-900">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                    </svg>
                                </button>
                                <div class="absolute right-0 mt-0 w-48 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-10">
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">View Stats</button>
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Disable</button>
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Duplicate</button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- Page Row 3 -->
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">Black Friday Deals</td>
                    <td class="px-6 py-4 text-sm">
                        <a href="https://landinghub.test/black-friday-deals" target="_blank" class="text-indigo-600 hover:underline cursor-pointer font-medium">
                            black-friday-deals
                            <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">Dark Premium</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">$39.99</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">Feb 15, 2026</td>
                    <td class="px-6 py-4 text-sm text-center">
                        <div class="flex justify-center items-center space-x-2">
                            <button class="text-indigo-600 hover:text-indigo-900 font-medium hover:underline" title="Edit">Edit</button>
                            <button class="text-red-600 hover:text-red-900 font-medium hover:underline" title="Delete">Delete</button>
                            <div class="relative group">
                                <button class="text-gray-500 hover:text-gray-900">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                    </svg>
                                </button>
                                <div class="absolute right-0 mt-0 w-48 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-10">
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">View Stats</button>
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Enable</button>
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Duplicate</button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- Page Row 4 -->
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">Newsletter Signup</td>
                    <td class="px-6 py-4 text-sm">
                        <a href="https://landinghub.test/newsletter-signup" target="_blank" class="text-indigo-600 hover:underline cursor-pointer font-medium">
                            newsletter-signup
                            <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">Clean & Simple</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">$19.99</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">Feb 10, 2026</td>
                    <td class="px-6 py-4 text-sm text-center">
                        <div class="flex justify-center items-center space-x-2">
                            <button class="text-indigo-600 hover:text-indigo-900 font-medium hover:underline" title="Edit">Edit</button>
                            <button class="text-red-600 hover:text-red-900 font-medium hover:underline" title="Delete">Delete</button>
                            <div class="relative group">
                                <button class="text-gray-500 hover:text-gray-900">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                    </svg>
                                </button>
                                <div class="absolute right-0 mt-0 w-48 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-10">
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">View Stats</button>
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Disable</button>
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Duplicate</button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between bg-gray-50">
        <p class="text-sm text-gray-600">Showing <span class="font-medium">1</span> to <span class="font-medium">4</span> of <span class="font-medium">24</span> pages</p>

        <div class="flex gap-2">
            <button class="px-3 py-2 border border-gray-300 text-gray-700 hover:bg-gray-100 rounded-lg text-sm font-medium transition" disabled>
                ← Previous
            </button>

            <div class="flex gap-1">
                <button class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium">1</button>
                <button class="px-3 py-2 border border-gray-300 text-gray-700 hover:bg-gray-100 rounded-lg text-sm font-medium transition">2</button>
                <button class="px-3 py-2 border border-gray-300 text-gray-700 hover:bg-gray-100 rounded-lg text-sm font-medium transition">3</button>
                <span class="px-3 py-2 text-gray-600 text-sm">...</span>
                <button class="px-3 py-2 border border-gray-300 text-gray-700 hover:bg-gray-100 rounded-lg text-sm font-medium transition">6</button>
            </div>

            <button class="px-3 py-2 border border-gray-300 text-gray-700 hover:bg-gray-100 rounded-lg text-sm font-medium transition">
                Next →
            </button>
        </div>
    </div>
</div>
@endsection
