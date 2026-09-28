<?php

namespace App\Livewire;

use Livewire\Component;

class LanguageSwitcher extends Component
{
    public string $current;

    public array $languages = [
        'en'    => ['label' => 'English', 'flag' => '🇬🇧', 'short' => 'EN'],
        'zh_CN' => ['label' => '中文',    'flag' => '🇨🇳', 'short' => '中文'],
    ];

    public function mount(): void
    {
        $this->current = session('locale', app()->getLocale() ?: 'en');
    }

    public function switchLanguage(string $locale): void
    {
        if (! array_key_exists($locale, $this->languages)) {
            return;
        }

        session(['locale' => $locale]);
        $this->current = $locale;

        // Full page reload so all server-rendered text picks up the new locale
        $this->redirect(url()->current(), navigate: false);
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}
