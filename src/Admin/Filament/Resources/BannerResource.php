<?php

namespace Eclipse\Cms\Admin\Filament\Resources;

use Closure;
use Eclipse\Cms\Admin\Filament\Resources\BannerResource\Pages;
use Eclipse\Cms\Models\Banner;
use Eclipse\Cms\Models\Banner\Position;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BannerResource extends Resource
{
    use Translatable;

    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $modelLabel = 'Banner';

    protected static ?string $pluralModelLabel = 'Banners';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->compact()
                    ->schema([
                        Forms\Components\Select::make('position_id')
                            ->label('Position')
                            ->relationship('position', 'name')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, Set $set) => $set('images', [])),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('link')
                            ->url()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true),

                        Forms\Components\Toggle::make('new_tab')
                            ->default(false)
                            ->label('Open in new tab'),
                    ]),

                Forms\Components\Repeater::make('images')
                    ->relationship()
                    ->columnSpanFull()
                    ->hiddenLabel()
                    ->schema([
                        Forms\Components\Hidden::make('type_id'),
                        Forms\Components\Hidden::make('is_hidpi'),
                        Forms\Components\Hidden::make('position_id'),
                        FileUpload::make('file')
                            ->hiddenLabel()
                            ->image()
                            ->directory('banners')
                            ->rules([
                                function (Get $get) {
                                    return function (string $attribute, $value, Closure $fail) use ($get): void {
                                        if (! $value) {
                                            return;
                                        }

                                        $typeId = $get('type_id');
                                        $isHidpi = $get('is_hidpi');

                                        if ($typeId) {
                                            $imageType = Position::find($get('position_id'))?->imageTypes()->find($typeId);
                                            if ($imageType && $imageType->image_width && $imageType->image_height) {
                                                $expectedWidth = $isHidpi ? $imageType->image_width * 2 : $imageType->image_width;
                                                $expectedHeight = $isHidpi ? $imageType->image_height * 2 : $imageType->image_height;

                                                $imageSize = getimagesize($value->getPathname());
                                                $actualWidth = $imageSize[0] ?? 0;
                                                $actualHeight = $imageSize[1] ?? 0;

                                                if ($actualWidth !== $expectedWidth || $actualHeight !== $expectedHeight) {
                                                    $fail("Image must be exactly {$expectedWidth}×{$expectedHeight}px. Got {$actualWidth}×{$actualHeight}px.");
                                                }
                                            }
                                        }
                                    };
                                },
                            ])
                            ->helperText(function (Get $get): string {
                                $typeId = $get('type_id');
                                $isHidpi = $get('is_hidpi');
                                $positionId = $get('position_id');

                                if ($typeId && $positionId) {
                                    $imageType = Position::find($positionId)?->imageTypes()->find($typeId);
                                    if ($imageType && $imageType->image_width && $imageType->image_height) {
                                        if ($isHidpi) {
                                            $hidpiWidth = $imageType->image_width * 2;
                                            $hidpiHeight = $imageType->image_height * 2;

                                            return "{$imageType->name} @2x ({$hidpiWidth}×{$hidpiHeight}, displayed as {$imageType->image_width}×{$imageType->image_height})";
                                        } else {
                                            return "{$imageType->name} ({$imageType->image_width}×{$imageType->image_height})";
                                        }
                                    }
                                }

                                return 'Upload banner image';
                            }),
                    ])
                    ->default(function (Get $get) {
                        $positionId = $get('position_id');
                        if (! $positionId) {
                            return [];
                        }

                        $items = [];
                        Position::find($positionId)?->imageTypes()->get()->each(function ($imageType) use (&$items, $positionId) {
                            if ($imageType->is_hidpi) {
                                $items[] = ['type_id' => $imageType->id, 'is_hidpi' => true, 'position_id' => $positionId];
                            } else {
                                $items[] = ['type_id' => $imageType->id, 'is_hidpi' => false, 'position_id' => $positionId];
                            }
                        });

                        return $items;
                    })
                    ->minItems(0)
                    ->maxItems(function (Get $get): int {
                        $positionId = $get('position_id');
                        if (! $positionId) {
                            return 0;
                        }

                        return Position::find($positionId)?->imageTypes()->count() ?? 0;
                    })
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->itemLabel(function (array $state, Get $get): string {
                        $typeId = $state['type_id'] ?? null;
                        $isHidpi = $state['is_hidpi'] ?? false;
                        $positionId = $get('position_id');

                        if ($typeId && $positionId) {
                            $imageType = Position::find($positionId)?->imageTypes()->find($typeId);
                            if ($imageType) {
                                if ($imageType->image_width && $imageType->image_height) {
                                    if ($isHidpi) {
                                        $hidpiWidth = $imageType->image_width * 2;
                                        $hidpiHeight = $imageType->image_height * 2;

                                        return "{$imageType->name} @2x ({$hidpiWidth}×{$hidpiHeight}, displayed as {$imageType->image_width}×{$imageType->image_height})";
                                    } else {
                                        return "{$imageType->name} ({$imageType->image_width}×{$imageType->image_height})";
                                    }
                                }

                                return $imageType->name;
                            }
                        }

                        return 'Banner Image';
                    }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('position.name')
                    ->label('Position'),

                Infolists\Components\TextEntry::make('name'),

                Infolists\Components\TextEntry::make('link')
                    ->url(fn ($record) => $record->link)
                    ->openUrlInNewTab(fn ($record) => $record->new_tab),

                Infolists\Components\IconEntry::make('is_active')
                    ->boolean(),

                Infolists\Components\IconEntry::make('new_tab')
                    ->boolean()
                    ->label('Open in new tab'),

                Infolists\Components\Grid::make()
                    ->columnSpanFull()
                    ->schema(
                        fn (Banner $record) => $record->images->load('type')->map(
                            fn ($image) => Infolists\Components\ImageEntry::make("image_{$image->id}")
                                ->columnSpanFull()
                                ->label($image->type->name ?? 'Image')
                                ->width('100%')
                                ->height('auto')
                                ->getStateUsing(fn () => $image->getTranslation('file', app()->getLocale()))
                        )->toArray()
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('images.file')
                    ->circular()
                    ->stacked()
                    ->getStateUsing(function (Banner $record) {
                        $locale = app()->getLocale();

                        return $record->images->map(function ($image) use ($locale) {
                            return $image->getTranslation('file', $locale);
                        })->filter()->values()->toArray();
                    })
                    ->preview(true),

                Tables\Columns\TextColumn::make('position.name')
                    ->label('Position')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('link')
                    ->limit(30)
                    ->url(fn ($record) => $record->link)
                    ->openUrlInNewTab(fn ($record) => $record->new_tab),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('new_tab')
                    ->boolean()
                    ->icon(fn ($state): string => match ($state) {
                        true => 'heroicon-o-arrow-top-right-on-square',
                        false => 'heroicon-o-minus',
                    })
                    ->label('New Tab'),
            ])
            ->defaultSort('sort')
            ->filters([
                Tables\Filters\SelectFilter::make('position_id')
                    ->relationship('position', 'name')
                    ->label('Position'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->native(false),

                Tables\Filters\TernaryFilter::make('new_tab')
                    ->label('Open in New Tab')
                    ->boolean()
                    ->trueLabel('New tab only')
                    ->falseLabel('Same tab only')
                    ->native(false),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->groups(['position.name'])
            ->reorderable('sort')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('editPosition')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->label('Position')
                    ->url(fn (Model $record): string => BannerPositionResource::getUrl('edit', [
                        'record' => $record->position,
                    ])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'view' => Pages\ViewBanner::route('/{record}'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
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
