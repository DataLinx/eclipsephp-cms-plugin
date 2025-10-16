<?php

namespace Eclipse\Cms\Admin\Filament\Resources;

use Eclipse\Cms\Admin\Filament\Resources\PageResource\Pages;
use Eclipse\Cms\Admin\Filament\Resources\PageResource\Pages\CreatePage;
use Eclipse\Cms\Admin\Filament\Resources\PageResource\Pages\EditPage;
use Eclipse\Cms\Admin\Filament\Resources\PageResource\Pages\ListPages;
use Eclipse\Cms\Enums\PageStatus;
use Eclipse\Cms\Models\Page;
use Filament\Actions;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;

class PageResource extends Resource
{
    use Translatable;

    protected static ?string $model = Page::class;

    protected static ?string $slug = 'cms/pages';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'CMS';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Pages';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label('Page Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter page title...')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, $set) {
                                if ($operation === 'create' && $state) {
                                    $slug = is_array($state) ? ($state['en'] ?? '') : $state;
                                    if ($slug) {
                                        $set('sef_key', Str::slug($slug));
                                    }
                                }
                            })
                            ->columnSpan(2),

                        Select::make('section_id')
                            ->label('Section')
                            ->relationship('section', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->columnSpan(1)
                            ->default(request()->get('sId')),

                        TextInput::make('sef_key')
                            ->label('URL Slug')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('auto-generated-from-title')
                            ->columnSpan(2),

                        Select::make('status')
                            ->label('Status')
                            ->options(PageStatus::class)
                            ->required()
                            ->default(PageStatus::Draft)
                            ->native(false)
                            ->columnSpan(1),

                        TextInput::make('code')
                            ->label('Page Code')
                            ->maxLength(255)
                            ->placeholder('Optional reference code')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->compact(),

                Section::make('Content')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('short_text')
                            ->label('Short Description')
                            ->placeholder('Brief summary or excerpt...')
                            ->rows(3)
                            ->columnSpanFull(),

                        RichEditor::make('long_text')
                            ->label('Main Content')
                            ->placeholder('Enter the main content of your page...')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'h2',
                                'h3',
                                'bulletList',
                                'orderedList',
                                'link',
                                'blockquote',
                                'codeBlock',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->compact(),

                Section::make('Information')
                    ->columnSpanFull()
                    ->schema([
                        Placeholder::make('created_at')
                            ->label('Created')
                            ->content(fn (?Page $record): string => $record?->created_at?->format('M j, Y g:i A') ?? '-'),

                        Placeholder::make('updated_at')
                            ->label('Last Modified')
                            ->content(fn (?Page $record): string => $record?->updated_at?->diffForHumans() ?? '-'),

                        Placeholder::make('word_count')
                            ->label('Word Count')
                            ->content(function (?Page $record): string {
                                if (! $record) {
                                    return '-';
                                }

                                $shortCount = $record->short_text ? str_word_count(strip_tags($record->short_text)) : 0;
                                $longCount = $record->long_text ? str_word_count(strip_tags($record->long_text)) : 0;
                                $total = $shortCount + $longCount;

                                return $total.' words';
                            }),
                    ])
                    ->columns(3)
                    ->compact()
                    ->hiddenOn('create'),

                Hidden::make('type')
                    ->default('page'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Page Title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('section.name')
                    ->label('Section')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->visible(fn ($livewire): bool => ! ($livewire instanceof Pages\ListPages && $livewire->sectionId)),

                TextColumn::make('sef_key')
                    ->label('URL Slug')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('URL slug copied')
                    ->copyMessageDuration(1500)
                    ->icon('heroicon-m-link')
                    ->iconPosition('after'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

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
                SelectFilter::make('status')
                    ->options(PageStatus::class),

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

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
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
        return ['title'];
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
