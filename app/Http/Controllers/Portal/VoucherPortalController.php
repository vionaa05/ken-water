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
            ->where('period_start', '>=', $user->current_period_start)
            ->with('voucher')
            ->latest()
            ->get();
            
        $promos = $user->personalPromos()
            ->where('period_start', '>=', $user->current_period_start)
            ->latest()
            ->get();
            
        return view('portal.vouchers.index', compact('vouchers', 'promos'));
    }
}
