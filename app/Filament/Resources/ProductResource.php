<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Produse';

    protected static ?string $modelLabel = 'Produs';

    protected static ?string $pluralModelLabel = 'Produse';

    protected static ?int $navigationSort = 1;

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

                        Forms\Components\Select::make('category_id')
                            ->label('Categorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nume')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Category::class, 'slug')
                                    ->alphaDash(),
                            ]),

                        Forms\Components\Textarea::make('short_description')
                            ->label('Descriere scurtă')
                            ->rows(3)
                            ->maxLength(500),

                        Forms\Components\RichEditor::make('description')
                            ->label('Descriere')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Preț și stoc')
                    ->schema([
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('price')
                            ->label('Preț')
                            ->required()
                            ->numeric()
                            ->prefix('LEI')
                            ->step(0.01)
                            ->minValue(0),

                        Forms\Components\TextInput::make('original_price')
                            ->label('Preț original')
                            ->numeric()
                            ->prefix('LEI')
                            ->step(0.01)
                            ->minValue(0)
                            ->helperText('Dacă este setat, se va calcula automat discountul'),

                        Forms\Components\TextInput::make('discount_percentage')
                            ->label('Discount (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),

                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Cantitate în stoc')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\Toggle::make('in_stock')
                            ->label('În stoc')
                            ->default(true),

                    ])->columns(3),

                Section::make('Imagini')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Imagine principală')
                            ->image()
                            ->disk('public')
                            ->directory(fn ($record) => $record && $record->slug
                                ? "products/{$record->slug}" 
                                : 'products/temp')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']),

                        Forms\Components\FileUpload::make('images')
                            ->label('Imagini suplimentare')
                            ->image()
                            ->disk('public')
                            ->directory(fn ($record) => $record && $record->slug
                                ? "products/{$record->slug}" 
                                : 'products/temp')
                            ->visibility('public')
                            ->multiple()
                            ->maxFiles(10)
                            ->maxSize(5120)
                            ->imageEditor()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']),
                    ])->columns(2),

                Section::make('Setări')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activ')
                            ->default(true),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Produs recomandat')
                            ->default(false),

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
                Tables\Columns\ImageColumn::make('image')
                    ->label('Imagine')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nume')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categorie')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preț')
                    ->money('LEI')
                    ->sortable(),

                Tables\Columns\TextColumn::make('original_price')
                    ->label('Preț original')
                    ->money('LEI')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stoc')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->stock_quantity > 10 ? 'success' : ($record->stock_quantity > 0 ? 'warning' : 'danger')),

                Tables\Columns\IconColumn::make('in_stock')
                    ->label('În stoc')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activ')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Recomandat')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creat la')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categorie')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activ')
                    ->placeholder('Toate')
                    ->trueLabel('Doar active')
                    ->falseLabel('Doar inactive'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Recomandat')
                    ->placeholder('Toate')
                    ->trueLabel('Doar recomandate')
                    ->falseLabel('Doar nerecomandate'),

                Tables\Filters\TernaryFilter::make('in_stock')
                    ->label('În stoc')
                    ->placeholder('Toate')
                    ->trueLabel('Doar în stoc')
                    ->falseLabel('Doar fără stoc'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canUpdate($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}

