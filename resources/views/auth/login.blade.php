<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ken Water</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0ea5e9',
                        'primary-dark': '#0284c7',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-sky-100 min-h-screen flex items-center justify-center p-4 font-sans">

    <div class="max-w-md w-full bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl overflow-hidden border border-white/50">
        <div class="p-8">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-tr from-sky-600 via-sky-500 to-cyan-400 text-white mb-4 shadow-xl shadow-sky-500/30">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Ken Water</h1>
                <p class="text-gray-500 mt-1 text-xs">Depot Air Minum Berkualitas</p>
            </div>

            @if (session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded text-sm">
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="login" class="block text-sm font-medium text-gray-700 mb-1">Nomor HP atau WhatsApp</label>
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors bg-white/50"
                        placeholder="Masukkan Nomor HP atau WhatsApp">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors bg-white/50"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <span class="ml-2 text-sm text-gray-600">Ingat Saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 px-4 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
                    Masuk
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-gray-600 border-t border-gray-200 pt-6">
                Belum punya akun pelanggan? <br>
                <a href="{{ route('portal.register') }}" class="text-primary hover:text-primary-dark font-semibold transition-colors mt-2 inline-block">Daftar Sekarang</a>
            </div>
        </div>
    </div>

</body>
</html>
