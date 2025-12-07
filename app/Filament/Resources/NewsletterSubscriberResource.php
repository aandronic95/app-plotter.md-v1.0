<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriberResource\Pages;
use App\Models\NewsletterSubscriber;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Abonați Newsletter';

    protected static ?string $modelLabel = 'Abonat Newsletter';

    protected static ?string $pluralModelLabel = 'Abonați Newsletter';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return 'Marketing';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informații abonat')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nume')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('079123456')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('privacy_accepted')
                            ->label('A acceptat politica de confidențialitate')
                            ->default(false)
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nume')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('privacy_accepted')
                    ->label('Politica acceptată')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data abonării')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizat la')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('privacy_accepted')
                    ->label('A acceptat politica')
                    ->query(fn (Builder $query): Builder => $query->where('privacy_accepted', true)),

                Filter::make('with_phone')
                    ->label('Cu telefon')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('phone')),

                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('De la'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Până la'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
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
                    Actions\BulkAction::make('export')
                        ->label('Exportă CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            $filename = 'newsletter_subscribers_' . date('Y-m-d_His') . '.csv';
                            
                            $csvData = [];
                            // BOM pentru UTF-8 (Excel compatibility)
                            $csvData[] = "\xEF\xBB\xBF";
                            // Headers
                            $csvData[] = implode(';', ['Nume', 'Email', 'Telefon', 'Politica acceptată', 'Data abonării']);
                            
                            // Data
                            foreach ($records as $record) {
                                $csvData[] = implode(';', [
                                    '"' . str_replace('"', '""', $record->name) . '"',
                                    '"' . str_replace('"', '""', $record->email) . '"',
                                    '"' . str_replace('"', '""', $record->phone ?? '') . '"',
                                    $record->privacy_accepted ? 'Da' : 'Nu',
                                    $record->created_at->format('d.m.Y H:i'),
                                ]);
                            }
                            
                            $content = implode("\n", $csvData);
                            
                            return response($content)
                                ->header('Content-Type', 'text/csv; charset=UTF-8')
                                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Exportă abonații')
                        ->modalDescription('Această acțiune va exporta toți abonații selectați într-un fișier CSV.')
                        ->modalSubmitActionLabel('Exportă'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Niciun abonat')
            ->emptyStateDescription('Abonații la newsletter vor apărea aici.')
            ->emptyStateIcon('heroicon-o-envelope');
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
            'index' => Pages\ListNewsletterSubscribers::route('/'),
            'view' => Pages\ViewNewsletterSubscriber::route('/{record}'),
            'edit' => Pages\EditNewsletterSubscriber::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Abonații se creează doar prin formularul public
    }

    public static function canViewAny(): bool
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

