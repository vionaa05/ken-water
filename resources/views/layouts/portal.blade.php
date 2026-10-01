<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ken Water - CRM')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Alpine.js (CDN fallback if npm fails) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind CSS (CDN fallback if build fails) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0ea5e9',
                        'primary-dark': '#0284c7',
                        secondary: '#0f172a',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style type="text/tailwindcss">
        @layer components {
            .btn-primary {
                @apply inline-flex justify-center items-center px-4 py-2 bg-primary border border-transparent rounded-lg font-semibold text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-all duration-200 shadow-sm;
            }
            .card {
                @apply bg-white rounded-xl shadow-sm border border-gray-100 p-4;
            }
            .glass {
                @apply bg-white/80 backdrop-blur-lg border border-white/20 shadow-lg;
            }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col">

    <!-- Mobile-first Navbar -->
    <nav class="bg-primary text-white shadow-md sticky top-0 z-50">
        <div class="max-w-md mx-auto px-4 sm:max-w-4xl sm:px-6 lg:max-w-7xl lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('portal.home') }}" class="font-bold text-xl flex items-center gap-2">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        Ken Water
                    </a>
                </div>

                <!-- Right Side (Notifications & Profile) -->
                @auth
                <div class="flex items-center gap-4">
                    <a href="{{ route('portal.notifications.index') }}" class="relative text-white hover:text-blue-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        @if(auth()->user()->notifications()->where('is_read', false)->count() > 0)
                        <span class="absolute -top-1 -right-1 bg-red-500 text-xs rounded-full w-4 h-4 flex items-center justify-center">
                            {{ auth()->user()->notifications()->where('is_read', false)->count() }}
                        </span>
                        @endif
                    </a>

                    <!-- Profile Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-white rounded-full">
                            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white font-bold">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 focus:outline-none">
                            <div class="px-4 py-2 border-b">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->customer_id }}</p>
                            </div>
                            <a href="{{ route('portal.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil Saya</a>
                            <a href="{{ route('portal.member-card') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Kartu Member</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow max-w-md mx-auto w-full px-4 sm:max-w-4xl sm:px-6 lg:max-w-7xl lg:px-8 py-6 pb-24">
        
        @if (session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        @if (session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
        @endif
        
        @if (session('warning'))
        <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('warning') }}</span>
        </div>
        @endif

        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation (Portal) -->
    @auth
    @if(auth()->user()->role === 'pelanggan')
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 sm:hidden z-50 pb-safe">
        <div class="flex justify-around items-center h-16">
            <a href="{{ route('portal.home') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('portal.home') ? 'text-primary' : 'text-gray-500' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[10px] font-medium">Beranda</span>
            </a>
            <a href="{{ route('portal.order.create') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('portal.order.*') ? 'text-primary' : 'text-gray-500' }}">
                <div class="bg-primary text-white p-3 rounded-full -mt-6 shadow-lg shadow-blue-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <span class="text-[10px] font-medium mt-1">Pesan</span>
            </a>
            <a href="{{ route('portal.rewards.index') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('portal.rewards.*') ? 'text-primary' : 'text-gray-500' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                <span class="text-[10px] font-medium">Reward</span>
            </a>
        </div>
    </div>
    @endif
    @endauth

    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
    </style>
</body>
</html>
