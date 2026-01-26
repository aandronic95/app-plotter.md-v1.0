<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductConfigurationResource\Pages;
use App\Models\ProductConfiguration;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProductConfigurationResource extends Resource
{
    protected static ?string $model = ProductConfiguration::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Configurații Produse';

    protected static ?string $modelLabel = 'Configurație Produs';

    protected static ?string $pluralModelLabel = 'Configurații Produse';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Produse';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informații de bază')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Produs')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($context) => $context === 'edit'),

                        Forms\Components\Select::make('print_size')
                            ->label('Dimensiune')
                            ->options([
                                'A3' => 'A3 (420x297 mm)',
                                'A4' => 'A4 (297x210 mm)',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('print_sides')
                            ->label('Laturi de printare')
                            ->options([
                                '4+0' => '1-сторонняя печать (4+0)',
                                '4+4' => '2-сторонняя печать (4+4)',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Cantitate (buc)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(500),
                    ])->columns(2),

                Section::make('Prețuri')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Preț total')
                            ->required()
                            ->numeric()
                            ->prefix('LEI')
                            ->step(0.01)
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                $quantity = (float) $get('quantity');
                                if ($quantity > 0 && $state) {
                                    $pricePerUnit = (float) $state / $quantity;
                                    $set('price_per_unit', round($pricePerUnit, 2));
                                }
                            }),

                        Forms\Components\TextInput::make('price_per_unit')
                            ->label('Preț per bucată')
                            ->required()
                            ->numeric()
                            ->prefix('LEI')
                            ->step(0.01)
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                $quantity = (float) $get('quantity');
                                if ($quantity > 0 && $state) {
                                    $totalPrice = (float) $state * $quantity;
                                    $set('price', round($totalPrice, 2));
                                }
                            }),

                        Forms\Components\TextInput::make('production_days')
                            ->label('Zile de producție')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(5)
                            ->helperText('Numărul de zile necesare pentru producție'),
                    ])->columns(3),

                Section::make('Setări')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activ')
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordine sortare')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produs')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('print_size')
                    ->label('Dimensiune')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('print_sides')
                    ->label('Laturi')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '4+0' => '1-сторонняя (4+0)',
                        '4+4' => '2-сторонняя (4+4)',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantitate')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', '.') . ' buc'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preț total')
                    ->money('P', locale: 'ro')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price_per_unit')
                    ->label('Preț/buc')
                    ->money('P', locale: 'ro')
                    ->sortable(),

                Tables\Columns\TextColumn::make('production_days')
                    ->label('Zile producție')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => $state . ' zile'),

                Tables\Columns\TextColumn::make('production_date')
                    ->label('Data producției')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activ')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creat la')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Produs')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('print_size')
                    ->label('Dimensiune')
                    ->options([
                        'A3' => 'A3',
                        'A4' => 'A4',
                    ]),

                Tables\Filters\SelectFilter::make('print_sides')
                    ->label('Laturi')
                    ->options([
                        '4+0' => '1-сторонняя (4+0)',
                        '4+4' => '2-сторонняя (4+4)',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activ')
                    ->placeholder('Toate')
                    ->trueLabel('Doar active')
                    ->falseLabel('Doar inactive'),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\BulkAction::make('mark_active')
                        ->label('Marchează ca activ')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),
                    Actions\BulkAction::make('mark_inactive')
                        ->label('Marchează ca inactiv')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductConfigurations::route('/'),
            'create' => Pages\CreateProductConfiguration::route('/create'),
            'view' => Pages\ViewProductConfiguration::route('/{record}'),
            'edit' => Pages\EditProductConfiguration::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canUpdate($record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
}

