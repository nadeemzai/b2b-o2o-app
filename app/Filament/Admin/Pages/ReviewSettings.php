<?php

namespace App\Filament\Admin\Pages;

use App\Models\AppSetting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ReviewSettings extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Review Settings';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int    $navigationSort  = 20;
    protected static string  $view            = 'filament.admin.pages.review-settings';
    protected static ?string $title           = 'Product Review Settings';

    /** Max number of reviews a single retailer can submit per product. */
    public int $maxReviewsPerProduct = 3;

    public function mount(): void
    {
        $this->maxReviewsPerProduct = AppSetting::getInt('max_reviews_per_product', 3);
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
        $this->validate([
            'maxReviewsPerProduct' => 'required|integer|min:1|max:50',
        ]);

        AppSetting::set('max_reviews_per_product', $this->maxReviewsPerProduct);

        Notification::make()
            ->title('Review settings saved')
            ->success()
            ->send();
    }
}
