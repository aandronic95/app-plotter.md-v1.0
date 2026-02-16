<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Comenzi';

    protected static ?string $modelLabel = 'Comandă';

    protected static ?string $pluralModelLabel = 'Comenzi';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['orderItems', 'deliveryMethod']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informații comandă')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('Număr comandă')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('user_id')
                            ->label('Utilizator')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'În așteptare',
                                'processing' => 'În procesare',
                                'shipped' => 'Expediată',
                                'delivered' => 'Livrată',
                                'cancelled' => 'Anulată',
                            ])
                            ->required()
                            ->default('pending'),

                        Forms\Components\Select::make('payment_status')
                            ->label('Status plată')
                            ->options([
                                'pending' => 'În așteptare',
                                'paid' => 'Plătită',
                                'failed' => 'Eșuată',
                                'refunded' => 'Rambursată',
                            ])
                            ->required()
                            ->default('pending'),

                        Forms\Components\TextInput::make('payment_method')
                            ->label('Metodă plată')
                            ->maxLength(255),

                        Forms\Components\Select::make('delivery_method_id')
                            ->label('Metodă de livrare')
                            ->relationship('deliveryMethod', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\TextInput::make('delivery_tracking_number')
                            ->label('Număr AWB / Tracking')
                            ->maxLength(255)
                            ->nullable(),
                    ])->columns(3),

                Section::make('Detalii financiare')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->required()
                            ->numeric()
                            ->prefix('LEI')
                            ->step(0.01)
                            ->minValue(0),

                        Forms\Components\TextInput::make('tax')
                            ->label('TVA')
                            ->numeric()
                            ->prefix('LEI')
                            ->step(0.01)
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\TextInput::make('shipping_cost')
                            ->label('Cost livrare')
                            ->numeric()
                            ->prefix('LEI')
                            ->step(0.01)
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\TextInput::make('total')
                            ->label('Total')
                            ->required()
                            ->numeric()
                            ->prefix('LEI')
                            ->step(0.01)
                            ->minValue(0),
                    ])->columns(4),

                Section::make('Detalii livrare')
                    ->schema([
                        Forms\Components\TextInput::make('shipping_name')
                            ->label('Nume')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('shipping_email')
                            ->label('Email')
                            ->required()
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('shipping_phone')
                            ->label('Telefon')
                            ->required()
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('shipping_address')
                            ->label('Adresă')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('shipping_city')
                            ->label('Oraș')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('shipping_postal_code')
                            ->label('Cod poștal')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('shipping_country')
                            ->label('Țară')
                            ->required()
                            ->maxLength(255)
                            ->default('Republica Moldova'),
                    ])->columns(3),

                Section::make('Produse comandate')
                    ->schema([
                        Forms\Components\Repeater::make('orderItems')
                            ->label('Produse')
                            ->relationship('orderItems')
                            ->orderColumn('id')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produs')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->disabled()
                                    ->dehydrated(),
                                
                                Forms\Components\TextInput::make('product_name')
                                    ->label('Nume produs')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled(fn ($context) => $context === 'view'),
                                
                                Forms\Components\TextInput::make('product_sku')
                                    ->label('SKU')
                                    ->maxLength(255)
                                    ->disabled(fn ($context) => $context === 'view'),
                                
                                Forms\Components\Select::make('print_size')
                                    ->label('Dimensiune')
                                    ->options([
                                        'A0' => 'A0',
                                        'A1' => 'A1',
                                        'A2' => 'A2',
                                        'A3' => 'A3 (420x297 mm)',
                                        'A4' => 'A4 (297x210 mm)',
                                        'A5' => 'A5',
                                        'A6' => 'A6',
                                        'Personalizat' => 'Personalizat',
                                    ])
                                    ->nullable()
                                    ->disabled(fn ($context) => $context === 'view')
                                    ->native(false),
                                
                                Forms\Components\Select::make('print_sides')
                                    ->label('Laturi de printare')
                                    ->options([
                                        '4+0' => '1-сторонняя печать (4+0)',
                                        '4+4' => '2-сторонняя печать (4+4)',
                                        '5+0' => '5+0',
                                        '5+5' => '5+5',
                                    ])
                                    ->nullable()
                                    ->disabled(fn ($context) => $context === 'view')
                                    ->native(false),
                                
                                Forms\Components\TextInput::make('format')
                                    ->label('Format')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->disabled(fn ($context) => $context === 'view'),
                                
                                Forms\Components\TextInput::make('suport')
                                    ->label('Suport')
                                    ->maxLength(100)
                                    ->nullable()
                                    ->disabled(fn ($context) => $context === 'view'),
                                
                                Forms\Components\TextInput::make('culoare')
                                    ->label('Culoare')
                                    ->maxLength(100)
                                    ->nullable()
                                    ->disabled(fn ($context) => $context === 'view'),
                                
                                Forms\Components\TextInput::make('colturi')
                                    ->label('Colțuri')
                                    ->maxLength(100)
                                    ->nullable()
                                    ->disabled(fn ($context) => $context === 'view'),
                                
                                Forms\Components\TextInput::make('configuration_quantity')
                                    ->label('Cantitate configurație')
                                    ->numeric()
                                    ->minValue(1)
                                    ->nullable()
                                    ->disabled(fn ($context) => $context === 'view')
                                    ->helperText('Cantitatea din configurația selectată (500, 1000, 2000)'),
                                
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantitate')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->disabled(fn ($context) => $context === 'view')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $price = (float) ($get('price') ?? 0);
                                        $quantity = (int) ($state ?? 1);
                                        $set('subtotal', number_format($price * $quantity, 2, '.', ''));
                                    }),
                                
                                Forms\Components\TextInput::make('price')
                                    ->label('Preț unitar')
                                    ->required()
                                    ->numeric()
                                    ->prefix('LEI')
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->disabled(fn ($context) => $context === 'view')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $price = (float) ($state ?? 0);
                                        $quantity = (int) ($get('quantity') ?? 1);
                                        $set('subtotal', number_format($price * $quantity, 2, '.', ''));
                                    }),
                                
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('LEI')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0)
                                    ->formatStateUsing(fn ($state) => $state ? number_format((float) $state, 2, '.', '') : '0.00'),
                            ])
                            ->columns(3)
                            ->addable(fn ($context) => $context !== 'view')
                            ->deletable(fn ($context) => $context !== 'view')
                            ->reorderable(fn ($context) => $context !== 'view')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['product_name'] ?? null),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed(false),

                Section::make('Notițe')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notițe')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('orderItems'))
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Număr comandă')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilizator')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'În așteptare',
                        'processing' => 'În procesare',
                        'shipped' => 'Expediată',
                        'delivered' => 'Livrată',
                        'cancelled' => 'Anulată',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status plată')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'În așteptare',
                        'paid' => 'Plătită',
                        'failed' => 'Eșuată',
                        'refunded' => 'Rambursată',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('deliveryMethod.name')
                    ->label('Metodă livrare')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('delivery_tracking_number')
                    ->label('AWB / Tracking')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('LEI')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('orderItems_count')
                    ->label('Nr. produse')
                    ->counts('orderItems')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('orderItems')
                    ->label('Produse comandate')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record || !$record->relationLoaded('orderItems')) {
                            return '—';
                        }
                        $items = $record->orderItems;
                        if ($items->isEmpty()) {
                            return '—';
                        }
                        return $items->map(function ($item) {
                            $name = ($item->product_name ?? 'N/A') . ' (x' . ($item->quantity ?? 0) . ')';
                            if ($item->print_size || $item->print_sides || $item->format || $item->suport || $item->culoare || $item->colturi || $item->configuration_quantity) {
                                $config = [];
                                if ($item->print_size) $config[] = $item->print_size;
                                if ($item->print_sides) $config[] = $item->print_sides;
                                if ($item->format) $config[] = 'Format: ' . $item->format;
                                if ($item->suport) $config[] = 'Suport: ' . $item->suport;
                                if ($item->culoare) $config[] = 'Culoare: ' . $item->culoare;
                                if ($item->colturi) $config[] = 'Colțuri: ' . $item->colturi;
                                if ($item->configuration_quantity) $config[] = $item->configuration_quantity . ' buc';
                                if (!empty($config)) {
                                    $name .= ' [' . implode(', ', $config) . ']';
                                }
                            }
                            return $name;
                        })->join(', ');
                    })
                    ->wrap()
                    ->limit(50)
                    ->tooltip(function ($state, $record) {
                        if (!$record || !$record->relationLoaded('orderItems')) {
                            return null;
                        }
                        $items = $record->orderItems;
                        if ($items->isEmpty()) {
                            return null;
                        }
                        return $items->map(function ($item) {
                            $name = ($item->product_name ?? 'N/A') . ' (x' . ($item->quantity ?? 0) . ')';
                            if ($item->print_size || $item->print_sides || $item->format || $item->suport || $item->culoare || $item->colturi || $item->configuration_quantity) {
                                $config = [];
                                if ($item->print_size) $config[] = $item->print_size;
                                if ($item->print_sides) $config[] = $item->print_sides;
                                if ($item->format) $config[] = 'Format: ' . $item->format;
                                if ($item->suport) $config[] = 'Suport: ' . $item->suport;
                                if ($item->culoare) $config[] = 'Culoare: ' . $item->culoare;
                                if ($item->colturi) $config[] = 'Colțuri: ' . $item->colturi;
                                if ($item->configuration_quantity) $config[] = $item->configuration_quantity . ' buc';
                                if (!empty($config)) {
                                    $name .= ' [' . implode(', ', $config) . ']';
                                }
                            }
                            return $name;
                        })->join(', ');
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data comandă')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'În așteptare',
                        'processing' => 'În procesare',
                        'shipped' => 'Expediată',
                        'delivered' => 'Livrată',
                        'cancelled' => 'Anulată',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status plată')
                    ->options([
                        'pending' => 'În așteptare',
                        'paid' => 'Plătită',
                        'failed' => 'Eșuată',
                        'refunded' => 'Rambursată',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->label('Data comandă')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('De la'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Până la'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn ($query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn ($query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
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

