<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Cek apakah user memiliki role yang diizinkan.
     * Semua pengecekan dilakukan di sisi server.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!in_array($user->role, $roles)) {
            // Redirect ke halaman yang sesuai dengan role user
            if ($user->role === 'pelanggan') {
                abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
