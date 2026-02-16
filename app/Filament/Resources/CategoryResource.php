<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Categorii';

    protected static ?string $modelLabel = 'Categorie';

    protected static ?string $pluralModelLabel = 'Categorii';

    protected static ?int $navigationSort = 2;

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

                        Forms\Components\Select::make('parent_id')
                            ->label('Categorie părinte')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Lăsați gol pentru o categorie principală'),

                        Forms\Components\Textarea::make('description')
                            ->label('Descriere')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('image')
                            ->label('Imagine')
                            ->image()
                            ->disk('public')
                            ->directory('categories')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Configurații Categorie')
                    ->schema([
                        // Formate
                        Forms\Components\Repeater::make('formats')
                            ->label('Formate')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Denumire')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('ex: 90x50 mm, Frontlit, etc.'),

                                Forms\Components\FileUpload::make('image')
                                    ->label('Imagine')
                                    ->image()
                                    ->disk('public')
                                    ->directory('categories/configurations/formats')
                                    ->visibility('public')
                                    ->maxSize(2048)
                                    ->imageEditor()
                                    ->deletable()
                                    ->downloadable()
                                    ->previewable()
                                    ->openable()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),

                                Forms\Components\Textarea::make('description')
                                    ->label('Descriere')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->placeholder('Descriere opțională'),
                            ])
                            ->columns(3)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->defaultItems(0)
                            ->addActionLabel('Adaugă format')
                            ->reorderableWithButtons()
                            ->cloneable()
                            ->deletable(),

                        // Suport
                        Forms\Components\Repeater::make('suport')
                            ->label('Suport')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Denumire')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('ex: Carton Premium, PVC, etc.'),

                                Forms\Components\FileUpload::make('image')
                                    ->label('Imagine')
                                    ->image()
                                    ->disk('public')
                                    ->directory('categories/configurations/suport')
                                    ->visibility('public')
                                    ->maxSize(2048)
                                    ->imageEditor()
                                    ->deletable()
                                    ->downloadable()
                                    ->previewable()
                                    ->openable()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),

                                Forms\Components\Textarea::make('description')
                                    ->label('Descriere')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->placeholder('Descriere opțională'),
                            ])
                            ->columns(3)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->defaultItems(0)
                            ->addActionLabel('Adaugă suport')
                            ->reorderableWithButtons()
                            ->cloneable()
                            ->deletable(),

                        // Culoare
                        Forms\Components\Repeater::make('culoare')
                            ->label('Culoare')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Denumire')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('ex: Alb, Negru, Roșu, etc.'),

                                Forms\Components\FileUpload::make('image')
                                    ->label('Imagine')
                                    ->image()
                                    ->disk('public')
                                    ->directory('categories/configurations/culoare')
                                    ->visibility('public')
                                    ->maxSize(2048)
                                    ->imageEditor()
                                    ->deletable()
                                    ->downloadable()
                                    ->previewable()
                                    ->openable()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),

                                Forms\Components\Textarea::make('description')
                                    ->label('Descriere')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->placeholder('Descriere opțională'),
                            ])
                            ->columns(3)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->defaultItems(0)
                            ->addActionLabel('Adaugă culoare')
                            ->reorderableWithButtons()
                            ->cloneable()
                            ->deletable(),

                        // Colturi
                        Forms\Components\Repeater::make('colturi')
                            ->label('Colțuri')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Denumire')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('ex: Drepte, Rotunjite, etc.'),

                                Forms\Components\FileUpload::make('image')
                                    ->label('Imagine')
                                    ->image()
                                    ->disk('public')
                                    ->directory('categories/configurations/colturi')
                                    ->visibility('public')
                                    ->maxSize(2048)
                                    ->imageEditor()
                                    ->deletable()
                                    ->downloadable()
                                    ->previewable()
                                    ->openable()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),

                                Forms\Components\Textarea::make('description')
                                    ->label('Descriere')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->placeholder('Descriere opțională'),
                            ])
                            ->columns(3)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->defaultItems(0)
                            ->addActionLabel('Adaugă colțuri')
                            ->reorderableWithButtons()
                            ->cloneable()
                            ->deletable(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

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
                Tables\Columns\ImageColumn::make('image')
                    ->label('Imagine')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nume')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Categorie părinte')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Produse')
                    ->counts('products')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('formats')
                    ->label('Formate')
                    ->formatStateUsing(fn ($state): string => 
                        is_array($state) && count($state) > 0
                            ? implode(', ', array_column($state, 'name'))
                            : '—'
                    )
                    ->badge()
                    ->color('info')
                    ->toggleable(),

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
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Categorie părinte')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
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
            ->defaultSort('name');
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
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

