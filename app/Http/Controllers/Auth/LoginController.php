<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role);
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Email atau nomor HP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginField = $request->login;
        $credentials = [];

        // Coba login dengan email atau nomor HP
        if (filter_var($loginField, FILTER_VALIDATE_EMAIL)) {
            $credentials = ['email' => $loginField, 'password' => $request->password];
        } else {
            $credentials = ['phone' => $loginField, 'password' => $request->password];
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return $this->redirectByRole(Auth::user()->role);
        }

        return back()->withErrors([
            'login' => 'Email/No. HP atau password salah.',
        ])->withInput($request->only('login'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda berhasil logout.');
    }

    private function redirectByRole(string $role)
    {
        return match($role) {
            'admin', 'staf' => redirect()->route('admin.dashboard'),
            'pelanggan' => redirect()->route('portal.home'),
            default => redirect()->route('login'),
        };
    }
}
