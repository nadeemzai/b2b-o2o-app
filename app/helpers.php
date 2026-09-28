<?php

use App\Services\CurrencyService;

if (! function_exists('currency_format')) {
    /**
     * Format a PKR price in the visitor's selected currency.
     *
     * Usage in Blade: {{ currency_format($product->price_pkr) }}
     */
    function currency_format(float|int|string $pkrAmount): string
    {
        return CurrencyService::format((float) $pkrAmount);
    }
}

if (! function_exists('currency_symbol')) {
    /**
     * Return the symbol for the currently selected currency.
     *
     * Usage in Blade: {{ currency_symbol() }}  →  "$", "¥", "PKR"
     */
    function currency_symbol(): string
    {
        $currency = session('currency', 'PKR');
        return CurrencyService::symbol($currency);
    }
}

if (! function_exists('active_currency')) {
    /**
     * Return the ISO code of the currently selected currency.
     */
    function active_currency(): string
    {
        return session('currency', 'PKR');
    }
}
