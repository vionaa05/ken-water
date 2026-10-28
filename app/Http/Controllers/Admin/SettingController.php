<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'inactive_days' => 'required|integer|min:1',
            'points_per_gallon' => 'required|integer|min:0',
            'price_per_gallon' => 'required|numeric|min:0',
            'silver_threshold' => 'required|integer|min:1',
            'gold_threshold' => 'required|integer|gt:silver_threshold',
            'depot_whatsapp' => 'nullable|string|max:30',
        ]);

        $waNumber = preg_replace('/[^0-9]/', '', $request->depot_whatsapp ?? '6281234567890');

        $settings = [
            'inactive_days' => $request->inactive_days,
            'points_per_gallon' => $request->points_per_gallon,
            'price_per_gallon' => $request->price_per_gallon,
            'silver_threshold' => $request->silver_threshold,
            'gold_threshold' => $request->gold_threshold,
            'depot_whatsapp' => $waNumber,
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Sinkronkan juga nomor telepon akun Admin yang sedang login
        $admin = auth()->user();
        if ($admin && $admin->role === 'admin' && !empty($waNumber)) {
            $admin->update(['phone' => $waNumber]);
        }

        return back()->with('success', 'Pengaturan sistem & nomor telepon akun Admin berhasil diperbarui.');
    }
}
