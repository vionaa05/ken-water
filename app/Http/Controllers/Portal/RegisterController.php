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
            'phone.required' => 'Nomor HP/WhatsApp wajib diisi.',
            'phone.unique' => 'Nomor HP sudah terdaftar. Silakan login.',
            'address.required' => 'Alamat wajib diisi.',
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

        Auth::login($user);

        return redirect()->route('portal.home')
            ->with('success', 'Selamat datang di Ken Water! Akun Anda berhasil dibuat.');
    }
}
