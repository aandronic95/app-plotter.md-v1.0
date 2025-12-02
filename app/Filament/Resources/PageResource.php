<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Pagini';

    protected static ?string $modelLabel = 'Pagină';

    protected static ?string $pluralModelLabel = 'Pagini';

    protected static ?int $navigationSort = 11;

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
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->helperText('URL-ul paginii (ex: despre-noi)')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('excerpt')
                            ->label('Rezumat')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Scurtă descriere a paginii')
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('content')
                            ->label('Conținut')
                            ->toolbarButtons([
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('SEO')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta Titlu')
                            ->maxLength(255)
                            ->helperText('Titlul pentru motoarele de căutare')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta Descriere')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Descrierea pentru motoarele de căutare')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('meta_keywords')
                            ->label('Meta Cuvinte Cheie')
                            ->maxLength(255)
                            ->helperText('Cuvinte cheie separate prin virgulă')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Setări')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activ')
                            ->default(true)
                            ->helperText('Pagina este activă'),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Publicat')
                            ->default(false)
                            ->helperText('Pagina este publicată și vizibilă publicului'),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Data publicării')
                            ->displayFormat('d.m.Y H:i')
                            ->helperText('Data când pagina va fi publicată')
                            ->nullable(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordine sortare')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Ordinea de afișare în listă'),
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
                    ->weight('medium')
                    ->limit(50),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->slug),

                Tables\Columns\TextColumn::make('excerpt')
                    ->label('Rezumat')
                    ->limit(50)
                    ->toggleable()
                    ->tooltip(fn ($record) => $record->excerpt),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activ')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publicat')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicat la')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordine')
                    ->sortable()
                    ->toggleable(),

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

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Publicat')
                    ->placeholder('Toate')
                    ->trueLabel('Doar publicate')
                    ->falseLabel('Doar nepublicate'),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'view' => Pages\ViewPage::route('/{record}'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
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

