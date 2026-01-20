<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryMethodResource\Pages;
use App\Models\DeliveryMethod;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class DeliveryMethodResource extends Resource
{
    protected static ?string $model = DeliveryMethod::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Metode de livrare';

    protected static ?string $modelLabel = 'Metodă de livrare';

    protected static ?string $pluralModelLabel = 'Metode de livrare';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informații de bază')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nume')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->alphaDash(),

                        Forms\Components\Textarea::make('description')
                            ->label('Descriere')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('delivery-methods')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Costuri și setări')
                    ->schema([
                        Forms\Components\TextInput::make('base_cost')
                            ->label('Cost de bază')
                            ->required()
                            ->numeric()
                            ->prefix('LEI')
                            ->step(0.01)
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\TextInput::make('free_shipping_threshold')
                            ->label('Prag transport gratuit')
                            ->numeric()
                            ->prefix('LEI')
                            ->step(0.01)
                            ->helperText('Transport gratuit pentru comenzi peste această sumă')
                            ->nullable(),

                        Forms\Components\TextInput::make('estimated_days_min')
                            ->label('Zile estimare minim')
                            ->numeric()
                            ->minValue(1)
                            ->nullable(),

                        Forms\Components\TextInput::make('estimated_days_max')
                            ->label('Zile estimare maxim')
                            ->numeric()
                            ->minValue(1)
                            ->nullable(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activ')
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordine sortare')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nume')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('base_cost')
                    ->label('Cost de bază')
                    ->money('LEI')
                    ->sortable(),

                Tables\Columns\TextColumn::make('free_shipping_threshold')
                    ->label('Prag transport gratuit')
                    ->money('LEI')
                    ->default('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimated_days')
                    ->label('Zile estimare')
                    ->formatStateUsing(function ($record) {
                        if (!$record->estimated_days_min && !$record->estimated_days_max) {
                            return '—';
                        }
                        if ($record->estimated_days_min && $record->estimated_days_max) {
                            return "{$record->estimated_days_min}-{$record->estimated_days_max} zile";
                        }
                        return $record->estimated_days_min ? "{$record->estimated_days_min}+ zile" : "{$record->estimated_days_max} zile";
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activ')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordine')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Nr. comenzi')
                    ->counts('orders')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data creării')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        true => 'Activ',
                        false => 'Inactiv',
                    ]),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryMethods::route('/'),
            'create' => Pages\CreateDeliveryMethod::route('/create'),
            'view' => Pages\ViewDeliveryMethod::route('/{record}'),
            'edit' => Pages\EditDeliveryMethod::route('/{record}/edit'),
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

