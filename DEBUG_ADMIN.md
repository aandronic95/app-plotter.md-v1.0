# Debug Admin Access - Pași de verificare

## 1. Verifică dacă Filament este instalat

```bash
cd /var/www/app-plotter.md-v1.0
composer show filament/filament
```

Dacă nu este instalat:
```bash
composer install --no-dev --optimize-autoloader
php artisan filament:upgrade
```

## 2. Verifică rolul utilizatorului în baza de date

```bash
php artisan tinker
```

Apoi în tinker:
```php
$user = \App\Models\User::where('email', 'your-email@example.com')->first();
echo "ID: " . $user->id . "\n";
echo "Email: " . $user->email . "\n";
echo "Role: " . $user->role . " (type: " . gettype($user->role) . ")\n";
echo "Is Admin: " . ($user->isAdmin() ? 'YES' : 'NO') . "\n";
echo "Can Access Panel: " . ($user->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')) ? 'YES' : 'NO') . "\n";
```

## 3. Setează rolul admin dacă nu este setat

```bash
php artisan user:set-role your-email@example.com admin
```

## 4. Verifică dacă rutele Filament sunt înregistrate

```bash
php artisan route:list | grep admin
```

Ar trebui să vezi rutele pentru `/admin/login` și `/admin`.

## 5. Curăță toate cache-urile

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

## 6. Verifică logurile în timp real

```bash
tail -f storage/logs/laravel.log
```

Apoi încearcă să accesezi `/admin` și vezi ce erori apar în log.

## 7. Testează accesul direct

Încearcă să accesezi:
- `https://devhub.md/admin/login` - ar trebui să vezi pagina de login
- `https://devhub.md/admin` - ar trebui să te redirecționeze la login sau să vezi dashboard-ul dacă ești autentificat

## 8. Verifică dacă provider-ul este înregistrat

```bash
cat bootstrap/providers.php
```

Ar trebui să conțină:
```php
App\Providers\Filament\AdminPanelProvider::class,
```

## 9. Verifică permisiunile fișierelor

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

## 10. Dacă tot nu funcționează

Verifică dacă există erori în loguri și trimite output-ul pentru analiză.
