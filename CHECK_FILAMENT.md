# Verificare și Instalare Filament pe Server

## Pași pentru a verifica și instala Filament

### 1. Verifică dacă Filament este instalat

```bash
# Verifică dacă Filament este în vendor
ls -la vendor/filament/

# Sau verifică prin composer
composer show filament/filament
```

### 2. Dacă Filament nu este instalat, instalează dependențele

```bash
# Instalează toate dependențele din composer.json
composer install --no-dev --optimize-autoloader

# Sau dacă vrei să instalezi și dependențele de dezvoltare
composer install --optimize-autoloader
```

### 3. Rulează comanda de upgrade Filament

```bash
php artisan filament:upgrade
```

### 4. Publică asset-urile Filament (dacă este necesar)

```bash
php artisan filament:assets
```

### 5. Curăță cache-ul

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### 6. Verifică dacă provider-ul este înregistrat

```bash
# Verifică providers.php
cat bootstrap/providers.php

# Ar trebui să conțină:
# App\Providers\Filament\AdminPanelProvider::class
```

### 7. Verifică dacă rutele Filament sunt înregistrate

```bash
php artisan route:list | grep admin
```

### 8. Verifică logurile pentru erori

```bash
tail -f storage/logs/laravel.log
```

## Comandă completă pentru instalare

```bash
cd /var/www/app-plotter.md-v1.0
composer install --no-dev --optimize-autoloader
php artisan filament:upgrade
php artisan filament:assets
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

## Verificare finală

După instalare, verifică:
1. Accesează `/admin` - ar trebui să vezi pagina de login Filament
2. Verifică logurile pentru erori
3. Verifică dacă utilizatorul are rolul 'admin' în baza de date

## Setare rol admin pentru utilizator

```bash
php artisan user:set-role your-email@example.com admin
```
