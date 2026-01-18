<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\HomeSectionResource\Pages;
use App\Models\HomeSection;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class HomeSectionResource extends Resource
{
    protected static ?string $model = HomeSection::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Secțiuni Homepage';

    protected static ?string $modelLabel = 'Secțiune Homepage';

    protected static ?string $pluralModelLabel = 'Secțiuni Homepage';

    protected static ?int $navigationSort = 15;

    public static function getNavigationGroup(): ?string
    {
        return 'Setări';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informații Secțiune')
                    ->schema([
                        Forms\Components\Select::make('section_type')
                            ->label('Tip Secțiune')
                            ->required()
                            ->options([
                                'product_categories' => 'Categorii Produse',
                                'custom_print' => 'Printare Personalizată',
                                'contact' => 'Contact',
                                'other' => 'Altele',
                            ])
                            ->native(false)
                            ->helperText('Tipul secțiunii de pe homepage'),

                        Forms\Components\TextInput::make('title')
                            ->label('Titlu')
                            ->maxLength(255)
                            ->helperText('Titlul secțiunii'),

                        Forms\Components\Textarea::make('description')
                            ->label('Descriere')
                            ->rows(3)
                            ->helperText('Descrierea secțiunii')
                            ->columnSpanFull(),

                        Forms\Components\KeyValue::make('settings')
                            ->label('Setări')
                            ->keyLabel('Cheie')
                            ->valueLabel('Valoare')
                            ->helperText('Setări specifice secțiunii (JSON)')
                            ->columnSpanFull(),
                    ])->columns(2),

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
                Tables\Columns\TextColumn::make('section_type')
                    ->label('Tip Secțiune')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'product_categories' => 'success',
                        'custom_print' => 'info',
                        'contact' => 'warning',
                        default => 'gray',
                    }),

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

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activ')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordine')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creat la')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('section_type')
                    ->label('Tip Secțiune')
                    ->options([
                        'product_categories' => 'Categorii Produse',
                        'custom_print' => 'Printare Personalizată',
                        'contact' => 'Contact',
                        'other' => 'Altele',
                    ])
                    ->placeholder('Toate'),

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
            'index' => Pages\ListHomeSections::route('/'),
            'create' => Pages\CreateHomeSection::route('/create'),
            'view' => Pages\ViewHomeSection::route('/{record}'),
            'edit' => Pages\EditHomeSection::route('/{record}/edit'),
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

