<?php

namespace Eclipse\Cms\Filament\Resources\SectionResource\RelationManagers;

use Eclipse\Cms\Enums\PageStatus;
use Eclipse\Cms\Filament\Resources\PageResource;
use Eclipse\Cms\Models\Page;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PagesRelationManager extends RelationManager
{
    protected static string $relationship = 'pages';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, $set) {
                        if ($operation === 'create' && $state) {
                            $set('sef_key', Str::slug($state));
                        }
                    }),

                TextInput::make('sef_key')
                    ->label('SEF Key')
                    ->required()
                    ->maxLength(255)
                    ->helperText('URL-friendly version of the title. Will be auto-generated if left empty.'),

                Textarea::make('short_text')
                    ->label('Short Text')
                    ->rows(3)
                    ->columnSpanFull(),

                RichEditor::make('long_text')
                    ->label('Long Text')
                    ->columnSpanFull(),

                TextInput::make('code')
                    ->label('Code')
                    ->maxLength(255),

                Select::make('status')
                    ->label('Status')
                    ->options(PageStatus::class)
                    ->required()
                    ->default(PageStatus::Draft)
                    ->native(false),

                Hidden::make('type'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('sef_key')
                    ->label('SEF Key')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('short_text')
                    ->label('Short Text')
                    ->limit(50)
                    ->toggleable()
                    ->icon(fn (Page $record): ?string => filled($record->short_text) ? 'heroicon-m-check-circle' : null)
                    ->iconColor('success'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PageStatus::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Create Page'),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (Page $record): string => PageResource::getUrl('edit', ['record' => $record])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
