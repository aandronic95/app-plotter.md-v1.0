<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SiteSettingResource\Pages;
use App\Models\SiteSetting;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Setări Site';

    protected static ?string $modelLabel = 'Setări Site';

    protected static ?string $pluralModelLabel = 'Setări Site';

    protected static ?int $navigationSort = 1;

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
                        Forms\Components\TextInput::make('site_name')
                            ->label('Nume site')
                            ->required()
                            ->maxLength(255)
                            ->default('PLOTTER.MD')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('site_description')
                            ->label('Descriere site')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('site_logo')
                            ->label('Logo (imagine completă)')
                            ->image()
                            ->disk('public')
                            ->directory('site')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor()
                            ->helperText('Logo-ul principal al site-ului')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('site_logo_icon')
                            ->label('Logo Icon (SVG/Iconiță)')
                            ->image()
                            ->disk('public')
                            ->directory('site')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->imageEditor()
                            ->helperText('Iconița mică pentru logo (recomandat SVG)')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('site_favicon')
                            ->label('Favicon')
                            ->image()
                            ->disk('public')
                            ->directory('site')
                            ->visibility('public')
                            ->maxSize(512)
                            ->imageEditor()
                            ->helperText('Favicon pentru browser (recomandat .ico sau .png 32x32)')
                            ->columnSpanFull(),
                    ]),

                Section::make('Informații de contact')
                    ->schema([
                        Forms\Components\TextInput::make('site_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('site_phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(50),

                        Forms\Components\Textarea::make('site_address')
                            ->label('Adresă')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Contacte Header')
                    ->description('Informațiile de contact afișate în header-ul site-ului')
                    ->schema([
                        Forms\Components\TextInput::make('header_contact_1_phone')
                            ->label('Telefon Contact 1 (Sales)')
                            ->tel()
                            ->maxLength(50)
                            ->placeholder('+373 68 582 157')
                            ->helperText('Telefon pentru contactul de vânzări'),
                        Forms\Components\TextInput::make('header_contact_1_email')
                            ->label('Email Contact 1 (Sales)')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('sales@plotter.md')
                            ->helperText('Email pentru contactul de vânzări'),
                        Forms\Components\TextInput::make('header_contact_2_phone')
                            ->label('Telefon Contact 2 (Info)')
                            ->tel()
                            ->maxLength(50)
                            ->placeholder('+373 60 169 285')
                            ->helperText('Telefon pentru informații'),
                        Forms\Components\TextInput::make('header_contact_2_email')
                            ->label('Email Contact 2 (Info)')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('info@plotter.md')
                            ->helperText('Email pentru informații'),
                        Forms\Components\TextInput::make('header_contact_3_phone')
                            ->label('Telefon Contact 3 (Office)')
                            ->tel()
                            ->maxLength(50)
                            ->placeholder('+373 60 169 285')
                            ->helperText('Telefon pentru birou'),
                        Forms\Components\TextInput::make('header_contact_3_email')
                            ->label('Email Contact 3 (Office)')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('office@plotter.md')
                            ->helperText('Email pentru birou'),
                    ])->columns(2),

                Section::make('Rețele sociale')
                    ->schema([
                        Forms\Components\TextInput::make('site_facebook')
                            ->label('Facebook URL')
                            ->url()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-link'),

                        Forms\Components\TextInput::make('site_instagram')
                            ->label('Instagram URL')
                            ->url()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-link'),

                        Forms\Components\TextInput::make('site_twitter')
                            ->label('Twitter/X URL')
                            ->url()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-link'),

                        Forms\Components\TextInput::make('site_linkedin')
                            ->label('LinkedIn URL')
                            ->url()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-link'),
                    ])->columns(2),

                Section::make('SEO & Analytics')
                    ->schema([
                        Forms\Components\Textarea::make('site_meta_keywords')
                            ->label('Meta Keywords')
                            ->rows(2)
                            ->maxLength(500)
                            ->helperText('Cuvinte cheie separate prin virgulă')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('site_meta_description')
                            ->label('Meta Description')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Descriere pentru motoarele de căutare')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('site_google_analytics')
                            ->label('Google Analytics ID')
                            ->maxLength(255)
                            ->placeholder('G-XXXXXXXXXX')
                            ->helperText('ID-ul de tracking Google Analytics')
                            ->columnSpanFull(),
                    ]),

                Section::make('Setări utilizator')
                    ->schema([
                        Forms\Components\Toggle::make('show_login_modal')
                            ->label('Afișează modal după logare')
                            ->helperText('Afișează modalul cu beneficii după autentificarea utilizatorilor')
                            ->default(true)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('show_site_name')
                            ->label('Afișează numele site-ului')
                            ->helperText('Afișează numele site-ului în header')
                            ->default(true)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('show_logo')
                            ->label('Afișează logo-ul')
                            ->helperText('Afișează logo-ul în header')
                            ->default(true)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('show_loyalty_points')
                            ->label('Afișează punctele de loialitate')
                            ->helperText('Afișează card-ul cu punctele de loialitate în profilul utilizatorului')
                            ->default(true)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('show_newsletter_form')
                            ->label('Afișează formularul de newsletter')
                            ->helperText('Afișează formularul de abonare la newsletter pe pagina principală')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),

                Section::make('Loading Screen')
                    ->schema([
                        Forms\Components\TextInput::make('loading_text_main')
                            ->label('Text principal loading')
                            ->maxLength(255)
                            ->default('tanavius')
                            ->helperText('Textul principal afișat pe ecranul de loading')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('loading_text_sub')
                            ->label('Text secundar loading')
                            ->maxLength(255)
                            ->default('www.plotter.md')
                            ->helperText('Textul secundar afișat sub textul principal')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('site_logo')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                Tables\Columns\TextColumn::make('site_name')
                    ->label('Nume site')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('site_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizat la')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('updated_at', 'desc');
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
            'index' => Pages\ListSiteSettings::route('/'),
            'edit' => Pages\EditSiteSetting::route('/{record}/edit'),
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
        // Permite doar un singur record și doar pentru admini
        return auth()->user()?->hasRole('admin') ?? false && SiteSetting::count() === 0;
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

