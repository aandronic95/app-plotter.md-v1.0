#!/bin/bash

# Script pentru rezolvarea erorii 403 pe server
# Rulează: bash fix-403-server.sh

echo "=========================================="
echo "Fix 403 Forbidden - Server Diagnostic"
echo "=========================================="
echo ""

cd /var/www/app-plotter.md-v1.0 || exit 1

echo "1. Curățare cache-uri Laravel..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

echo ""
echo "2. Curățare cache Spatie Permission..."
php artisan permission:cache-reset 2>/dev/null || echo "  (comandă nu disponibilă, continuăm...)"

echo ""
echo "3. Verificare permisiuni fișiere..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || chown -R apache:apache storage bootstrap/cache 2>/dev/null || echo "  (nu s-a putut schimba owner, continuăm...)"

echo ""
echo "4. Verificare utilizator și rol..."
php artisan tinker --execute="
\$user = \App\Models\User::where('email', 'devhub.md@gmail.com')->first();
if (\$user) {
    echo 'User: ' . \$user->email . PHP_EOL;
    echo 'Has admin role: ' . (\$user->hasRole('admin') ? 'YES' : 'NO') . PHP_EOL;
    echo 'Can access panel: ' . (\$user->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')) ? 'YES' : 'NO') . PHP_EOL;
    if (!\$user->hasRole('admin')) {
        echo 'Assigning admin role...' . PHP_EOL;
        \$user->syncRoles(['admin']);
        echo 'Admin role assigned!' . PHP_EOL;
    }
} else {
    echo 'User not found!' . PHP_EOL;
}
"

echo ""
echo "5. Verificare rute admin..."
php artisan route:list | grep -i admin | head -5

echo ""
echo "6. Curățare sesiuni..."
php artisan session:clear 2>/dev/null || echo "  (comandă nu disponibilă)"

echo ""
echo "7. Verificare loguri recente..."
if [ -f storage/logs/laravel.log ]; then
    echo "Ultimele erori din log:"
    tail -n 20 storage/logs/laravel.log | grep -i "403\|forbidden\|admin" | tail -5 || echo "  (nu s-au găsit erori relevante)"
else
    echo "  (fișier log nu există)"
fi

echo ""
echo "=========================================="
echo "Diagnostic completat!"
echo "=========================================="
echo ""
echo "Pași următori:"
echo "1. Reîncarcă PHP-FPM: sudo systemctl reload php8.3-fpm"
echo "2. Reîncarcă Nginx/Apache: sudo systemctl reload nginx (sau apache2)"
echo "3. Testează accesul: https://devhub.md/admin/login"
echo "4. Șterge cookie-urile din browser și încearcă din nou"
echo ""
