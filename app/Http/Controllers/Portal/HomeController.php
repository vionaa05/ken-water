<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\PointService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(private PointService $pointService) {}

    public function index()
    {
        $user = auth()->user();
        
        $activeVouchersCount = $user->userVouchers()->where('status', 'aktif')->count();
        $personalPromosCount = $user->personalPromos()->where('is_used', false)->where('is_expired', false)->count();
        
        $progress = $this->pointService->getProgressToNextLevel($user);

        // Cek jika akun inactive
        if ($user->status === 'tidak_aktif') {
            return view('portal.home-inactive', compact('user', 'progress'));
        }

        return view('portal.home', compact('user', 'activeVouchersCount', 'personalPromosCount', 'progress'));
    }
}
