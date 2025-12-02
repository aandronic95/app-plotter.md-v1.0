# Fix 403 Forbidden pe Server - Ghid Complet

## 🚨 Eroare: 403 Forbidden pe server

Dacă primești eroarea "Failed to load resource: the server responded with a status of 403 (Forbidden)", urmează acești pași:

## ✅ Soluție Rapidă - Rulează scriptul:

```bash
cd /var/www/app-plotter.md-v1.0
chmod +x fix-403-server.sh
bash fix-403-server.sh
```

## 🔧 Soluție Manuală - Pași detaliați:

### 1. Curăță TOATE cache-urile:

```bash
cd /var/www/app-plotter.md-v1.0

# Cache Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Cache Spatie Permission
php artisan permission:cache-reset

# Dacă există OPcache, reîncarcă PHP-FPM
sudo systemctl reload php8.3-fpm
# sau
sudo systemctl restart php8.3-fpm
```

### 2. Verifică și atribuie rolul admin:

```bash
php artisan tinker
```

Apoi:
```php
// Verifică utilizatorul
$user = \App\Models\User::where('email', 'devhub.md@gmail.com')->first();

// Verifică rolul
echo "Has admin: " . ($user->hasRole('admin') ? 'YES' : 'NO') . "\n";
echo "Can access: " . ($user->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')) ? 'YES' : 'NO') . "\n";

// Dacă nu are rolul, atribuie-l
if (!$user->hasRole('admin')) {
    $user->syncRoles(['admin']);
    echo "Admin role assigned!\n";
}

// Verifică din nou
$user->refresh();
echo "Has admin: " . ($user->hasRole('admin') ? 'YES' : 'NO') . "\n";
```

### 3. Verifică permisiunile fișierelor:

```bash
cd /var/www/app-plotter.md-v1.0

# Setează permisiunile corecte
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Setează owner-ul corect (ajustă în funcție de server)
chown -R www-data:www-data storage bootstrap/cache
# sau pentru Apache
chown -R apache:apache storage bootstrap/cache
```

### 4. Verifică configurația serverului:

#### Pentru Nginx:

```bash
# Verifică configurația
sudo cat /etc/nginx/sites-available/devhub.md | grep -A 10 -B 10 "location"

# Verifică dacă există reguli care blochează /admin
sudo grep -i "deny\|block\|403" /etc/nginx/sites-available/devhub.md

# Testează configurația
sudo nginx -t

# Reîncarcă Nginx
sudo systemctl reload nginx
```

#### Pentru Apache:

```bash
# Verifică configurația
sudo cat /etc/apache2/sites-available/devhub.md.conf | grep -A 10 -B 10 "Directory\|Location"

# Testează configurația
sudo apachectl configtest

# Reîncarcă Apache
sudo systemctl reload apache2
```

### 5. Verifică logurile:

```bash
# Loguri Laravel
tail -n 100 storage/logs/laravel.log | grep -i "403\|forbidden\|admin"

# Loguri Nginx
sudo tail -n 100 /var/log/nginx/error.log | grep -i "403\|forbidden"

# Loguri Apache
sudo tail -n 100 /var/log/apache2/error.log | grep -i "403\|forbidden"

# Loguri PHP-FPM
sudo tail -n 100 /var/log/php8.3-fpm.log | grep -i "error"
```

### 6. Verifică sesiunea și cookie-urile:

```bash
# Curăță sesiunile vechi
php artisan session:clear

# Verifică configurația sesiunii în .env
grep SESSION_DRIVER .env
```

### 7. Testează accesul direct:

```bash
# Testează cu curl
curl -I https://devhub.md/admin/login

# Dacă vezi 403, problema este la nivel de server
# Dacă vezi 200, problema este după autentificare
```

### 8. Verifică dacă există firewall sau restricții:

```bash
# Verifică iptables
sudo iptables -L -n | grep -i block

# Verifică fail2ban
sudo fail2ban-client status

# Verifică dacă IP-ul tău este blocat
sudo fail2ban-client status sshd
```

### 9. Debug avansat - Creează ruta de test:

Adaugă în `routes/web.php` (temporar pentru test):

```php
Route::get('/test-admin-access', function() {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Not authenticated'], 401);
    }
    return response()->json([
        'user_id' => $user->id,
        'email' => $user->email,
        'has_admin_role' => $user->hasRole('admin'),
        'can_access_panel' => $user->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')),
        'roles' => $user->getRoleNames()->toArray(),
    ]);
})->middleware('auth');
```

Apoi testează:
```
https://devhub.md/test-admin-access
```

### 10. Verifică dacă problema este doar în browser:

- Șterge cookie-urile pentru devhub.md
- Încearcă în mod incognito
- Încearcă din alt browser
- Încearcă din alt dispozitiv/rețea

## 🔍 Cauze comune pentru 403:

1. **Cache-uri vechi** - Cel mai comun
2. **Rolul admin nu este atribuit** - Verificat în tinker
3. **Permisiuni fișiere** - storage/bootstrap/cache
4. **Configurație server** - Nginx/Apache blochează
5. **Sesiune expirată** - Cookie-uri vechi
6. **Firewall/WAF** - Blochează la nivel de server
7. **OPcache** - Cache PHP care nu se actualizează

## 📋 Checklist Final:

- [ ] Toate cache-urile sunt curățate
- [ ] Rolul admin este atribuit și verificat
- [ ] Permisiunile fișierelor sunt corecte
- [ ] Serverul (Nginx/Apache) este reîncărcat
- [ ] PHP-FPM este reîncărcat
- [ ] Logurile nu arată erori relevante
- [ ] Browser-ul nu are cache vechi
- [ ] Cookie-urile sunt șterse

## 🚨 Dacă tot nu funcționează:

1. **Verifică dacă problema este doar pentru un anumit utilizator:**
   - Creează un alt utilizator admin
   - Testează cu acel utilizator

2. **Verifică dacă problema este doar pentru /admin:**
   - Testează alte rute autentificate
   - Verifică dacă problema este specifică Filament

3. **Contactează hosting-ul:**
   - Poate există WAF (Web Application Firewall)
   - Poate există restricții la nivel de server
   - Poate există mod_security care blochează

## 📝 Note importante:

- **403 Forbidden** înseamnă că serverul înțelege cererea dar refuză să o autorizeze
- Dacă `canAccessPanel()` returnează `true` în tinker, problema este probabil cache sau configurație server
- Filament verifică `canAccessPanel()` înainte de a permite accesul
- Cache-ul Spatie Permission poate cauza probleme dacă nu este resetat după modificări de roluri
