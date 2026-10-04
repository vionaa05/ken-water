<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VoucherPortalController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $vouchers = $user->userVouchers()
            ->when($user->current_period_start, fn($q, $start) => $q->where('period_start', '>=', $start))
            ->with('voucher')
            ->latest()
            ->get();
            
        $promos = $user->personalPromos()
            ->when($user->current_period_start, fn($q, $start) => $q->where('period_start', '>=', $start))
            ->latest()
            ->get();
            
        return view('portal.vouchers.index', compact('vouchers', 'promos'));
    }
}
