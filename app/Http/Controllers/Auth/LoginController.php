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

        $loginField = trim($request->login);

        // Coba login dengan email
        if (filter_var($loginField, FILTER_VALIDATE_EMAIL)) {
            if (Auth::attempt(['email' => $loginField, 'password' => $request->password], $request->boolean('remember'))) {
                $request->session()->regenerate();
                return $this->redirectByRole(Auth::user()->role);
            }
        } else {
            // Normalisasi variasi nomor HP (misal: 0812... vs 62812... vs +62812...)
            $cleanPhone = preg_replace('/[^0-9]/', '', $loginField);
            $variants = array_unique(array_filter([
                $loginField,
                $cleanPhone,
                preg_replace('/^62/', '0', $cleanPhone),
                preg_replace('/^0/', '62', $cleanPhone),
            ]));

            foreach ($variants as $phoneCandidate) {
                if (Auth::attempt(['phone' => $phoneCandidate, 'password' => $request->password], $request->boolean('remember'))) {
                    $request->session()->regenerate();
                    return $this->redirectByRole(Auth::user()->role);
                }
            }
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
            'admin' => redirect()->route('admin.dashboard'),
            'pelanggan' => redirect()->route('portal.home'),
            default => redirect()->route('login'),
        };
    }
}
