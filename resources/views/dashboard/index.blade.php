@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Generated Pages -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total Generated Pages</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalPages }}</p>
                <p class="text-green-600 text-xs font-medium mt-2">Total pages created</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Active Pages -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Active Pages</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $activePages }}</p>
                <p class="text-gray-600 text-xs font-medium mt-2">Currently live</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Inactive Pages -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Inactive Pages</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $inactivePages }}</p>
                <p class="text-gray-600 text-xs font-medium mt-2">Paused or disabled</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M6.343 3.665c.886-.887 1.303-1.330 1.906-1.497.602-.167 1.31.008 2.725.361l2.05.512c.545.136.817.204 1.076.204.259 0 .531-.068 1.076-.204l2.05-.512c1.415-.353 2.123-.528 2.725-.361.603.167 1.02.61 1.906 1.497.886.887 1.329 1.303 1.497 1.906.166.602-.009 1.31-.361 2.725l-.512 2.05c-.136.545-.204.817-.204 1.076 0 .259.068.531.204 1.076l.512 2.05c.352 1.415.527 2.123.361 2.725-.168.603-.611 1.02-1.497 1.906-.887.886-1.303 1.329-1.906 1.497-.602.166-1.31-.009-2.725-.361l-2.05-.512c-.545-.136-.817-.204-1.076-.204-.259 0-.531.068-1.076.204l-2.05.512c-1.415.352-2.123.527-2.725.361-.603-.168-1.02-.611-1.906-1.497-.886-.887-1.329-1.303-1.497-1.906-.166-.602.009-1.31.361-2.725l.512-2.05c.136-.545.204-.817.204-1.076 0-.259-.068-.531-.204-1.076l-.512-2.05c-.352-1.415-.527-2.123-.361-2.725.168-.603.611-1.02 1.497-1.906z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total Revenue</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">TZS {{ number_format($totalRevenue, 0) }}</p>
                <p class="text-green-600 text-xs font-medium mt-2">From completed transactions</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Chart Section -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Revenue Trend</h2>
            <div class="h-80 flex items-end justify-between">
                <div class="w-full h-full flex items-end justify-between space-x-2">
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full h-24 bg-indigo-100 rounded-t-lg"></div>
                        <p class="text-xs text-gray-600 mt-2">Jan</p>
                    </div>
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full h-32 bg-indigo-200 rounded-t-lg"></div>
                        <p class="text-xs text-gray-600 mt-2">Feb</p>
                    </div>
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full h-40 bg-indigo-300 rounded-t-lg"></div>
                        <p class="text-xs text-gray-600 mt-2">Mar</p>
                    </div>
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full h-36 bg-indigo-200 rounded-t-lg"></div>
                        <p class="text-xs text-gray-600 mt-2">Apr</p>
                    </div>
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full h-48 bg-indigo-400 rounded-t-lg"></div>
                        <p class="text-xs text-gray-600 mt-2">May</p>
                    </div>
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full h-44 bg-indigo-300 rounded-t-lg"></div>
                        <p class="text-xs text-gray-600 mt-2">Jun</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Sidebar -->
    <div class="space-y-6">
        <!-- Average Order Value -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-gray-600 text-sm font-medium mb-2">Avg. Order Value</p>
            <p class="text-2xl font-bold text-gray-900">TZS142.67</p>
            <p class="text-green-600 text-xs font-medium mt-3">↑ 5% vs last month</p>
        </div>

        <!-- Conversion Rate -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-gray-600 text-sm font-medium mb-2">Conversion Rate</p>
            <p class="text-2xl font-bold text-gray-900">3.24%</p>
            <p class="text-gray-600 text-xs font-medium mt-3">328 total conversions</p>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-gray-900 text-sm font-bold mb-4">Quick Actions</p>
            <button class="w-full flex items-center justify-center space-x-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>New Page</span>
            </button>
            <button class="w-full flex items-center justify-center space-x-2 px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                <span>View Reports</span>
            </button>
        </div>
    </div>
</div>

<!-- Recent Pages Table -->
<div class="mt-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">Recently Generated Pages</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Page Title</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Template</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentPages as $page)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $page->title }}</td>
                        <td class="px-6 py-4 text-sm text-indigo-600 hover:underline cursor-pointer">{{ $page->slug }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $page->template)) }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($page->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $page->price ? 'TZS ' . number_format($page->price, 2) : 'Free' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $page->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm text-center">
                            <a href="{{ route('pages.edit', $page) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-600">No pages created yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
