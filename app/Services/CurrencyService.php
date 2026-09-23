<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    public const CURRENCIES = ['PKR', 'USD', 'CNY'];

    public const SYMBOLS = [
        'PKR' => 'PKR',
        'USD' => '$',
        'CNY' => '¥',
    ];

    public const NAMES = [
        'PKR' => 'Pakistani Rupee',
        'CNY' => 'Chinese Yuan',
        'USD' => 'US Dollar',
    ];

    /**
     * Fallback rates (PKR → other) used when the API is unavailable.
     * Update these occasionally as a safety net.
     */
    private const FALLBACK_RATES = [
        'USD' => 0.00356,   // 1 PKR ≈ 0.00356 USD  (~281 PKR/USD)
        'CNY' => 0.02580,   // 1 PKR ≈ 0.02580 CNY  (~38.75 PKR/CNY)
    ];

    /**
     * Return PKR → {USD, CNY} rates, fetched fresh once per day.
     *
     * @return array{USD: float, CNY: float}
     */
    public static function getRates(): array
    {
        return Cache::remember('currency_rates_pkr', now()->addHours(24), function (): array {
            try {
                $response = Http::timeout(8)
                    ->get('https://api.frankfurter.app/latest', [
                        'from' => 'PKR',
                        'to'   => 'USD,CNY',
                    ]);

                if ($response->successful()) {
                    $rates = $response->json('rates', []);
                    if (isset($rates['USD'], $rates['CNY'])) {
                        return [
                            'USD' => (float) $rates['USD'],
                            'CNY' => (float) $rates['CNY'],
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('CurrencyService: Frankfurter API failed — using fallback rates.', [
                    'error' => $e->getMessage(),
                ]);
            }

            return self::FALLBACK_RATES;
        });
    }

    /**
     * Convert a PKR amount to the target currency.
     */
    public static function convert(float $pkrAmount, string $currency): float
    {
        if ($currency === 'PKR') {
            return $pkrAmount;
        }

        $rates = self::getRates();
        $rate  = $rates[$currency] ?? 1.0;

        return round($pkrAmount * $rate, 2);
    }

    /**
     * Format a PKR amount in the currency currently selected in the session.
     * Returns a string ready for display, e.g. "$ 12.50" or "¥ 89.23" or "PKR 3,520".
     */
    public static function format(float $pkrAmount): string
    {
        $currency = session('currency', 'PKR');

        if (! in_array($currency, self::CURRENCIES, true)) {
            $currency = 'PKR';
        }

        $converted = self::convert($pkrAmount, $currency);
        $symbol    = self::SYMBOLS[$currency] ?? $currency;

        if ($currency === 'PKR') {
            return $symbol . ' ' . number_format($converted, 0);
        }

        return $symbol . ' ' . number_format($converted, 2);
    }

    /**
     * Return the symbol for a currency code.
     */
    public static function symbol(string $currency): string
    {
        return self::SYMBOLS[$currency] ?? $currency;
    }

    /**
     * Return today's rates date from Frankfurter (for display).
     */
    public static function ratesDate(): string
    {
        return Cache::get('currency_rates_date_pkr', 'N/A');
    }
}
