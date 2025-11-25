<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NavigationResource\Pages;
use App\Models\Navigation;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class NavigationResource extends Resource
{
    protected static ?string $model = Navigation::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bars-3-bottom-left';

    protected static ?string $navigationLabel = 'Navigare';

    protected static ?string $modelLabel = 'Element Navigare';

    protected static ?string $pluralModelLabel = 'Elemente Navigare';

    protected static ?int $navigationSort = 10;

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

                        Forms\Components\TextInput::make('href')
                            ->label('Link (URL)')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('/dashboard sau https://example.com')
                            ->helperText('Link intern (ex: /dashboard) sau extern (ex: https://example.com)')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('icon')
                            ->label('Iconiță')
                            ->maxLength(255)
                            ->placeholder('LayoutGrid sau numele componentei icon')
                            ->helperText('Numele componentei icon din lucide-vue-next (ex: LayoutGrid, Home, etc.)')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('group')
                            ->label('Grup')
                            ->required()
                            ->options([
                                'main' => 'Principal (Sidebar)',
                                'footer' => 'Footer',
                                'header' => 'Header',
                            ])
                            ->default('main')
                            ->native(false)
                            ->live(),

                        Forms\Components\Select::make('category')
                            ->label('Categorie (pentru Footer)')
                            ->options([
                                'company' => 'Companie',
                                'customer' => 'Servicii Clienți',
                                'legal' => 'Legal',
                            ])
                            ->visible(fn (Get $get) => $get('group') === 'footer')
                            ->native(false)
                            ->helperText('Selectează categoria doar pentru elementele din footer'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordine sortare')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Descriere')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Setări avansate')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activ')
                            ->default(true)
                            ->helperText('Elementul va fi afișat în navigare'),

                        Forms\Components\Toggle::make('is_external')
                            ->label('Link extern')
                            ->default(false)
                            ->helperText('Bifează dacă link-ul este extern'),

                        Forms\Components\Select::make('target')
                            ->label('Target')
                            ->options([
                                '_self' => 'Aceeași fereastră (_self)',
                                '_blank' => 'Fereastră nouă (_blank)',
                                '_parent' => 'Fereastră părinte (_parent)',
                                '_top' => 'Fereastră top (_top)',
                            ])
                            ->default('_self')
                            ->native(false)
                            ->helperText('Cum se deschide link-ul'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titlu')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('href')
                    ->label('Link')
                    ->searchable()
                    ->copyable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->href),

                Tables\Columns\TextColumn::make('icon')
                    ->label('Iconiță')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('group')
                    ->label('Grup')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'main' => 'primary',
                        'footer' => 'success',
                        'header' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'main' => 'Principal',
                        'footer' => 'Footer',
                        'header' => 'Header',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activ')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_external')
                    ->label('Extern')
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
                Tables\Filters\SelectFilter::make('group')
                    ->label('Grup')
                    ->options([
                        'main' => 'Principal',
                        'footer' => 'Footer',
                        'header' => 'Header',
                    ])
                    ->placeholder('Toate'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activ')
                    ->placeholder('Toate')
                    ->trueLabel('Doar active')
                    ->falseLabel('Doar inactive'),

                Tables\Filters\TernaryFilter::make('is_external')
                    ->label('Link extern')
                    ->placeholder('Toate')
                    ->trueLabel('Doar externe')
                    ->falseLabel('Doar interne'),
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
            'index' => Pages\ListNavigations::route('/'),
            'create' => Pages\CreateNavigation::route('/create'),
            'view' => Pages\ViewNavigation::route('/{record}'),
            'edit' => Pages\EditNavigation::route('/{record}/edit'),
        ];
    }
}

