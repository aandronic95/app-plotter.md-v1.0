<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\HeaderContactResource\Pages;
use App\Models\HeaderContact;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class HeaderContactResource extends Resource
{
    protected static ?string $model = HeaderContact::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationLabel = 'Contacte Header';

    protected static ?string $modelLabel = 'Contact Header';

    protected static ?string $pluralModelLabel = 'Contacte Header';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Setări';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informații de contact')
                    ->description('Informațiile de contact afișate în header-ul site-ului')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titlu')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ex: Sales, Info, Office')
                            ->helperText('Titlul contactului (va fi afișat în admin)')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(50)
                            ->placeholder('+373 68 582 157')
                            ->helperText('Număr de telefon pentru contact'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('sales@plotter.md')
                            ->helperText('Adresă email pentru contact'),
                    ])->columns(2),

                Section::make('Setări')
                    ->schema([
                        Forms\Components\TextInput::make('order')
                            ->label('Ordine')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Ordinea de afișare (0 = primul)')
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activ')
                            ->helperText('Contact activ/inactiv')
                            ->default(true),
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

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('order')
                    ->label('Ordine')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activ')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),

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
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
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
            'index' => Pages\ListHeaderContacts::route('/'),
            'create' => Pages\CreateHeaderContact::route('/create'),
            'edit' => Pages\EditHeaderContact::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
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

