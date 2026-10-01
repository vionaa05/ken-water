<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Voucher;
use App\Models\User;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with(['creator', 'voucher'])->latest()->paginate(15);
        return view('admin.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $vouchers = Voucher::where('is_active', true)->get();
        return view('admin.campaigns.create', compact('vouchers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:promo_baru,promo_loyal,ulang_tahun,ajakan_kembali',
            'target_segment' => 'required|array',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'message_template' => 'required|string',
            'voucher_id' => 'nullable|exists:vouchers,id',
        ]);

        // Hitung perkiraan target
        $targetCount = $this->calculateTargetCount($request->target_segment, $request->type);

        $campaign = Campaign::create([
            'name' => $request->name,
            'type' => $request->type,
            'target_segment' => $request->target_segment,
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'message_template' => $request->message_template,
            'voucher_id' => $request->voucher_id,
            'created_by' => auth()->id(),
            'target_count' => $targetCount,
        ]);

        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Kampanye berhasil dibuat. Target: ' . $targetCount . ' pelanggan.');
    }

    public function show(Campaign $campaign)
    {
        $campaign->load(['creator', 'voucher']);
        $targetCustomers = $this->getTargetCustomers($campaign->target_segment, $campaign->type);
        
        return view('admin.campaigns.show', compact('campaign', 'targetCustomers'));
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Kampanye berhasil dihapus.');
    }

    private function calculateTargetCount(array $segments, string $type): int
    {
        return $this->getTargetCustomers($segments, $type)->count();
    }

    private function getTargetCustomers(array $segments, string $type)
    {
        $query = User::customers();

        if (in_array('semua', $segments)) {
            // Biarkan query apa adanya
        } elseif (in_array('tidak_aktif', $segments) || $type === 'ajakan_kembali') {
            $query->inactive();
        } else {
            $query->active();
            // Filter segmen (logic sederhana)
            if (in_array('loyal', $segments) || $type === 'promo_loyal') {
                $query->whereIn('loyalty_level', ['silver', 'gold']);
            }
            if ($type === 'ulang_tahun') {
                $query->whereMonth('birth_date', now()->month);
            }
        }

        return $query->get();
    }
}
