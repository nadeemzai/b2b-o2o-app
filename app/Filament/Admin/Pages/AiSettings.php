<?php

namespace App\Filament\Admin\Pages;

use App\Models\AppSetting;
use App\Services\AiService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Crypt;

class AiSettings extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-cpu-chip';
    protected static ?string $navigationLabel = 'AI Settings';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int    $navigationSort  = 30;
    protected static string  $view            = 'filament.admin.pages.ai-settings';
    protected static ?string $title           = 'AI Provider Settings';

    // ── Active provider & model ───────────────────────────────────────────────
    public string $aiProvider = 'anthropic';
    public string $aiModel    = 'claude-haiku-4-5';

    // ── Per-provider key inputs (blank = keep existing) ───────────────────────
    public string $anthropicKeyInput = '';
    public string $groqKeyInput      = '';

    // ── Status flags (read from DB on mount) ──────────────────────────────────
    public bool $hasAnthropicKey = false;
    public bool $hasGroqKey      = false;

    public function mount(): void
    {
        $this->aiProvider       = AppSetting::get('ai_provider', 'anthropic');
        $this->aiModel          = AppSetting::get('ai_model', 'claude-haiku-4-5');
        $this->hasAnthropicKey  = (bool) AppSetting::get('ai_anthropic_key', '');
        $this->hasGroqKey       = (bool) AppSetting::get('ai_groq_key', '');
    }

    public function getModelOptions(): array
    {
        return AiService::MODELS[$this->aiProvider] ?? AiService::MODELS['anthropic'];
    }

    public function updatedAiProvider(string $value): void
    {
        $defaults = [
            'anthropic' => 'claude-haiku-4-5',
            'groq'      => 'meta-llama/llama-4-maverick-17b-128e-instruct',
        ];
        $this->aiModel = $defaults[$value] ?? 'claude-haiku-4-5';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->icon('heroicon-o-check')
                ->color('warning')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $rules = [
            'aiProvider'       => 'required|in:anthropic,groq',
            'aiModel'          => 'required|string|max:120',
            'anthropicKeyInput' => 'nullable|string|min:10',
            'groqKeyInput'      => 'nullable|string|min:10',
        ];

        $this->validate($rules);

        AppSetting::set('ai_provider', $this->aiProvider);
        AppSetting::set('ai_model',    $this->aiModel);

        if ($this->anthropicKeyInput !== '') {
            AppSetting::set('ai_anthropic_key', Crypt::encryptString($this->anthropicKeyInput));
            $this->hasAnthropicKey  = true;
            $this->anthropicKeyInput = '';
        }

        if ($this->groqKeyInput !== '') {
            AppSetting::set('ai_groq_key', Crypt::encryptString($this->groqKeyInput));
            $this->hasGroqKey  = true;
            $this->groqKeyInput = '';
        }

        Notification::make()
            ->title('AI settings saved')
            ->success()
            ->send();
    }
}
