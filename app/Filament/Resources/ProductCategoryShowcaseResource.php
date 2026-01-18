<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductCategoryShowcaseResource\Pages;
use App\Models\Category;
use App\Models\ProductCategoryShowcase;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProductCategoryShowcaseResource extends Resource
{
    protected static ?string $model = ProductCategoryShowcase::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Showcase Categorii Produse';

    protected static ?string $modelLabel = 'Showcase Categorie';

    protected static ?string $pluralModelLabel = 'Showcase Categorii';

    protected static ?int $navigationSort = 13;

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
                        Forms\Components\TextInput::make('section_title')
                            ->label('Titlu Secțiune')
                            ->maxLength(255)
                            ->helperText('Titlul principal al secțiunii (ex: "TIPURI DE PRODUSE")')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('section_description')
                            ->label('Descriere Secțiune')
                            ->rows(3)
                            ->helperText('Descrierea secțiunii principale')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('carousel_banner_image')
                            ->label('Imagine Banner Carousel')
                            ->image()
                            ->disk('public')
                            ->directory('category-showcases/banners')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor()
                            ->helperText('Imaginea banner-ului pentru primul element din carousel (setată din admin)')
                            ->columnSpanFull(),
                    ]),

                Section::make('Informații Card')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nume Card')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Numele cardului (ex: "Sacoșe", "Pachete")'),

                        Forms\Components\TextInput::make('subtitle')
                            ->label('Subtitlu')
                            ->maxLength(255)
                            ->helperText('Subtitlul cardului (opțional)'),

                        Forms\Components\Select::make('category_id')
                            ->label('Categorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Categoria asociată acestui card'),

                        Forms\Components\FileUpload::make('image')
                            ->label('Imagine')
                            ->image()
                            ->disk('public')
                            ->directory('category-showcases')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor()
                            ->helperText('Imaginea pentru card')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('button_text')
                            ->label('Text Buton')
                            ->default('VEZI TOT CATALOGUL >')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('button_link')
                            ->label('Link Buton')
                            ->url()
                            ->maxLength(500)
                            ->helperText('Link-ul către care duce butonul'),
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
                Tables\Columns\ImageColumn::make('image')
                    ->label('Imagine')
                    ->size(100)
                    ->circular(false),

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
                    ->color('info')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('button_text')
                    ->label('Text Buton')
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
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categorie')
                    ->relationship('category', 'name')
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
            'index' => Pages\ListProductCategoryShowcases::route('/'),
            'create' => Pages\CreateProductCategoryShowcase::route('/create'),
            'view' => Pages\ViewProductCategoryShowcase::route('/{record}'),
            'edit' => Pages\EditProductCategoryShowcase::route('/{record}/edit'),
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

