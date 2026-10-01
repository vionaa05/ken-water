<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Complaint;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // =====================
        // KARTU RINGKASAN
        // =====================
        $totalCustomers = User::customers()->count();
        $activeCustomers = User::customers()->active()->count();
        $inactiveCustomers = User::customers()->inactive()->count();
        $loyalCustomers = User::loyalCustomers()->count();

        // Pendapatan total (semua transaksi, termasuk sebelum reaktivasi)
        $totalRevenue = Order::where('status', 'selesai')->sum('total_price');

        // Pesanan masuk yang belum diproses
        $pendingOrders = Order::where('status', 'dipesan')
            ->where('source', 'online')
            ->with('user')
            ->latest('order_date')
            ->take(10)
            ->get();

        $pendingOrdersCount = Order::where('status', 'dipesan')
            ->where('source', 'online')
            ->count();

        // Keluhan belum selesai
        $openComplaintsCount = Complaint::whereIn('status', ['baru', 'diproses'])->count();

        // =====================
        // GRAFIK TRANSAKSI PER BULAN (12 bulan terakhir)
        // =====================
        $monthlyData = Order::where('status', 'selesai')
            ->where('order_date', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw('YEAR(order_date) as year'),
                DB::raw('MONTH(order_date) as month'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(gallon_qty) as total_gallons')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $chartLabels = [];
        $chartRevenue = [];
        $chartOrders = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->locale('id')->isoFormat('MMM YY');
            $chartLabels[] = $monthName;

            $found = $monthlyData->first(function ($item) use ($date) {
                return $item->year == $date->year && $item->month == $date->month;
            });

            $chartRevenue[] = $found ? (float) $found->revenue : 0;
            $chartOrders[] = $found ? (int) $found->order_count : 0;
        }

        // =====================
        // TOP 5 PELANGGAN (berdasarkan total belanja periode saat ini)
        // =====================
        $topCustomers = User::customers()
            ->withCount(['completedOrders as order_count'])
            ->withSum('completedOrders as total_spend', 'total_price')
            ->orderByDesc('total_spend')
            ->take(5)
            ->get();

        // =====================
        // KELUHAN TERBARU YANG BELUM SELESAI
        // =====================
        $openComplaints = Complaint::whereIn('status', ['baru', 'diproses'])
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalCustomers', 'activeCustomers', 'inactiveCustomers', 'loyalCustomers',
            'totalRevenue', 'pendingOrders', 'pendingOrdersCount', 'openComplaintsCount',
            'chartLabels', 'chartRevenue', 'chartOrders',
            'topCustomers', 'openComplaints'
        ));
    }
}
