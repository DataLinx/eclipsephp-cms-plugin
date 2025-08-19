<?php

namespace Eclipse\Cms\Admin\Filament\Resources;

use Eclipse\Cms\Admin\Filament\Resources\MenuItemResource\Pages;
use Eclipse\Cms\Enums\MenuItemType;
use Eclipse\Cms\Models\Menu;
use Eclipse\Cms\Models\Menu\Item;
use Eclipse\Cms\Services\LinkableDiscoveryService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class MenuItemResource extends Resource
{
    use Translatable;

    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'CMS';

    protected static ?int $navigationSort = 4;

    public static function getMenuItemFormSchema(): array
    {
        return [
            Forms\Components\Select::make('menu_id')
                ->relationship('menu', 'title')
                ->required()
                ->preload()
                ->searchable(),

            Forms\Components\TextInput::make('label')
                ->required(),

            Forms\Components\Select::make('type')
                ->options(MenuItemType::class)
                ->required()
                ->live()
                ->afterStateUpdated(function (Forms\Set $set) {
                    $set('linkable_class', null);
                    $set('linkable_id', null);
                    $set('custom_url', null);
                }),

            Forms\Components\Group::make([
                Forms\Components\Select::make('linkable_class')
                    ->label('Link Type')
                    ->options(fn () => LinkableDiscoveryService::getLinkableOptions())
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('linkable_id', null))
                    ->visible(fn (Get $get) => $get('type') === 'Linkable'),

                Forms\Components\Select::make('linkable_id')
                    ->label('Target')
                    ->options(function (Get $get) {
                        if (! $get('linkable_class')) {
                            return [];
                        }

                        return LinkableDiscoveryService::getLinkableModels($get('linkable_class'));
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get) => $get('type') === 'Linkable' && $get('linkable_class')),
            ])
                ->visible(fn (Get $get) => $get('type') === 'Linkable'),

            Forms\Components\TextInput::make('custom_url')
                ->label('Custom URL')
                ->url()
                ->required()
                ->visible(fn (Get $get) => $get('type') === 'CustomUrl'),

            Forms\Components\Toggle::make('new_tab')
                ->label('Open in new tab')
                ->default(false)
                ->visible(fn (Get $get) => in_array($get('type'), ['Linkable', 'CustomUrl'])),

            Forms\Components\Toggle::make('is_active')
                ->default(true),

            Forms\Components\Hidden::make('sort')
                ->default(0),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::getMenuItemFormSchema());
    }

    public static function getMenuItemTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('label')
                ->searchable()
                ->sortable(false),

            Tables\Columns\TextColumn::make('type')
                ->badge()
                ->formatStateUsing(fn ($state) => $state->getLabel()),

            Tables\Columns\IconColumn::make('is_active')
                ->boolean()
                ->sortable(false),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $selectArray = Item::selectArray();

                unset($selectArray[Item::defaultParentKey()]);
                $orderedIds = array_keys($selectArray);

                if (! empty($orderedIds)) {
                    // Use database-agnostic ordering for testing compatibility
                    if (config('database.default') === 'sqlite' || app()->environment('testing')) {
                        foreach (array_reverse($orderedIds) as $id) {
                            $query->orderByDesc(DB::raw("id = {$id}"));
                        }

                        return $query->orderBy('sort');
                    } else {
                        $idsString = implode(',', $orderedIds);

                        return $query->orderByRaw("FIELD(id, {$idsString})");
                    }
                }

                return $query->orderBy('sort');
            })
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable(false)
                    ->formatStateUsing(fn (Model $record): HtmlString => new HtmlString($record->getTreeFormattedName()))
                    ->tooltip(fn ($record) => $record->getFullPath()),

                Tables\Columns\TextColumn::make('menu.title')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->getLabel()),

                Tables\Columns\IconColumn::make('new_tab')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\SelectFilter::make('menu_id')
                    ->label('Menu')
                    ->options(Menu::pluck('title', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('type')
                    ->options(MenuItemType::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parent Item')
                    ->options(Item::getHierarchicalOptions())
                    ->searchable(),

                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
            'sorting' => Pages\SortingMenuItem::route('/sorting'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
