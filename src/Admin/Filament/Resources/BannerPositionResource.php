<?php

namespace Eclipse\Cms\Admin\Filament\Resources;

use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages;
use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\RelationManagers;
use Eclipse\Cms\Models\Banner\Position as BannerPosition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BannerPositionResource extends Resource
{
    use Translatable;

    protected static ?string $model = BannerPosition::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $modelLabel = 'Banner Position';

    protected static ?string $pluralModelLabel = 'Banner Positions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->compact()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('code')
                            ->maxLength(20)
                            ->alphaDash(),
                    ]),

                Forms\Components\Repeater::make('imageTypes')
                    ->relationship()
                    ->columnSpanFull()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Image Type')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('code')
                            ->alphaDash()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('image_width')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(9999)
                            ->label('Width (px)'),

                        Forms\Components\TextInput::make('image_height')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(9999)
                            ->label('Height (px)'),

                        Forms\Components\Toggle::make('is_hidpi')
                            ->label('Require HiDPI (2x) images'),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->addActionLabel('Add Image Type')
                    ->reorderableWithButtons()
                    ->collapsible(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Position Details')
                    ->columns([
                        'default' => 2,
                        'md' => 4,
                    ])
                    ->compact()
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),

                        Infolists\Components\TextEntry::make('code'),

                        Infolists\Components\TextEntry::make('imageTypes_count')
                            ->badge()
                            ->label('Image Types')
                            ->getStateUsing(fn ($record) => $record->imageTypes()->count()),

                        Infolists\Components\TextEntry::make('banners_count')
                            ->badge()
                            ->label('Total Banners')
                            ->getStateUsing(fn ($record) => $record->banners()->count()),
                    ]),

                Infolists\Components\RepeatableEntry::make('imageTypes')
                    ->columns([
                        'default' => 2,
                        'md' => 4,
                    ])
                    ->columnSpanFull()
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),

                        Infolists\Components\TextEntry::make('code')
                            ->badge(),

                        Infolists\Components\TextEntry::make('image_size')
                            ->default(
                                fn (Model $record) => "{$record->image_width}px * {$record->image_height}px"
                            ),

                        Infolists\Components\IconEntry::make('is_hidpi')
                            ->label('HiDPI Required')
                            ->boolean(),
                    ])
                    ->visible(fn ($record) => $record->imageTypes()->exists()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('code')
                    ->sortable()
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('banners_count')
                    ->badge()
                    ->counts('banners')
                    ->suffix(fn (?int $state): string => $state > 1 ? ' Items' : ' Item')
                    ->label('Banners'),
            ])
            ->defaultSort('id')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BannersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBannerPositions::route('/'),
            'create' => Pages\CreateBannerPosition::route('/create'),
            'view' => Pages\ViewBannerPosition::route('/{record}'),
            'edit' => Pages\EditBannerPosition::route('/{record}/edit'),
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
