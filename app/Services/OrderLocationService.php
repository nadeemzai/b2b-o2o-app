<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderLocationService
{
    /**
     * HTTP headers that identify a mobile app client.
     * If the header key exists AND either the value is null (any value accepted)
     * or the value matches exactly, the request is classified as mobile_app.
     */
    private const MOBILE_APP_HEADERS = [
        'X-App-Platform'  => null,     // any value → mobile
        'X-Client-Type'   => 'mobile',
        'X-App-Source'    => 'mobile_app',
        'X-Platform'      => 'mobile',
        'X-App-Type'      => 'mobile',
        'X-Mobile-App'    => null,     // any value → mobile
    ];

    /**
     * Substrings (case-insensitive) in the User-Agent that indicate a mobile app.
     */
    private const MOBILE_UA_PATTERNS = [
        'flutter', 'okhttp', 'dart:io', 'dartio', 'reactnative',
        'nativescript', 'ozgroup', 'b2bo2o',
    ];

    // ──────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────

    /**
     * Collect all location + device metadata from the request.
     * Returns an array ready to merge into Order::update().
     */
    public function collect(Request $request): array
    {
        $ip          = $this->getClientIp($request);
        $deviceType  = $this->detectDeviceType($request);
        $location    = $ip && ! $this->isPrivateIp($ip)
            ? $this->resolveLocation($ip)
            : [];

        return [
            'ip_address'       => $ip,
            'user_agent'       => substr((string) $request->userAgent(), 0, 500),
            'device_type'      => $deviceType,
            'order_city'       => $location['city']      ?? null,
            'order_area'       => $location['area']      ?? null,
            'order_latitude'   => $location['latitude']  ?? null,
            'order_longitude'  => $location['longitude'] ?? null,
        ];
    }

    /**
     * Resolve the real client IP, handling common proxy headers.
     */
    public function getClientIp(Request $request): ?string
    {
        $candidates = array_filter([
            $request->header('CF-Connecting-IP'),   // Cloudflare
            $request->header('X-Real-IP'),
            $request->header('X-Forwarded-For'),    // may be comma-list
            $request->ip(),
        ]);

        foreach ($candidates as $value) {
            // X-Forwarded-For can be "client, proxy1, proxy2"
            $ip = trim(explode(',', $value)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return null;
    }

    /**
     * Detect whether the request comes from a mobile app or a web browser.
     */
    public function detectDeviceType(Request $request): string
    {
        // 1. Check custom mobile-app headers first (most reliable signal).
        foreach (self::MOBILE_APP_HEADERS as $header => $expected) {
            $value = $request->header($header);
            if ($value !== null) {
                if ($expected === null || strtolower($value) === strtolower($expected)) {
                    return 'mobile_app';
                }
            }
        }

        // 2. Fall back to User-Agent pattern matching.
        $ua = strtolower((string) $request->userAgent());
        foreach (self::MOBILE_UA_PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) {
                return 'mobile_app';
            }
        }

        // 3. If there's a User-Agent at all, classify as web.
        if ($ua !== '') {
            return 'web';
        }

        return 'unknown';
    }

    /**
     * Call ip-api.com to resolve a public IP to city-level location data.
     * Returns empty array on any failure (timeout, rate-limit, bad IP).
     */
    public function resolveLocation(string $ip): array
    {
        try {
            $response = Http::timeout(3)
                ->retry(1, 200)
                ->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'status,city,regionName,lat,lon',
                ]);

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();

            if (($data['status'] ?? '') !== 'success') {
                return [];
            }

            return [
                'city'      => $data['city']       ?? null,
                'area'      => $data['regionName'] ?? null,
                'latitude'  => isset($data['lat']) ? (float) $data['lat'] : null,
                'longitude' => isset($data['lon']) ? (float) $data['lon'] : null,
            ];
        } catch (\Throwable $e) {
            Log::warning('OrderLocationService: geolocation failed', [
                'ip'    => $ip,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    private function isPrivateIp(string $ip): bool
    {
        return ! filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
