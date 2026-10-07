<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - Ken Water')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0ea5e9',
                        'primary-dark': '#0284c7',
                        secondary: '#1e293b', // Slate 800
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
                @apply inline-flex justify-center items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-all shadow-sm;
            }
            .btn-secondary {
                @apply inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-all shadow-sm;
            }
            .card {
                @apply bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden;
            }
            .input-field {
                @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 sm:text-sm;
            }
        }
        
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased flex h-screen overflow-hidden" x-data="{ sidebarOpen: true }">

    <!-- Sidebar -->
    <aside :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}" class="fixed inset-y-0 left-0 z-50 w-64 bg-secondary text-white transition-transform duration-300 ease-in-out md:relative md:translate-x-0 h-full flex flex-col">
        <div class="flex items-center justify-between h-16 px-4 bg-gray-900 border-b border-gray-800">
            <span class="text-lg font-bold flex items-center gap-2">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                Ken Water BO
            </span>
            <button @click="sidebarOpen = false" class="md:hidden text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto py-4">
            <nav class="px-2 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>
                
                <a href="{{ route('admin.customers.index') }}" class="flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.customers.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Pelanggan
                </a>

                <a href="{{ route('admin.orders.index') }}" class="flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.orders.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Pesanan Masuk
                </a>

                <a href="{{ route('admin.complaints.index') }}" class="flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.complaints.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Keluhan
                </a>

                <div x-data="{ expanded: {{ request()->routeIs('admin.loyalty.*') || request()->routeIs('admin.segmentation.*') || request()->routeIs('admin.campaigns.*') ? 'true' : 'false' }} }" class="mt-4 pt-4 border-t border-gray-800">
                    <button @click="expanded = !expanded" class="w-full flex items-center justify-between px-2 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-gray-700 hover:text-white">
                        <div class="flex items-center">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                            CRM & Loyalty
                        </div>
                        <svg :class="{'rotate-180': expanded}" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div x-show="expanded" class="pl-10 pr-2 py-2 space-y-1" x-collapse>
                        <a href="{{ route('admin.segmentation.index') }}" class="block px-2 py-2 text-sm rounded-md {{ request()->routeIs('admin.segmentation.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}">Segmentasi</a>
                        <a href="{{ route('admin.loyalty.rewards.index') }}" class="block px-2 py-2 text-sm rounded-md {{ request()->routeIs('admin.loyalty.rewards.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}">Katalog Reward</a>
                        <a href="{{ route('admin.loyalty.vouchers.index') }}" class="block px-2 py-2 text-sm rounded-md {{ request()->routeIs('admin.loyalty.vouchers.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}">Voucher</a>
                        <a href="{{ route('admin.loyalty.promos.index') }}" class="block px-2 py-2 text-sm rounded-md {{ request()->routeIs('admin.loyalty.promos.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}">Promo Personal</a>
                        <a href="{{ route('admin.loyalty.redemptions.index') }}" class="block px-2 py-2 text-sm rounded-md {{ request()->routeIs('admin.loyalty.redemptions.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}">Validasi Penukaran</a>
                        <a href="{{ route('admin.campaigns.index') }}" class="block px-2 py-2 text-sm rounded-md {{ request()->routeIs('admin.campaigns.*') ? 'text-white bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-700' }}">Kampanye WA</a>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-800">
                    <a href="{{ route('admin.settings.index') }}" class="flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Pengaturan
                    </a>
                </div>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Navbar -->
        <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
            <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 md:hidden focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="hidden md:block"></div> <!-- Spacer -->
                
                <div class="flex items-center gap-4">
                    <div class="flex flex-col items-end mr-2">
                        <span class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</span>
                        <span class="text-xs text-gray-500">{{ ucfirst(auth()->user()->role) }}</span>
                    </div>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium border border-red-200 rounded-md px-3 py-1 bg-red-50 hover:bg-red-100 transition-colors">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main View -->
        <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                @if (session('success'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
                @endif

                @if (session('error'))
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
                @endif
                
                @if ($errors->any())
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>
