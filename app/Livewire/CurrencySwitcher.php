<?php

namespace App\Livewire;

use App\Services\CurrencyService;
use Livewire\Component;

class CurrencySwitcher extends Component
{
    public string $current;

    /** @var array<string, array{symbol: string, name: string, flag: string}> */
    public array $currencies = [
        'PKR' => ['symbol' => 'PKR', 'name' => 'Pakistani Rupee', 'flag' => '🇵🇰'],
        'USD' => ['symbol' => '$',   'name' => 'US Dollar',        'flag' => '🇺🇸'],
        'CNY' => ['symbol' => '¥',   'name' => 'Chinese Yuan',     'flag' => '🇨🇳'],
    ];

    public array $rates = [];

    public function mount(): void
    {
        $this->current = session('currency', 'PKR');
        $this->rates   = CurrencyService::getRates();
    }

    public function switchCurrency(string $currency): void
    {
        if (! array_key_exists($currency, $this->currencies)) {
            return;
        }

        session(['currency' => $currency]);
        $this->current = $currency;

        // Full reload so server-rendered prices update
        $this->redirect(url()->current(), navigate: false);
    }

    public function render()
    {
        return view('livewire.currency-switcher');
    }
}
