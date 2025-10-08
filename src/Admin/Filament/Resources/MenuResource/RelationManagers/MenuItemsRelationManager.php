<?php

namespace Eclipse\Cms\Admin\Filament\Resources\MenuResource\RelationManagers;

use Eclipse\Cms\Admin\Filament\Resources\MenuResource;
use Eclipse\Cms\Enums\MenuItemType;
use Eclipse\Cms\Models\Menu\Item;
use Eclipse\Common\Foundation\Models\Scopes\ActiveScope;
use Eclipse\Common\Foundation\Plugins\HasLinkables;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\Concerns\Translatable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class MenuItemsRelationManager extends RelationManager
{
    use Translatable;

    protected static ?string $title = 'Menu Items';

    protected static string $relationship = 'allItems';

    protected static ?string $recordTitleAttribute = 'label';

    protected function getLinkableTypes(): array
    {
        $linkables = [];

        foreach (Filament::getCurrentPanel()?->getPlugins() ?? [] as $plugin) {
            if ($plugin instanceof HasLinkables) {
                $linkables = array_merge($linkables, $plugin->getLinkables());
            }
        }

        return $linkables;
    }

    protected function getMenuItemFormSchema(?int $excludeId = null): array
    {
        return [
            Forms\Components\Select::make('parent_id')
                ->columnSpanFull()
                ->label('Parent Item')
                ->options(
                    fn (?Model $record = null): array => Item::getHierarchicalOptions(
                        $this->getOwnerRecord()->id
                    )
                )
                ->searchable()
                ->placeholder('Select parent item (leave empty for root level)')
                ->nullable()
                ->dehydrateStateUsing(fn (?string $state): int => $state ? (int) $state : -1)
                ->formatStateUsing(fn (?int $state): ?string => $state === -1 ? null : (string) $state),
            Forms\Components\TextInput::make('label')
                ->columnSpanFull()
                ->required(),
            Forms\Components\Select::make('type')
                ->columnSpanFull()
                ->options(MenuItemType::class)
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set): void {
                    $set('custom_url', null);
                }),
            Forms\Components\MorphToSelect::make('linkable')
                ->columnSpanFull()
                ->label('Link Target')
                ->types($this->getLinkableTypes())
                ->searchable()
                ->preload()
                ->required()
                ->visible(fn (Get $get) => $get('type') === 'Linkable'),
            Forms\Components\TextInput::make('custom_url')
                ->columnSpanFull()
                ->label('Custom URL')
                ->required()
                ->visible(fn (Get $get) => $get('type') === 'CustomUrl'),
            Forms\Components\Toggle::make('new_tab')
                ->columnSpanFull()
                ->label('Open in new tab')
                ->default(false)
                ->visible(fn (Get $get) => in_array($get('type'), ['Linkable', 'CustomUrl'])),
            Forms\Components\Toggle::make('is_active')
                ->columnSpanFull()
                ->default(true),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getMenuItemFormSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable(false)
                    ->formatStateUsing(
                        fn (Model $record): HtmlString => new HtmlString(
                            $record->getTreeFormattedName()
                        )
                    )
                    ->tooltip(fn ($record) => $record->getFullPath()),
                Tables\Columns\TextColumn::make('type')
                    ->sortable(false)
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->getLabel()),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(false),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Active only')
                    ->trueLabel('All')
                    ->falseLabel('Inactive only')
                    ->queries(
                        true: fn ($query) => $query,
                        false: fn ($query) => $query->where('is_active', false),
                        blank: fn ($query) => $query->where('is_active', true),
                    )
                    ->baseQuery(fn (Builder $query) => $query->withoutGlobalScope(ActiveScope::class))
                    ->indicateUsing(function (array $state): array {
                        if ($state['value'] ?? null) {
                            return [Tables\Filters\Indicator::make('All')];
                        }

                        if (($state['value'] ?? null) === false) {
                            return [Tables\Filters\Indicator::make('Inactive only')];
                        }

                        return [];
                    }),

                TrashedFilter::make(),

                SelectFilter::make('type')
                    ->options(MenuItemType::class)
                    ->multiple(),
                SelectFilter::make('parent_id')
                    ->label('Parent Item')
                    ->options(fn () => Item::getParentOptions($this->getOwnerRecord()->id))
                    ->searchable(),
                TernaryFilter::make('new_tab')
                    ->label('Opens in New Tab'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('New Menu Item')
                    ->icon('heroicon-o-plus-circle'),
                Tables\Actions\Action::make('sort')
                    ->label('Sort Items')
                    ->icon('heroicon-o-arrows-up-down')
                    ->url(fn () => MenuResource::getUrl('sort-items', ['record' => $this->getOwnerRecord()]))
                    ->color('gray'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\Action::make('addSubitem')
                    ->icon('heroicon-o-plus-circle')
                    ->color('warning')
                    ->label('Add Sub-item')
                    ->form(fn () => $this->getMenuItemFormSchema(excludeId: null))
                    ->fillForm(fn (Model $record): array => [
                        'parent_id' => $record->id,
                        'is_active' => true,
                    ])
                    ->action(function (array $data, Model $record): void {
                        $data['menu_id'] = $this->getOwnerRecord()->id;
                        $data['parent_id'] = $record->id;

                        Item::create($data);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion()
                        ->hidden(function (HasTable $livewire): bool {
                            $filterState = $livewire->getTableFilterState('is_active') ?? [];

                            if (! array_key_exists('value', $filterState)) {
                                return true;
                            }

                            return blank($filterState['value']);
                        }),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion()
                        ->hidden(function (HasTable $livewire): bool {
                            $filterState = $livewire->getTableFilterState('is_active') ?? [];

                            if (! array_key_exists('value', $filterState)) {
                                return false;
                            }

                            if ($filterState['value']) {
                                return false;
                            }

                            return filled($filterState['value']);
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
