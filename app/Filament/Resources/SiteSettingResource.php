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

                Section::make('Servicii - Secțiunea Principală')
                    ->description('Configurare pentru secțiunea de servicii afișată pe pagina principală')
                    ->schema([
                        Forms\Components\TextInput::make('service_feature_1_icon')
                            ->label('Icon Serviciu 1 (Rapiditate)')
                            ->maxLength(255)
                            ->default('Zap')
                            ->helperText('Numele iconului din lucide-vue-next (ex: Zap, Settings, Truck, Smile)')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('service_feature_1_title')
                            ->label('Titlu Serviciu 1')
                            ->maxLength(255)
                            ->default('RAPIDITATE')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('service_feature_1_description')
                            ->label('Descriere Serviciu 1')
                            ->rows(3)
                            ->default('Efectuăm rapid comenzile dvs., asigurându-ne că fiecare detaliu este gestionat cu precizie și promptitudine.')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('service_feature_2_icon')
                            ->label('Icon Serviciu 2 (Suport în Alegere)')
                            ->maxLength(255)
                            ->default('Settings')
                            ->helperText('Numele iconului din lucide-vue-next')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('service_feature_2_title')
                            ->label('Titlu Serviciu 2')
                            ->maxLength(255)
                            ->default('SUPORT ÎN ALEGERE')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('service_feature_2_description')
                            ->label('Descriere Serviciu 2')
                            ->rows(3)
                            ->default('Echipa noastră dedicată vă garantează că veți fi ajutați să faceți o alegere corectă pentru rezultatul dorit.')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('service_feature_3_icon')
                            ->label('Icon Serviciu 3 (Transport)')
                            ->maxLength(255)
                            ->default('Truck')
                            ->helperText('Numele iconului din lucide-vue-next')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('service_feature_3_title')
                            ->label('Titlu Serviciu 3')
                            ->maxLength(255)
                            ->default('TRANSPORT')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('service_feature_3_description')
                            ->label('Descriere Serviciu 3')
                            ->rows(3)
                            ->default('Cu mândrie vă asigurăm că fiecare comandă beneficiază de un angajament ferm: garantăm livrare fără deteriorare')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('service_feature_4_icon')
                            ->label('Icon Serviciu 4 (Calitate Garantată)')
                            ->maxLength(255)
                            ->default('Smile')
                            ->helperText('Numele iconului din lucide-vue-next')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('service_feature_4_title')
                            ->label('Titlu Serviciu 4')
                            ->maxLength(255)
                            ->default('CALITATE GARANTATĂ')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('service_feature_4_description')
                            ->label('Descriere Serviciu 4')
                            ->rows(3)
                            ->default('Suntem mândri să vă asigurăm că fiecare produs sau serviciu pe care îl oferim vine însoţit de o promisiune fermă: calitate garantată.')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Hero Banner')
                    ->description('Configurare pentru banner-ul principal afișat pe pagina principală')
                    ->schema([
                        Forms\Components\TextInput::make('hero_banner_headline')
                            ->label('Headline')
                            ->maxLength(255)
                            ->helperText('Textul de headline afișat deasupra titlului (ex: 🔥 OFERTĂ SPECIALĂ)')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('hero_banner_title')
                            ->label('Titlu principal')
                            ->maxLength(255)
                            ->default('PRINTĂM')
                            ->helperText('Titlul principal al banner-ului. Dacă este gol, se va folosi animația cu cuvinte rotative.')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('hero_banner_description')
                            ->label('Descriere')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Descrierea banner-ului')
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('hero_banner_features')
                            ->label('Features')
                            ->schema([
                                Forms\Components\TextInput::make('feature')
                                    ->label('Feature')
                                    ->maxLength(255),
                            ])
                            ->defaultItems(0)
                            ->itemLabel(fn (array $state): ?string => $state['feature'] ?? null)
                            ->helperText('Lista de features afișate cu checkmark-uri')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('hero_banner_button1_text')
                            ->label('Text Buton 1')
                            ->maxLength(255)
                            ->helperText('Textul pentru primul buton CTA'),

                        Forms\Components\TextInput::make('hero_banner_button1_link')
                            ->label('Link Buton 1')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Link-ul pentru primul buton CTA'),

                        Forms\Components\TextInput::make('hero_banner_button2_text')
                            ->label('Text Buton 2')
                            ->maxLength(255)
                            ->helperText('Textul pentru al doilea buton CTA'),

                        Forms\Components\TextInput::make('hero_banner_button2_link')
                            ->label('Link Buton 2')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Link-ul pentru al doilea buton CTA'),

                        Forms\Components\FileUpload::make('hero_banner_image')
                            ->label('Imagine Hero Banner')
                            ->image()
                            ->disk('public')
                            ->directory('hero-banners')
                            ->visibility('public')
                            ->maxSize(10240)
                            ->imageEditor()
                            ->helperText('Imaginea pentru hero banner (recomandat 1200x600px)')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('hero_banner_is_active')
                            ->label('Banner activ')
                            ->helperText('Activează/dezactivează afișarea banner-ului')
                            ->default(true)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('hero_banner_sort_order')
                            ->label('Ordinea de sortare')
                            ->numeric()
                            ->default(0)
                            ->helperText('Ordinea de sortare (0 = primul)')
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('hero_banner_rotating_words')
                            ->label('Cuvinte rotative pentru animație')
                            ->schema([
                                Forms\Components\TextInput::make('word')
                                    ->label('Cuvânt')
                                    ->maxLength(255),
                            ])
                            ->defaultItems(5)
                            ->default([
                                ['word' => 'HAINE'],
                                ['word' => 'CĂRȚI DE VIZITE'],
                                ['word' => 'BANERE'],
                                ['word' => 'CUTII'],
                                ['word' => 'POSTERE'],
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['word'] ?? null)
                            ->helperText('Lista de cuvinte care se rotesc în animația de typing (folosit dacă titlul este gol)')
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

