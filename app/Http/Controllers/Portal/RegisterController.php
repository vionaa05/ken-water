<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RegisterController extends Controller
{
    public function showForm()
    {
        if (Auth::check()) {
            return redirect()->route('portal.home');
        }
        return view('portal.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:users,phone',
            'address' => 'required|string|max:255',
            'birth_date' => 'nullable|date|before:today',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 100 karakter.',
            'phone.required' => 'Nomor HP/WhatsApp wajib diisi.',
            'phone.max' => 'Nomor HP maksimal 20 karakter.',
            'phone.unique' => 'Nomor HP sudah terdaftar. Silakan login.',
            'address.required' => 'Alamat wajib diisi.',
            'address.max' => 'Alamat maksimal 255 karakter.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $now = Carbon::now();

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'birth_date' => $request->birth_date,
            'password' => Hash::make($request->password),
            'role' => 'pelanggan',
            'customer_id' => User::generateCustomerId(),
            'status' => 'aktif',
            'loyalty_level' => 'bronze',
            'points' => 0,
            'current_period_start' => $now,
            'registered_at' => $now,
        ]);

        return redirect()->route('login')
            ->with('success', 'Akun berhasil dibuat. Silakan login untuk melanjutkan.');
    }
}
