<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(private PointService $pointService) {}

    /**
     * Login via API (Mendukung Admin & Pelanggan)
     * Menggunakan Email atau Nomor HP atau Customer ID
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Email, No. HP, atau ID Pelanggan wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $loginInput = trim($request->login);

        // Cari user berdasarkan email, no HP, atau customer_id
        $user = User::where(function ($query) use ($loginInput) {
            $query->where('email', $loginInput)
                  ->orWhere('phone', $loginInput)
                  ->orWhere('customer_id', $loginInput);
        })->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial tidak valid. Periksa kembali ID/No HP dan password Anda.',
            ], 401);
        }

        // Hapus token lama jika perlu, atau langsung buat token baru
        $token = $user->createToken('ken_water_mobile_token')->plainTextToken;

        // Ambil data progress loyalitas jika role pelanggan
        $progress = null;
        if ($user->role === 'pelanggan') {
            $progress = $this->pointService->getProgressToNextLevel($user);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'role' => $user->role, // 'admin' atau 'pelanggan'
                'customer_id' => $user->customer_id,
                'loyalty_level' => $user->loyalty_level,
                'points' => (int) $user->points,
                'status' => $user->status,
                'progress' => $progress,
            ],
        ]);
    }

    /**
     * Dapatkan data user saat ini
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $progress = null;
        if ($user->role === 'pelanggan') {
            $progress = $this->pointService->getProgressToNextLevel($user);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'role' => $user->role,
                'customer_id' => $user->customer_id,
                'loyalty_level' => $user->loyalty_level,
                'points' => (int) $user->points,
                'status' => $user->status,
                'progress' => $progress,
            ],
        ]);
    }

    /**
     * Logout & Revoke Token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout.',
        ]);
    }
}
