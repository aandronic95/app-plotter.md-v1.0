# Verificare Filament - Pași pentru Server

## 1. Verifică dacă Filament este instalat

```bash
cd /var/www/app-plotter.md-v1.0
composer show filament/filament
```

Dacă nu apare, instalează:
```bash
composer install --no-dev --optimize-autoloader
php artisan filament:upgrade
```

## 2. Verifică dacă rutele Filament sunt înregistrate

```bash
php artisan route:list | grep admin
```

Ar trebui să vezi rute precum:
- `GET|HEAD  admin/login`
- `POST      admin/login`
- `GET|HEAD  admin`
- etc.

## 3. Verifică dacă provider-ul este înregistrat

```bash
php artisan package:discover
```

Apoi verifică:
```bash
cat bootstrap/providers.php
```

Ar trebui să conțină:
```php
App\Providers\Filament\AdminPanelProvider::class,
```

## 4. Testează metoda canAccessPanel direct

```bash
php artisan tinker
```

Apoi:
```php
$user = \App\Models\User::where('email', 'devhub.md@gmail.com')->first();
$panel = \Filament\Facades\Filament::getPanel('admin');
echo "Can Access Panel: " . ($user->canAccessPanel($panel) ? 'YES' : 'NO') . "\n";
```

## 5. Verifică dacă poți accesa pagina de login

Încearcă să accesezi direct:
```
https://devhub.md/admin/login
```

Ar trebui să vezi pagina de login Filament (nu 403).

## 6. Dacă vezi 403 la /admin/login

Aceasta înseamnă că problema este cu rutele sau middleware-urile. Verifică:

```bash
# Verifică dacă există conflicte de rute
php artisan route:list | grep -E "(admin|403)"

# Verifică middleware-urile
php artisan route:list --path=admin
```

## 7. Curăță tot cache-ul

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Și cache-ul aplicației
php artisan optimize:clear
```

## 8. Verifică logurile

```bash
tail -n 100 storage/logs/laravel.log | grep -i "filament\|admin\|403"
```

## 9. Verifică permisiunile

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

## 10. Dacă tot nu funcționează - Reinstalează Filament

```bash
composer remove filament/filament
composer require filament/filament:^4.0
php artisan filament:install --panels
php artisan filament:upgrade
php artisan optimize:clear
```

## 11. Test final

După toate pașii de mai sus:
1. Accesează `https://devhub.md/admin/login`
2. Loghează-te cu `devhub.md@gmail.com`
3. Ar trebui să fii redirecționat la dashboard-ul admin
