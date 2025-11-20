# Deployment Quick Start - Plesk

## Pași Rapizi

### 1. Upload Fișierelor
```bash
# Via Git (recomandat)
cd /var/www/vhosts/yourdomain.com/httpdocs
git clone https://your-repo.git .

# Sau via FTP/SFTP - upload toate fișierele
```

### 2. Configurare PHP în Plesk
- **Domains** → **yourdomain.com** → **PHP Settings**
- Selectează **PHP 8.2+**
- Activează extensiile: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`, `zip`

### 3. Creează Baza de Date
- **Databases** → **Add Database**
- Notează: nume, utilizator, parolă

### 4. Configurează .env
```bash
cd /var/www/vhosts/yourdomain.com/httpdocs
cp .env.example .env
nano .env  # sau editează via Plesk File Manager
```

Actualizează:
```env
APP_NAME="Your App"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=nume_baza_date
DB_USERNAME=utilizator
DB_PASSWORD=parola
```

### 5. Rulează Deployment Script
```bash
bash deploy.sh
```

Sau manual:
```bash
composer install --optimize-autoloader --no-dev
npm install && npm run build
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage bootstrap/cache
```

### 6. Configurează Cron Job
În Plesk: **Scheduled Tasks** → **Add Task**
```bash
* * * * * cd /var/www/vhosts/yourdomain.com/httpdocs && php artisan schedule:run >> /dev/null 2>&1
```

### 7. Verifică
- Accesează domeniul în browser
- Testează funcționalitățile

## Troubleshooting Rapid

**Eroare 500?**
```bash
tail -f storage/logs/laravel.log
chmod -R 775 storage bootstrap/cache
php artisan config:clear
```

**Assets nu se încarcă?**
```bash
npm run build
php artisan storage:link
```

**Probleme cu baza de date?**
- Verifică credențialele în `.env`
- Verifică că utilizatorul are permisiuni

## Documentație Completă
Vezi `PLESK_DEPLOYMENT.md` pentru instrucțiuni detaliate.

