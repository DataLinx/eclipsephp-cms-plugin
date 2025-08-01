<?php

namespace Eclipse\Cms\Filament\Resources;

use Eclipse\Cms\Enums\SectionType;
use Eclipse\Cms\Filament\Resources\SectionResource\Pages;
use Eclipse\Cms\Filament\Resources\SectionResource\RelationManagers;
use Eclipse\Cms\Models\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SectionResource extends Resource
{
    use Translatable;

    protected static ?string $model = Section::class;

    protected static ?string $slug = 'cms/sections';

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Sections';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FormSection::make()
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

                FormSection::make('Information')
                    ->schema([
                        Placeholder::make('created_at')
                            ->label('Created')
                            ->content(fn (?Section $record): string => $record?->created_at?->format('M j, Y g:i A') ?? '-'),

                        Placeholder::make('updated_at')
                            ->label('Last Modified')
                            ->content(fn (?Section $record): string => $record?->updated_at?->diffForHumans() ?? '-'),

                        Placeholder::make('pages_count')
                            ->label('Total Pages')
                            ->content(fn (?Section $record): string => $record ? $record->pages()->count().' pages' : '-'),
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
                    ->sortable()
                    ->alignCenter(),

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
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PagesRelationManager::class,
        ];
    }

    public static function getRelatedUrl(string $relation, Model $record): string
    {
        if ($relation === 'pages') {
            return PageResource::getUrl('index', ['tableFilters' => ['section' => ['value' => $record->id]]]);
        }

        return parent::getRelatedUrl($relation, $record);
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
            'view',
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
