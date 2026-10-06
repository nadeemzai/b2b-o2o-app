<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Captures geolocation (from IP) and device type for an incoming order request.
 *
 * Geolocation: calls ip-api.com (free, no API key required up to 1,000 req/day).
 *              Falls back gracefully — all fields null — if the call fails or times out.
 *
 * Device type: checks, in order:
 *   1. Custom app headers (configurable; see MOBILE_APP_HEADERS below).
 *   2. User-Agent string patterns common to mobile apps / Flutter apps.
 *   3. Defaults to 'web'.
 *
 * To adapt to your mobile app's exact header, add it to MOBILE_APP_HEADERS below.
 */
class OrderLocationService
{
    /**
     * Header name → expected value (case-insensitive).
     * The mobile app should send ONE of these on every request.
     *
     * Common patterns:
     *   'X-App-Platform' => 'android' or 'ios'
     *   'X-Client-Type'  => 'mobile'
     *   'X-App-Source'   => 'mobile_app'
     *   'X-Platform'     => 'mobile'
     *
     * If the header is present with ANY value (not just a specific one), set the
     * value to null — e.g. ['X-Mobile-App' => null] means: presence is enough.
     */
    private const MOBILE_APP_HEADERS = [
        'X-App-Platform'  => null,   // any value → mobile
        'X-Client-Type'   => 'mobile',
        'X-App-Source'    => 'mobile_app',
        'X-Platform'      => 'mobile',
        'X-App-Type'      => 'mobile',
    ];

    /**
     * User-Agent substrings that indicate a mobile app (not a mobile browser).
     * Flutter, React Native, Kotlin OkHttp, Swift URLSession, etc.
     */
    private const MOBILE_UA_PATTERNS = [
        'flutter',
        'okhttp',
        'dart:io',
        'reactnative',
        'nativescript',
        'ozgroup',     // put your app's UA string here if it's custom
    ];

    // ──────────────────────────────────────────────

    /**
     * Resolve the real client IP, respecting common proxies.
     */
    public function getClientIp(Request $request): ?string
    {
        // Respect X-Forwarded-For (load balancers, Nginx proxies)
        $forwarded = $request->header('X-Forwarded-For');
        if ($forwarded) {
            // Take the leftmost (original client) IP
            return trim(explode(',', $forwarded)[0]);
        }

        return $request->ip();
    }

    /**
     * Detect device type from the request headers and User-Agent.
     *
     * @return 'mobile_app'|'web'|'unknown'
     */
    public function detectDeviceType(Request $request): string
    {
        // 1. Custom app headers
        foreach (self::MOBILE_APP_HEADERS as $header => $expectedValue) {
            $headerValue = $request->header($header);
            if ($headerValue !== null) {
                if ($expectedValue === null) {
                    return 'mobile_app';   // any presence is enough
                }
                if (strtolower($headerValue) === strtolower($expectedValue)) {
                    return 'mobile_app';
                }
            }
        }

        // 2. User-Agent patterns
        $ua = strtolower($request->userAgent() ?? '');
        foreach (self::MOBILE_UA_PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) {
                return 'mobile_app';
            }
        }

        return 'web';
    }

    /**
     * Resolve city/area/lat/lng from the client IP using ip-api.com (free tier).
     *
     * Returns an array:
     * [
     *   'city'      => string|null,
     *   'area'      => string|null,   // region / province
     *   'latitude'  => float|null,
     *   'longitude' => float|null,
     * ]
     */
    public function resolveLocation(string $ip): array
    {
        $empty = ['city' => null, 'area' => null, 'latitude' => null, 'longitude' => null];

        // Skip loopback / private IPs (local dev, internal proxies)
        if ($this->isPrivateIp($ip)) {
            return $empty;
        }

        try {
            $response = Http::timeout(3)
                ->retry(1, 200)
                ->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'status,city,regionName,lat,lon',
                ]);

            if (! $response->successful()) {
                return $empty;
            }

            $data = $response->json();

            if (($data['status'] ?? '') !== 'success') {
                return $empty;
            }

            return [
                'city'      => $data['city']       ?? null,
                'area'      => $data['regionName']  ?? null,
                'latitude'  => isset($data['lat']) ? (float) $data['lat'] : null,
                'longitude' => isset($data['lon']) ? (float) $data['lon'] : null,
            ];

        } catch (\Throwable $e) {
            Log::warning('OrderLocationService: geolocation lookup failed', [
                'ip'    => $ip,
                'error' => $e->getMessage(),
            ]);
            return $empty;
        }
    }

    /**
     * Collect all location + device data for an incoming request.
     *
     * Returns array ready to mass-assign onto the Order model:
     * [
     *   'ip_address'       => string|null,
     *   'user_agent'       => string|null,
     *   'device_type'      => 'web'|'mobile_app'|'unknown',
     *   'order_city'       => string|null,
     *   'order_area'       => string|null,
     *   'order_latitude'   => float|null,
     *   'order_longitude'  => float|null,
     * ]
     */
    public function collect(Request $request): array
    {
        $ip         = $this->getClientIp($request);
        $deviceType = $this->detectDeviceType($request);
        $location   = $this->resolveLocation($ip ?? '');

        return [
            'ip_address'      => $ip,
            'user_agent'      => substr($request->userAgent() ?? '', 0, 500),
            'device_type'     => $deviceType,
            'order_city'      => $location['city'],
            'order_area'      => $location['area'],
            'order_latitude'  => $location['latitude'],
            'order_longitude' => $location['longitude'],
        ];
    }

    // ──────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────

    private function isPrivateIp(string $ip): bool
    {
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'], true)) {
            return true;
        }
        // FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
