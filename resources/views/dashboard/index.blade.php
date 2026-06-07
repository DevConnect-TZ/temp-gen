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

@if(!empty($accountRevenue))
<!-- PesaLink Account Revenue Cards -->
<div class="mb-8">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">PesaLink Account Revenue</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($accountRevenue as $acc)
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4" style="border-left-color: {{ $acc['borderColor'] }}">
            <p class="text-xs text-gray-500 font-medium truncate">{{ $acc['name'] }}</p>
            <p class="text-lg font-bold text-gray-900 mt-1">TZS {{ number_format($acc['total'], 0) }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Chart Section -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Revenue Trend</h2>
                    <p class="text-sm text-gray-500 mt-1">Per-account revenue over the last 14 days</p>
                </div>
                <div class="text-right">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Current Revenue</p>
                    <p class="text-sm font-semibold text-gray-900">TZS {{ number_format($totalRevenue, 0) }}</p>
                </div>
            </div>
            <div class="h-80">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Stats Sidebar -->
    <div class="space-y-6">
        <!-- Avg Order Value -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-gray-600 text-sm font-medium mb-2">PesaLink Accounts</p>
            <p class="text-2xl font-bold text-gray-900">{{ count($accountRevenue) }}</p>
            <p class="text-gray-600 text-xs font-medium mt-3">Active sub-accounts</p>
        </div>

        <!-- Total PesaLink Revenue -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-gray-600 text-sm font-medium mb-2">PesaLink Total</p>
            <p class="text-2xl font-bold text-gray-900">TZS {{ number_format(collect($accountRevenue)->sum('total'), 0) }}</p>
            <p class="text-gray-600 text-xs font-medium mt-3">From all accounts combined</p>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-gray-900 text-sm font-bold mb-4">Quick Actions</p>
            <a href="/pages/create" class="block w-full text-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition mb-2">
                New Page
            </a>
            <a href="/pesalink-accounts" class="block w-full text-center px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-medium transition">
                PesaLink Accounts
            </a>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    (() => {
        const canvas = document.getElementById('revenueTrendChart');

        if (!canvas || typeof Chart === 'undefined') {
            return;
        }

        const labels = @json($revenueTrendLabels);
        const accountDatasets = @json($accountDatasets);

        new Chart(canvas, {
            type: 'line',
            data: {
                labels,
                datasets: accountDatasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            color: '#6b7280',
                            font: { size: 12 },
                        },
                    },
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 12,
                        callbacks: {
                            label(context) {
                                return context.dataset.label + ': TZS ' + Number(context.parsed.y || 0).toLocaleString('en-US');
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#6b7280' },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(148, 163, 184, 0.18)' },
                        ticks: {
                            color: '#6b7280',
                            callback(value) {
                                return 'TZS ' + Number(value).toLocaleString('en-US');
                            },
                        },
                    },
                },
            },
        });
    })();
</script>
@endsection
