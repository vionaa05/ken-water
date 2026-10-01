<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Ambil nilai pengaturan sistem dari database
     */
    function setting(string $key, mixed $default = null): mixed
    {
        static $cache = [];

        if (!isset($cache[$key])) {
            try {
                $setting = \App\Models\Setting::find($key);
                $cache[$key] = $setting ? $setting->value : $default;
            } catch (\Exception $e) {
                return $default;
            }
        }

        return $cache[$key];
    }
}

if (!function_exists('format_rupiah')) {
    function format_rupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('loyalty_level_label')) {
    function loyalty_level_label(string $level): string
    {
        return match($level) {
            'bronze' => '🥉 Bronze',
            'silver' => '🥈 Silver',
            'gold' => '🥇 Gold',
            default => ucfirst($level),
        };
    }
}
