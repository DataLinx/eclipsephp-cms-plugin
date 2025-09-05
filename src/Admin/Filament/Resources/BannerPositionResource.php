<?php

namespace Eclipse\Cms\Admin\Filament\Resources;

use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages;
use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\RelationManagers;
use Eclipse\Cms\Models\Banner\Position as BannerPosition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BannerPositionResource extends Resource
{
    use Translatable;

    protected static ?string $model = BannerPosition::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $modelLabel = 'Banner';

    protected static ?string $pluralModelLabel = 'Banners';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Position')
                    ->collapsible()
                    ->collapsed(
                        fn ($context) => ($context === 'edit')
                    )
                    ->compact()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('code')
                            ->maxLength(20)
                            ->alphaDash(),
                    ]),

                Forms\Components\Section::make('Image Types')
                    ->collapsible()
                    ->collapsed(
                        fn ($context) => ($context === 'edit')
                    )
                    ->compact()
                    ->schema([
                        Forms\Components\Repeater::make('imageTypes')
                            ->relationship()
                            ->columnSpanFull()
                            ->hiddenLabel()
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
                            ->collapsed(
                                fn ($context) => ($context === 'edit')
                            )
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Add Image Type')
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ]),
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

            ])
            ->defaultSort('id')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
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
            RelationManagers\BannerRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBannerPositions::route('/'),
            'create' => Pages\CreateBannerPosition::route('/create'),
            'edit' => Pages\EditBannerPosition::route('/{record}/edit'),
        ];
    }

    public static function getPermissions(): array
    {
        return [
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'restore',
            'restore_any',
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
