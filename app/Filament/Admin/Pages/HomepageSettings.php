<?php

namespace App\Filament\Admin\Pages;

use App\Models\HomepageSection;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class HomepageSettings extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Homepage Sections';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int    $navigationSort  = 10;
    protected static string  $view            = 'filament.admin.pages.homepage-settings';
    protected static ?string $title           = 'Homepage Section Settings';

    public bool $dealsActive       = true;
    public bool $newArrivalsActive = true;
    public bool $showStockBadge    = false;

    public function mount(): void
    {
        $sections = HomepageSection::whereIn('key', ['deals', 'new_arrivals', 'show_stock_badge'])
            ->get()
            ->keyBy('key');

        $this->dealsActive       = (bool) ($sections->get('deals')?->is_active ?? true);
        $this->newArrivalsActive = (bool) ($sections->get('new_arrivals')?->is_active ?? true);
        $this->showStockBadge    = (bool) ($sections->get('show_stock_badge')?->is_active ?? false);
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
        HomepageSection::where('key', 'deals')
            ->update(['is_active' => $this->dealsActive]);

        HomepageSection::where('key', 'new_arrivals')
            ->update(['is_active' => $this->newArrivalsActive]);

        HomepageSection::where('key', 'show_stock_badge')
            ->update(['is_active' => $this->showStockBadge]);

        Notification::make()
            ->title('Homepage sections updated')
            ->success()
            ->send();
    }
}
