<?php

namespace Eclipse\Cms\Admin\Filament\Resources;

use Eclipse\Cms\Admin\Filament\Resources\SectionResource\Pages\CreateSection;
use Eclipse\Cms\Admin\Filament\Resources\SectionResource\Pages\EditSection;
use Eclipse\Cms\Admin\Filament\Resources\SectionResource\Pages\ListSections;
use Eclipse\Cms\Enums\SectionType;
use Eclipse\Cms\Models\Section;
use Filament\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;

class SectionResource extends Resource
{
    use Translatable;

    protected static ?string $model = Section::class;

    protected static ?string $slug = 'cms/sections';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static string|\UnitEnum|null $navigationGroup = 'CMS';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationLabel = 'Sections';

    protected static ?int $navigationSort = 200;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaSection::make('Basic Information')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Section Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter section name...')
                            ->columnSpan(2),

                        Select::make('type')
                            ->label('Section Type')
                            ->options(SectionType::class)
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->compact(),

                SchemaSection::make('Information')
                    ->columnSpanFull()
                    ->schema([
                        Placeholder::make('created_at')
                            ->label('Created')
                            ->content(fn (?Section $record): string => $record?->created_at?->format('M j, Y g:i A') ?? '-'),

                        Placeholder::make('updated_at')
                            ->label('Last Modified')
                            ->content(fn (?Section $record): string => $record?->updated_at?->diffForHumans() ?? '-'),

                        Placeholder::make('pages_count')
                            ->label('Total Pages')
                            ->content(fn (?Section $record): string => $record?->pages()->count().' pages' ?? '-'),
                    ])
                    ->columns(3)
                    ->compact()
                    ->hiddenOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Section Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('pages_count')
                    ->label('Pages')
                    ->counts('pages')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(SectionType::class),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
                Actions\ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSections::route('/'),
            'create' => CreateSection::route('/create'),
            'edit' => EditSection::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getPermissionPrefixes(): array
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
}
