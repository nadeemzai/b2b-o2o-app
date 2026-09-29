{{--
    <x-price :value="$pkrAmount" />

    Renders a 1688-style split price: the integer part at the inherited font size,
    the decimal part at ~60% of that size, baseline-aligned.
    For PKR (no decimals) the whole amount renders at one size.

    Props
      value   float   Amount in PKR — component converts to session currency.
      class   string  Extra classes (colour, weight, size) forwarded to the wrapper.
      symbol  bool    Show the currency symbol (default true).
--}}
@props(['value', 'class' => '', 'symbol' => true])

@php
    use App\Services\CurrencyService;

    $currency  = session('currency', 'PKR');
    $converted = CurrencyService::convert((float) $value, $currency);
    $sym       = $symbol ? CurrencyService::symbol($currency) : '';

    if ($currency === 'PKR') {
        // Whole numbers for PKR — no split
        $intPart = ($sym ? $sym . ' ' : '') . number_format((int) round($converted), 0);
        $decPart = null;
    } else {
        $formatted = number_format($converted, 2);
        [$whole, $frac] = explode('.', $formatted . '.00');
        $intPart = ($sym ? $sym . ' ' : '') . $whole;
        $decPart = '.' . $frac;
    }
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-baseline tabular-nums leading-none ' . $class]) }}>
    <span class="font-[inherit] text-[1em]">{{ $intPart }}</span>@if($decPart)<span class="font-[inherit]" style="font-size:0.62em">{{ $decPart }}</span>@endif
</span>
