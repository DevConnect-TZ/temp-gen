<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <aside class="hidden lg:flex lg:w-64 bg-gray-900 text-gray-100 flex-col shadow-lg">
            <!-- Logo -->
            <div class="px-6 py-8 border-b border-gray-800">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-lg">LG</span>
                    </div>
                    <h1 class="text-xl font-bold text-white">LandingHub</h1>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <a href="/dashboard" wire:navigate class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-colors {{ request()->is('dashboard') ? 'text-gray-100 bg-indigo-600' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4m0 0l4 4m-4-4v4"></path>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="/pages" wire:navigate class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->is('pages') || request()->is('pages/*') ? 'text-gray-100 bg-indigo-600' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span>Manage Pages</span>
                </a>

                <a href="/pages/create" wire:navigate class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->is('pages/create') ? 'text-gray-100 bg-indigo-600' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Create New Page</span>
                </a>

                <a href="/templates" wire:navigate class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->is('templates') ? 'text-gray-100 bg-indigo-600' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.5a2 2 0 00-1 3.75A2 2 0 0010 19h-3z"></path>
                    </svg>
                    <span>Templates</span>
                </a>

                <a href="/payment-gateways" wire:navigate class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->is('payment-gateways') || request()->is('payment-gateways/*') ? 'text-gray-100 bg-indigo-600' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Payment Gateways</span>
                </a>

                <a href="/settings" wire:navigate class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->is('settings') ? 'text-gray-100 bg-indigo-600' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Settings</span>
                </a>
            </nav>

            <!-- Logout Button -->
            <div class="px-4 py-6 border-t border-gray-800">
                <form method="POST" action="/logout" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 text-gray-400 hover:text-gray-100 hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Mobile Sidebar Overlay -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

        <!-- Mobile Sidebar -->
        <aside id="mobileSidebar" class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-gray-100 z-50 transform -translate-x-full transition-transform duration-300 lg:hidden flex flex-col">
            <div class="px-6 py-8 border-b border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-lg">LG</span>
                        </div>
                        <h1 class="text-xl font-bold text-white">LandingHub</h1>
                    </div>
                    <button id="closeSidebar" class="text-gray-400 hover:text-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <a href="/dashboard" wire:navigate onclick="closeMobileSidebar()" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->is('dashboard') ? 'text-gray-100 bg-indigo-600 font-medium' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4m0 0l4 4m-4-4v4"></path>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="/pages" wire:navigate onclick="closeMobileSidebar()" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->is('pages') || request()->is('pages/*') ? 'text-gray-100 bg-indigo-600 font-medium' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span>Manage Pages</span>
                </a>
                <a href="/pages/create" wire:navigate onclick="closeMobileSidebar()" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->is('pages/create') ? 'text-gray-100 bg-indigo-600 font-medium' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Create New Page</span>
                </a>
                <a href="/templates" wire:navigate onclick="closeMobileSidebar()" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->is('templates') ? 'text-gray-100 bg-indigo-600 font-medium' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.5a2 2 0 00-1 3.75A2 2 0 0010 19h-3z"></path>
                    </svg>
                    <span>Templates</span>
                </a>
                <a href="/payment-gateways" wire:navigate onclick="closeMobileSidebar()" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->is('payment-gateways') || request()->is('payment-gateways/*') ? 'text-gray-100 bg-indigo-600 font-medium' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Payment Gateways</span>
                </a>
                <a href="/settings" wire:navigate onclick="closeMobileSidebar()" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors {{ request()->is('settings') ? 'text-gray-100 bg-indigo-600 font-medium' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Settings</span>
                </a>
            </nav>

            <div class="px-4 py-6 border-t border-gray-800">
                <form method="POST" action="/logout" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 text-gray-400 hover:text-gray-100 hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-30">
                <div class="px-3 sm:px-4 lg:px-8 py-3 sm:py-4 flex items-center justify-between gap-2 sm:gap-4">
                    <!-- Hamburger Menu Button (Mobile Only) -->
                    <button id="openSidebar" aria-controls="mobileSidebar" aria-expanded="false" class="block lg:hidden z-50 inline-flex items-center justify-center p-2 rounded-lg text-gray-700 bg-white/0 hover:bg-white/10 active:bg-white/20 transition-colors flex-shrink-0" title="Open menu">
                        <span class="sr-only">Open main menu</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Page Title -->
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-900 truncate">@yield('page_title', 'Dashboard')</h2>
                    </div>

                    <!-- Right Header Actions -->
                    <div class="flex items-center gap-2 sm:gap-4 lg:gap-6 flex-shrink-0">
                        <!-- Notification Bell -->
                        <button class="text-gray-600 hover:text-gray-900 transition-colors p-2 rounded-lg hover:bg-gray-100" title="Notifications">
                            <svg class="w-5 sm:w-6 h-5 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </button>

                        <!-- Admin Profile -->
                        <div class="hidden sm:flex items-center gap-2 sm:gap-3 pl-2 sm:pl-4 lg:pl-6 border-l border-gray-200">
                            <div class="w-8 sm:w-10 h-8 sm:h-10 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-indigo-600 font-semibold text-xs sm:text-sm">AD</span>
                            </div>
                            <div class="hidden sm:block">
                                <p class="text-xs sm:text-sm font-medium text-gray-900 whitespace-nowrap">Admin User</p>
                                <p class="text-xs text-gray-600 whitespace-nowrap">Administrator</p>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="flex-1 overflow-auto bg-gray-50">
                <div class="p-3 sm:p-4 lg:p-8">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
        // Initialize sidebar controls and make them resilient to Livewire navigation
        function initializeSidebarControls() {
            const openSidebarBtn = document.getElementById('openSidebar');
            const closeSidebarBtn = document.getElementById('closeSidebar');
            const mobileSidebar = document.getElementById('mobileSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            function closeMobileSidebar() {
                if (mobileSidebar) mobileSidebar.classList.add('-translate-x-full');
                if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
                if (openSidebarBtn) openSidebarBtn.setAttribute('aria-expanded', 'false');
            }

            function openMobileSidebar() {
                if (mobileSidebar) mobileSidebar.classList.remove('-translate-x-full');
                if (sidebarOverlay) sidebarOverlay.classList.remove('hidden');
                if (openSidebarBtn) openSidebarBtn.setAttribute('aria-expanded', 'true');
            }

            // Fresh event listeners using onclick
            if (openSidebarBtn) {
                openSidebarBtn.onclick = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    openMobileSidebar();
                };
            }

            if (closeSidebarBtn) {
                closeSidebarBtn.onclick = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    closeMobileSidebar();
                };
            }

            if (sidebarOverlay) {
                sidebarOverlay.onclick = closeMobileSidebar;
            }

            // Expose to window for sidebar links
            window.closeMobileSidebar = closeMobileSidebar;
        }

        // Initialize on first load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeSidebarControls);
        } else {
            initializeSidebarControls();
        }

        // Re-initialize after every Livewire navigation
        if (typeof window !== 'undefined' && window.Livewire) {
            window.Livewire.hook('navigate.end', () => {
                setTimeout(initializeSidebarControls, 100);
            });
        }

        // Global escape key handler
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const mobileSidebar = document.getElementById('mobileSidebar');
                if (mobileSidebar && !mobileSidebar.classList.contains('-translate-x-full')) {
                    const openBtn = document.getElementById('openSidebar');
                    if (openBtn) openBtn.click();
                }
            }
        });
    </script>
    @livewireScripts
</body>
</html>