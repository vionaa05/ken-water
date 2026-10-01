<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SegmentationController extends Controller
{
    public function index()
    {
        $customers = User::customers()
            ->with('completedOrders')
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'customer_id' => $customer->customer_id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'status' => $customer->status,
                    'loyalty_level' => $customer->loyalty_level,
                    'points' => $customer->points,
                    'segment' => $customer->segment,
                    'customer_value' => $customer->customer_value,
                    'order_count' => $customer->order_count_current_period,
                    'total_spend' => $customer->total_spend_current_period,
                    'days_since_last_order' => $customer->getDaysSinceLastOrder(),
                    'registered_at' => $customer->registered_at,
                ];
            });

        // Hitung distribusi segmen
        $segmentCounts = $customers->groupBy('segment')
            ->map(fn($group) => $group->count());

        $levelCounts = $customers->groupBy('loyalty_level')
            ->map(fn($group) => $group->count());

        $valueCounts = $customers->groupBy('customer_value')
            ->map(fn($group) => $group->count());

        // Data untuk chart
        $chartSegmentData = [
            ['label' => 'Baru', 'count' => $segmentCounts->get('Baru', 0), 'color' => '#3b82f6'],
            ['label' => 'Reguler', 'count' => $segmentCounts->get('Reguler', 0), 'color' => '#10b981'],
            ['label' => 'Sering Beli', 'count' => $segmentCounts->get('Sering Beli', 0), 'color' => '#f59e0b'],
            ['label' => 'Hampir Hilang', 'count' => $segmentCounts->get('Hampir Hilang', 0), 'color' => '#ef4444'],
        ];

        $chartLevelData = [
            ['label' => 'Bronze', 'count' => $levelCounts->get('bronze', 0), 'color' => '#CD7F32'],
            ['label' => 'Silver', 'count' => $levelCounts->get('silver', 0), 'color' => '#C0C0C0'],
            ['label' => 'Gold', 'count' => $levelCounts->get('gold', 0), 'color' => '#FFD700'],
        ];

        return view('admin.segmentation.index', compact(
            'customers', 'chartSegmentData', 'chartLevelData',
            'segmentCounts', 'levelCounts', 'valueCounts'
        ));
    }
}
