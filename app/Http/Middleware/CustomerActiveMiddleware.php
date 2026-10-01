<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerActiveMiddleware
{
    /**
     * Pastikan pelanggan berstatus aktif sebelum melakukan aksi tertentu
     * (pesan, tukar poin, pakai voucher)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->role === 'pelanggan' && $user->status === 'tidak_aktif') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akun Anda tidak aktif. Silakan reaktivasi terlebih dahulu.',
                    'redirect' => route('portal.reactivate'),
                ], 403);
            }

            return redirect()->route('portal.reactivate')
                ->with('warning', 'Akun Anda tidak aktif. Silakan reaktivasi untuk melanjutkan.');
        }

        return $next($request);
    }
}
