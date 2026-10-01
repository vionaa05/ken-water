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
        ]);

        $settings = [
            'inactive_days' => $request->inactive_days,
            'points_per_gallon' => $request->points_per_gallon,
            'price_per_gallon' => $request->price_per_gallon,
            'silver_threshold' => $request->silver_threshold,
            'gold_threshold' => $request->gold_threshold,
        ];

        foreach ($settings as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }

        return back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
