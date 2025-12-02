<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionResource\Pages;
use App\Models\Page;
use App\Models\Product;
use App\Models\Promotion;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Promoții';

    protected static ?string $modelLabel = 'Promoție';

    protected static ?string $pluralModelLabel = 'Promoții';

    protected static ?int $navigationSort = 12;

    public static function getNavigationGroup(): ?string
    {
        return 'Setări';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informații de bază')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titlu')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Descriere')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('banner')
                            ->label('Banner (URL sau cale)')
                            ->required()
                            ->maxLength(500)
                            ->helperText('URL-ul sau calea către imaginea banner-ului')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordine sortare')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Ordinea de afișare în carousel'),

                        Forms\Components\DateTimePicker::make('end_date')
                            ->label('Data expirării')
                            ->displayFormat('d.m.Y H:i')
                            ->helperText('Data când promoția expiră (opțional)')
                            ->minDate(now())
                            ->columnSpanFull(),
                    ]),

                Section::make('Link')
                    ->schema([
                        Forms\Components\TextInput::make('external_link')
                            ->label('Link extern')
                            ->url()
                            ->maxLength(500)
                            ->helperText('Link extern (opțional)')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('page_id')
                            ->label('Pagină')
                            ->relationship('page', 'title')
                            ->searchable()
                            ->preload()
                            ->helperText('Selectați o pagină (opțional)')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('product_id')
                            ->label('Produs')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Selectați un produs (opțional)')
                            ->columnSpanFull(),
                    ])
                    ->description('Specificați fie un link extern, fie o pagină, fie un produs')
                    ->collapsible(),

                Section::make('Setări')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activ')
                            ->default(true)
                            ->helperText('Promoția este activă și va fi afișată'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('banner')
                    ->label('Banner')
                    ->size(100)
                    ->circular(false),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titlu')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(50),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descriere')
                    ->limit(50)
                    ->toggleable()
                    ->tooltip(fn ($record) => $record->description),

                Tables\Columns\TextColumn::make('external_link')
                    ->label('Link extern')
                    ->limit(30)
                    ->toggleable()
                    ->copyable()
                    ->tooltip(fn ($record) => $record->external_link),

                Tables\Columns\TextColumn::make('page.title')
                    ->label('Pagină')
                    ->sortable()
                    ->toggleable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produs')
                    ->sortable()
                    ->toggleable()
                    ->default('—'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activ')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordine')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Expiră la')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->color(fn ($record) => $record->end_date && $record->end_date < now() ? 'danger' : null),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creat la')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizat la')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
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
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'view' => Pages\ViewPromotion::route('/{record}'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
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

