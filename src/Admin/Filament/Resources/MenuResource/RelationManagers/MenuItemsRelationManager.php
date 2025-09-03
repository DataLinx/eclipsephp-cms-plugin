<?php

namespace Eclipse\Cms\Admin\Filament\Resources\MenuResource\RelationManagers;

use Eclipse\Cms\Admin\Filament\Resources\MenuResource;
use Eclipse\Cms\Enums\MenuItemType;
use Eclipse\Cms\Models\Menu\Item;
use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Filament\Forms;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\Concerns\Translatable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class MenuItemsRelationManager extends RelationManager
{
    use Translatable;

    protected static ?string $title = 'Menu Items';

    protected static string $relationship = 'allItems';

    protected static ?string $recordTitleAttribute = 'label';

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
                ->types([
                    MorphToSelect\Type::make(Page::class)
                        ->titleAttribute('title')
                        ->label('Page'),
                    MorphToSelect\Type::make(Section::class)
                        ->titleAttribute('name')
                        ->label('Section'),
                ])
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
                TrashedFilter::make(),

                SelectFilter::make('type')
                    ->options(MenuItemType::class)
                    ->multiple(),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
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
                Tables\Actions\Action::make('addSublink')
                    ->icon('heroicon-o-plus-circle')
                    ->color('warning')
                    ->label('Add Sublink')
                    ->form(fn () => $this->getMenuItemFormSchema(excludeId: null))
                    ->fillForm(fn (Model $record): array => [
                        'parent_id' => $record->id,
                    ])
                    ->action(function (array $data, Model $record): void {
                        $data['menu_id'] = $this->getOwnerRecord()->id;
                        $data['parent_id'] = $record->id;

                        Item::create($data);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }
}
