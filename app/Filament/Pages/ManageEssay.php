<?php

namespace App\Filament\Pages;

use App\Settings\EssaySettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class ManageEssay extends SettingsPage
{
    protected static ?int $navigationSort = 1;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = EssaySettings::class;

    public static function getNavigationLabel(): string
    {
        return __('filament/pages/manage-essay.navigation-label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament/pages/manage-essay.page-title');
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('filament/pages/manage-essay.heading');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('defaultDailyLimit')
                    ->label(__('filament/pages/manage-essay.form.default-daily-limit'))
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(999),
            ]);
    }
}
