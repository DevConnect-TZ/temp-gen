@extends('layouts.app')

@section('title', 'Templates')
@section('page_title', 'Templates')

@section('content')
<div>
    <p class="text-gray-600 text-sm mb-8">Select a template to create your landing page</p>

    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @foreach($templates as $template)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 group">
            <!-- Cover Image -->
            <div class="relative h-64 overflow-hidden bg-gray-200">
                <img src="{{ $template['cover'] }}" alt="{{ $template['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition"></div>
            </div>

            <!-- Template Info -->
            <div class="p-8">
                <!-- Template Name -->
                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ $template['name'] }}</h3>
                    <div class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-full">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Built-in Template
                    </div>
                </div>

                <!-- Template ID -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-xs text-gray-600 mb-1">Template File</p>
                    <p class="text-sm font-mono font-medium text-gray-900">{{ $template['name'] }}.html</p>
                </div>

                <!-- Use Template Button -->
                <a href="/pages/create?template={{ $template['name'] }}" class="w-full inline-flex items-center justify-center px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition duration-200 shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Use Template
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
