<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->get("month", now()->month);
        $year  = (int) $request->get("year",  now()->year);

        // Klamping nilai agar valid
        $month = max(1, min(12, $month));

        // Periode aktif
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd   = $periodStart->copy()->endOfMonth();
        $periodLabel = $periodStart->locale("id")->isoFormat("MMMM YYYY");

        // Daftar 12 bulan
        $months = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // Daftar tahun dari tahun pertama transaksi s/d tahun berjalan (minimal 3 tahun terakhir)
        $earliestOrderYear = (int)(Order::whereNotNull('completed_at')->min(DB::raw('YEAR(completed_at)')) 
            ?: Order::min(DB::raw('YEAR(order_date)')) 
            ?: (now()->year - 2));
        $startYear = min($earliestOrderYear, now()->year - 2);

        $years = [];
        for ($y = now()->year; $y >= $startYear; $y--) {
            $years[] = $y;
        }

        // Query semua pesanan selesai di periode ini
        $orders = Order::with("user")
            ->where("status", "selesai")
            ->whereBetween("completed_at", [$periodStart, $periodEnd])
            ->orderBy("completed_at")
            ->get();

        // Hitung ringkasan
        $totalOrders  = $orders->count();
        $totalGallons = $orders->sum("gallon_qty");
        $totalRevenue = $orders->sum("total_price");
        $onlineCount  = $orders->where("source", "online")->count();
        $directCount  = $orders->where("source", "langsung")->count();
        $avgOrder     = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Jika request print, kembalikan view khusus dokumen cetak (tabel bersih tanpa website shell)
        if ($request->boolean('print') || $request->get('print') === '1') {
            return view("admin.reports.print", compact(
                "month", "year", "periodLabel", "months", "years",
                "orders", "totalOrders", "totalGallons", "totalRevenue",
                "onlineCount", "directCount", "avgOrder"
            ));
        }

        return view("admin.reports.index", compact(
            "month", "year", "periodLabel", "months", "years",
            "orders", "totalOrders", "totalGallons", "totalRevenue",
            "onlineCount", "directCount", "avgOrder"
        ));
    }
}