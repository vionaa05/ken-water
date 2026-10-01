<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Ken Water</title>
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
<body class="bg-gradient-to-br from-blue-50 to-sky-100 min-h-screen flex items-center justify-center p-4 py-8 font-sans">

    <div class="max-w-md w-full bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl overflow-hidden border border-white/50">
        <div class="p-8">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Daftar Akun Pelanggan</h1>
                <p class="text-gray-500 mt-2 text-sm">Mulai nikmati kemudahan pesan air minum & kumpulkan poinnya!</p>
            </div>

            @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('portal.register.submit') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors bg-white/50">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor HP/WhatsApp *</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors bg-white/50"
                        placeholder="Contoh: 08123456789">
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap *</label>
                    <textarea id="address" name="address" required rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors bg-white/50">{{ old('address') }}</textarea>
                </div>

                <div>
                    <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir (Opsional)</label>
                    <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors bg-white/50">
                    <p class="text-xs text-gray-500 mt-1">Dapatkan promo khusus di hari ulang tahun Anda!</p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                    <input type="password" id="password" name="password" required minlength="6"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors bg-white/50">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors bg-white/50">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 px-4 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
                        Daftar Sekarang
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-sm text-gray-600 border-t border-gray-200 pt-6">
                Sudah punya akun? <a href="{{ route('login') }}" class="text-primary hover:text-primary-dark font-semibold transition-colors">Login di sini</a>
            </div>
        </div>
    </div>

</body>
</html>
