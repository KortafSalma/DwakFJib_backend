<?php

use Illuminate\Support\Facades\Cache;

if (!function_exists('cache_key')) {
    function cache_key(string $prefix, mixed $identifier): string
    {
        return sprintf('dwakfjib:%s:%s', $prefix, $identifier);
    }
}

if (!function_exists('remember_cache')) {
    function remember_cache(string $key, \Closure $callback, int $ttl = 3600): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }
}

if (!function_exists('clear_cache_prefix')) {
    function clear_cache_prefix(string $prefix): void
    {
        Cache::forget($prefix);
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float $amount, string $currency = 'MAD'): string
    {
        return number_format($amount, 2) . ' ' . $currency;
    }
}

if (!function_exists('generate_order_number')) {
    function generate_order_number(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}

if (!function_exists('calculate_distance')) {
    function calculate_distance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
