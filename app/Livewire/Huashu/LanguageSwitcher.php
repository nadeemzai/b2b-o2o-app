<?php

namespace App\Livewire\Huashu;

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
        app()->setLocale($locale);
        $this->current = $locale;

        $this->redirect(url()->current(), navigate: false);
    }

    public function render()
    {
        return view('livewire.huashu.language-switcher');
    }
}
