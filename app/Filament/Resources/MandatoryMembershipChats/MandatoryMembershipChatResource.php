<?php

namespace App\Filament\Resources\MandatoryMembershipChats;

use App\Models\MandatoryMembershipChat;
use App\Services\MandatoryMembershipChatDataMutator;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class MandatoryMembershipChatResource extends Resource
{
    protected static ?string $model = MandatoryMembershipChat::class;

    protected static ?int $navigationSort = 0;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenter;

    public static function getNavigationBadge(): ?string
    {
        $count = MandatoryMembershipChat::where('validity', false)->count('id');

        return $count > 0 ? Number::format($count) : null;
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return Color::Amber;
    }

    public static function getPluralLabel(): string
    {
        return __('filament/resources/mandatory-membership-chats/index.plural-label');
    }

    public static function getModelLabel(): string
    {
        return __('filament/resources/mandatory-membership-chats/index.model-label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('chat_id')
                    ->label(__('filament/resources/mandatory-membership-chats/index.form.chat-id'))
                    ->required()
                    ->integer()
                    ->unique(),

                TextInput::make('title')
                    ->label(__('filament/resources/mandatory-membership-chats/index.form.title'))
                    ->required(fn (string $operation): bool => $operation === 'edit')
                    ->string()
                    ->maxLength(255),

                TextInput::make('url')
                    ->label(__('filament/resources/mandatory-membership-chats/index.form.url'))
                    ->required(fn (string $operation): bool => $operation === 'edit')
                    ->url()
                    ->regex('/^https?:\/\/t.me\/(..*)$/')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->striped()
            ->emptyStateHeading(__('filament/resources/mandatory-membership-chats/index.table.empty-state-heading'))
            ->defaultSort('order_number')
            ->reorderable('order_number')
            ->columns([
                TextColumn::make('title')
                    ->label(__('filament/resources/mandatory-membership-chats/index.table.columns.title'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('chat_id')
                    ->label(__('filament/resources/mandatory-membership-chats/index.table.columns.chat-id'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('url')
                    ->label(__('filament/resources/mandatory-membership-chats/index.table.columns.url'))
                    ->url(fn (MandatoryMembershipChat $mandatoryMembershipChat) => $mandatoryMembershipChat->url)
                    ->sortable()
                    ->searchable()
                    ->extraAttributes(['dir' => 'ltr']),

                IconColumn::make('validity')
                    ->label(__('filament/resources/mandatory-membership-chats/index.table.columns.validity'))
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('validity')
                    ->label(__('filament/resources/mandatory-membership-chats/index.table.filters.validity'))
                    ->searchable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalWidth(Width::Medium)
                        ->mutateDataUsing(new MandatoryMembershipChatDataMutator()->mutate(...)),
                    DeleteAction::make()->modalWidth(Width::Medium)
                ])
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth(Width::Medium)
                    ->mutateDataUsing(new MandatoryMembershipChatDataMutator()->mutate(...))
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMandatoryMembershipChats::route('/'),
        ];
    }
}
