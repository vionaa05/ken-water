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

                <!-- Desktop Navigation Links -->
                @auth
                @if(auth()->user()->role === 'pelanggan')
                <div class="hidden sm:flex items-center space-x-1 lg:space-x-3">
                    <a href="{{ route('portal.home') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('portal.home') ? 'bg-white/20 text-white font-bold' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                        Beranda
                    </a>
                    <a href="{{ route('portal.order.create') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('portal.order.*') ? 'bg-white/20 text-white font-bold' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                        Pesan Galon
                    </a>
                    <a href="{{ route('portal.orders.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('portal.orders.*') ? 'bg-white/20 text-white font-bold' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                        Pesanan Saya
                    </a>
                    <a href="{{ route('portal.rewards.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('portal.rewards.*') ? 'bg-white/20 text-white font-bold' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                        Reward
                    </a>
                    <a href="{{ route('portal.vouchers.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('portal.vouchers.*') ? 'bg-white/20 text-white font-bold' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                        Voucher
                    </a>
                    <a href="{{ route('portal.complaints.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('portal.complaints.*') ? 'bg-white/20 text-white font-bold' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                        Keluhan
                    </a>
                </div>
                @endif
                @endauth

                <!-- Right Side (Notifications & Profile) -->
                @auth
                <div class="flex items-center gap-3 sm:gap-4">
                    <a href="{{ route('portal.notifications.index') }}" class="relative text-white hover:text-blue-100 p-1.5 rounded-full hover:bg-white/10 transition-colors" title="Notifikasi">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        @if(auth()->user()->notifications()->where('is_read', false)->count() > 0)
                        <span class="absolute 0 top-0.5 right-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center ring-2 ring-primary">
                            {{ auth()->user()->notifications()->where('is_read', false)->count() }}
                        </span>
                        @endif
                    </a>

                    <!-- Profile Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-white rounded-full transition-transform active:scale-95">
                            <div class="w-9 h-9 rounded-full bg-white/20 border border-white/30 flex items-center justify-center text-white font-bold shadow-sm">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl py-2 ring-1 ring-black/5 focus:outline-none z-50">
                            <div class="px-4 py-2.5 border-b border-gray-100">
                                <p class="text-sm font-bold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-primary font-mono font-medium">{{ auth()->user()->customer_id }}</p>
                                <div class="mt-1 flex items-center gap-1.5">
                                    <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                                    <span class="text-[11px] text-gray-500 capitalize">{{ auth()->user()->loyalty_level ?? 'Bronze' }} Member • {{ auth()->user()->points ?? 0 }} Pts</span>
                                </div>
                            </div>
                            <a href="{{ route('portal.profile') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-sky-50 hover:text-primary transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Profil Saya
                            </a>
                            <a href="{{ route('portal.orders.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-sky-50 hover:text-primary transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                Riwayat Pesanan
                            </a>
                            <a href="{{ route('portal.transactions') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-sky-50 hover:text-primary transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Riwayat Transaksi
                            </a>
                            <a href="{{ route('portal.member-card') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-sky-50 hover:text-primary transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                Kartu Member
                            </a>
                            <a href="{{ route('portal.vouchers.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-sky-50 hover:text-primary transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                Voucher & Promo
                            </a>
                            <a href="{{ route('portal.complaints.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-sky-50 hover:text-primary transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                Keluhan & Bantuan
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow max-w-md mx-auto w-full px-4 sm:max-w-4xl sm:px-6 lg:max-w-7xl lg:px-8 py-6 pb-28 sm:pb-12">
        
        @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-xl flex items-center gap-3 shadow-xs" role="alert">
            <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
        @endif

        @if (session('error'))
        <div class="mb-4 bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-xl flex items-center gap-3 shadow-xs" role="alert">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
        @endif
        
        @if (session('warning'))
        <div class="mb-4 bg-amber-50 border border-amber-300 text-amber-800 px-4 py-3 rounded-xl flex items-center gap-3 shadow-xs" role="alert">
            <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span class="text-sm font-medium">{{ session('warning') }}</span>
        </div>
        @endif

        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation (Portal) -->
    @auth
    @if(auth()->user()->role === 'pelanggan')
    <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-gray-200/80 sm:hidden z-50 pb-safe shadow-[0_-4px_12px_rgba(0,0,0,0.05)]">
        <div class="grid grid-cols-5 items-center h-16 px-1">
            <a href="{{ route('portal.home') }}" class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('portal.home') ? 'text-primary font-bold' : 'text-gray-400 hover:text-gray-600 font-medium' }} transition-colors">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[10px]">Beranda</span>
            </a>
            <a href="{{ route('portal.order.create') }}" class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('portal.order.create') ? 'text-primary font-bold' : 'text-gray-400 hover:text-gray-600 font-medium' }} transition-colors">
                <div class="w-6 h-6 flex items-center justify-center mb-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <span class="text-[10px]">Pesan</span>
            </a>
            <a href="{{ route('portal.orders.index') }}" class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('portal.orders.*') ? 'text-primary font-bold' : 'text-gray-400 hover:text-gray-600 font-medium' }} transition-colors">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span class="text-[10px]">Pesanan</span>
            </a>
            <a href="{{ route('portal.rewards.index') }}" class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('portal.rewards.*') ? 'text-primary font-bold' : 'text-gray-400 hover:text-gray-600 font-medium' }} transition-colors">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                <span class="text-[10px]">Reward</span>
            </a>
            <a href="{{ route('portal.complaints.index') }}" class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('portal.complaints.*') ? 'text-primary font-bold' : 'text-gray-400 hover:text-gray-600 font-medium' }} transition-colors">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span class="text-[10px]">Keluhan</span>
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
