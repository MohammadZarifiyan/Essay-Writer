<?php

namespace App\Filament\Resources\BotNotifications;

use App\Models\BotNotification;
use App\State\BotNotificationStatus\Canceled;
use App\State\BotNotificationStatus\Failed;
use App\State\BotNotificationStatus\Finished;
use App\State\BotNotificationStatus\Pending;
use App\State\BotNotificationStatus\Processing;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Number;

class BotNotificationResource extends Resource
{
    protected static ?string $model = BotNotification::class;

    protected static ?int $navigationSort = 0;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    public static function getNavigationBadge(): ?string
    {
        $count = BotNotification::whereState('status', [Pending::class, Processing::class])->count('id');

        return $count > 0 ? Number::format($count) : null;
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return Color::Green;
    }

    public static function getPluralLabel(): string
    {
        return __('filament/resources/bot-notifications/index.plural-label');
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources/bot-notifications/index.model-label');
    }

    public static function table(Table $table): Table
    {
        return $table->striped()
            ->emptyStateHeading(__('filament/resources/bot-notifications/index.table.empty-state-heading'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label(__('filament/resources/bot-notifications/index.table.columns.id'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label(__('filament/resources/bot-notifications/index.table.columns.status'))
                    ->badge()
                    ->color(fn (BotNotification $botNotification) => match (true) {
                        $botNotification->status->equals(Pending::class) => Color::Blue,
                        $botNotification->status->equals(Processing::class) => Color::Orange,
                        $botNotification->status->equals(Failed::class) => Color::Red,
                        $botNotification->status->equals(Finished::class) => Color::Green,
                        $botNotification->status->equals(Canceled::class) => Color::Gray,
                    })
                    ->getStateUsing(fn (BotNotification $botNotification) => __('states/bot-notification-status.' . $botNotification->status::$name)),

                IconColumn::make('quote')
                    ->label(__('filament/resources/bot-notifications/index.table.columns.quote'))
                    ->boolean(),

                TextColumn::make('total_notifications')
                    ->label(__('filament/resources/bot-notifications/index.table.columns.total-notifications'))
                    ->sortable(),

                TextColumn::make('successful_notifications')
                    ->label(__('filament/resources/bot-notifications/index.table.columns.successful-notifications'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament/resources/bot-notifications/index.table.filters.status'))
                    ->options(function () {
                        return BotNotification::getStatesFor('status')
                            ->mapWithKeys(fn (string $status) => [$status => __('states/bot-notification-status.' . $status)])
                            ->toArray();
                    })
                    ->optionsLimit(-1)
                    ->preload()
                    ->multiple()
                    ->searchable(),

                TernaryFilter::make('quote')
                    ->label(__('filament/resources/bot-notifications/index.table.filters.quote'))
                    ->searchable(),
            ])
            ->recordActions([
                Action::make('cancel')
                    ->label(__('filament/resources/bot-notifications/index.table.record-actions.cancel.label'))
                    ->button()
                    ->visible(fn (BotNotification $botNotification) => $botNotification->status->equals(Pending::class, Processing::class))
                    ->requiresConfirmation()
                    ->modalWidth(Width::Medium)
                    ->modalHeading(__('filament/resources/bot-notifications/index.table.record-actions.cancel.modal-heading'))
                    ->modalDescription(__('filament/resources/bot-notifications/index.table.record-actions.cancel.modal-description'))
                    ->action(function (BotNotification $botNotification) {
                        if (!$botNotification->status->equals(Processing::class)) {
                            Notification::make()
                                ->title(__('filament/resources/bot-notifications/index.notifications.failed-to-cancel-the-notification'))
                                ->danger()
                                ->send();
                            return;
                        }

                        $batch = Bus::findBatch($botNotification->batch_id);

                        if ($batch) {
                            $batch->cancel();

                            if (!$batch->cancelled()) {
                                Notification::make()
                                    ->title(__('filament/resources/bot-notifications/index.notifications.failed-to-cancel-the-notification'))
                                    ->danger()
                                    ->send();
                            }

                            return;
                        }

                        $botNotification->status->transitionTo(Canceled::class);

                        Notification::make()
                            ->title(__('filament/resources/bot-notifications/index.notifications.notification-canceled'))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBotNotifications::route('/'),
        ];
    }
}
