<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CustomPrintBannerResource\Pages;
use App\Models\CustomPrintBanner;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CustomPrintBannerResource extends Resource
{
    protected static ?string $model = CustomPrintBanner::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-printer';

    protected static ?string $navigationLabel = 'Banner Printare Personalizată';

    protected static ?string $modelLabel = 'Banner Printare';

    protected static ?string $pluralModelLabel = 'Banner-uri Printare';

    protected static ?int $navigationSort = 14;

    public static function getNavigationGroup(): ?string
    {
        return 'Setări';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informații Banner')
                    ->schema([
                        Forms\Components\TextInput::make('headline')
                            ->label('Headline')
                            ->maxLength(255)
                            ->helperText('Textul principal (ex: "Hai să tipărim propriul tău produs !")')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('title')
                            ->label('Titlu')
                            ->maxLength(255)
                            ->helperText('Titlul banner-ului'),

                        Forms\Components\Textarea::make('description')
                            ->label('Descriere')
                            ->rows(4)
                            ->helperText('Descrierea banner-ului')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('button_text')
                            ->label('Text Buton')
                            ->default('Tipăreşte macheta mea')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('button_link')
                            ->label('Link Buton')
                            ->url()
                            ->maxLength(500)
                            ->helperText('Link-ul către care duce butonul')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('image')
                            ->label('Imagine')
                            ->image()
                            ->disk('public')
                            ->directory('custom-print-banners')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor()
                            ->helperText('Imaginea din partea dreaptă a banner-ului')
                            ->columnSpanFull(),

                        Forms\Components\ColorPicker::make('background_color')
                            ->label('Culoare Fundal')
                            ->default('#f0f0f0')
                            ->helperText('Culoarea de fundal a banner-ului'),
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

                Tables\Columns\TextColumn::make('headline')
                    ->label('Headline')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(50),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titlu')
                    ->searchable()
                    ->toggleable()
                    ->limit(30),

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
            'index' => Pages\ListCustomPrintBanners::route('/'),
            'create' => Pages\CreateCustomPrintBanner::route('/create'),
            'view' => Pages\ViewCustomPrintBanner::route('/{record}'),
            'edit' => Pages\EditCustomPrintBanner::route('/{record}/edit'),
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

