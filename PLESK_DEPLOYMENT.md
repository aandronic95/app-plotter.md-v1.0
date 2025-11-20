# Instrucțiuni de Deployment pe Plesk

Acest ghid vă va ajuta să deployați aplicația Laravel pe un server Plesk.

## Cerințe Preliminare

- Plesk cu PHP 8.2 sau superior
- MySQL/MariaDB
- Composer instalat
- Node.js și npm (pentru build assets)
- Acces SSH la server

## Pași de Deployment

### 1. Pregătirea Serverului

#### Verifică versiunea PHP
```bash
php -v
```
Trebuie să fie PHP 8.2 sau superior.

#### Verifică Composer
```bash
composer --version
```

### 2. Upload Fișierelor

#### Opțiunea 1: Git (Recomandat)
```bash
cd /var/www/vhosts/yourdomain.com/httpdocs
git clone https://your-repository-url.git .
```

#### Opțiunea 2: FTP/SFTP
- Upload toate fișierele în directorul `httpdocs` al domeniului
- Asigură-te că toate fișierele sunt uploadate (inclusiv `.htaccess`)

### 3. Configurarea Plesk

#### Setări PHP în Plesk
1. Deschide Plesk Panel
2. Mergi la **Domains** → **yourdomain.com** → **PHP Settings**
3. Selectează **PHP 8.2** sau superior
4. Activează extensiile necesare:
   - `pdo_mysql`
   - `mbstring`
   - `openssl`
   - `tokenizer`
   - `xml`
   - `ctype`
   - `json`
   - `bcmath`
   - `fileinfo`
   - `gd` (pentru procesarea imaginilor)
   - `zip`

#### Setări Document Root
1. Mergi la **Domains** → **yourdomain.com** → **Hosting Settings**
2. Asigură-te că **Document root** este setat la `/httpdocs` sau `/public_html`
3. Dacă aplicația este în subdirector, ajustează document root-ul

### 4. Configurarea Bazei de Date

#### Creează baza de date în Plesk
1. Mergi la **Databases** → **Add Database**
2. Notează:
   - Numele bazei de date
   - Utilizatorul
   - Parola

#### Actualizează `.env`
```bash
cd /var/www/vhosts/yourdomain.com/httpdocs
cp .env.example .env
nano .env
```

Actualizează următoarele valori:
```env
APP_NAME="Your App Name"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nume_baza_date
DB_USERNAME=utilizator_baza_date
DB_PASSWORD=parola_baza_date

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### 5. Instalarea Dependențelor

#### Instalează dependențele PHP
```bash
cd /var/www/vhosts/yourdomain.com/httpdocs
composer install --optimize-autoloader --no-dev
```

#### Instalează dependențele Node.js și build assets
```bash
npm install
npm run build
```

### 6. Configurarea Laravel

#### Generează cheia aplicației
```bash
php artisan key:generate
```

#### Rulează migrările
```bash
php artisan migrate --force
```

#### Creează link-ul simbolic pentru storage
```bash
php artisan storage:link
```

#### Optimizează aplicația pentru production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 7. Setarea Permisiunilor

```bash
cd /var/www/vhosts/yourdomain.com/httpdocs

# Setează permisiuni pentru storage
chmod -R 775 storage bootstrap/cache
chown -R plesk:psacln storage bootstrap/cache

# Asigură-te că directorul public/storage există și are permisiuni corecte
chmod -R 775 public/storage
chown -R plesk:psacln public/storage
```

### 8. Configurarea Cron Jobs

În Plesk, mergi la **Scheduled Tasks** și adaugă:

```bash
* * * * * cd /var/www/vhosts/yourdomain.com/httpdocs && php artisan schedule:run >> /dev/null 2>&1
```

### 9. Configurarea Queue Worker (dacă este necesar)

Dacă folosești queue-uri, configurează un worker:

În Plesk, la **Scheduled Tasks**, adaugă:
```bash
* * * * * cd /var/www/vhosts/yourdomain.com/httpdocs && php artisan queue:work --tries=3 --timeout=90
```

Sau folosește un proces manager precum Supervisor (dacă este disponibil).

### 10. Verificarea Deployment-ului

1. Accesează domeniul în browser
2. Verifică că aplicația se încarcă corect
3. Testează funcționalitățile principale:
   - Autentificare
   - Navigare produse
   - Coș de cumpărături
   - Checkout

### 11. Securitate

#### Asigură-te că `.env` nu este accesibil public
Verifică că `.env` este în `.gitignore` și nu este accesibil prin web.

#### Verifică permisiunile fișierelor
```bash
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
```

## Troubleshooting

### Eroare 500 Internal Server Error
1. Verifică logurile: `storage/logs/laravel.log`
2. Verifică permisiunile pentru `storage` și `bootstrap/cache`
3. Verifică că `.env` este configurat corect
4. Verifică că toate extensiile PHP sunt activate

### Probleme cu Assets (CSS/JS)
1. Rulează `npm run build` din nou
2. Verifică că directorul `public/build` există
3. Verifică permisiunile pentru `public/build`

### Probleme cu Storage
1. Verifică că link-ul simbolic există: `php artisan storage:link`
2. Verifică permisiunile pentru `storage/app/public`
3. Verifică că `public/storage` este accesibil

### Probleme cu Baza de Date
1. Verifică credențialele în `.env`
2. Verifică că utilizatorul bazei de date are permisiuni corecte
3. Verifică că toate migrările au fost rulate

### Clear Cache
Dacă întâmpini probleme, șterge cache-ul:
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

## Optimizări Post-Deployment

### 1. Activează OPcache
În Plesk, la **PHP Settings**, asigură-te că OPcache este activat.

### 2. Configurează Redis (opțional)
Dacă ai Redis disponibil, poți folosi pentru cache și session:
```env
CACHE_STORE=redis
SESSION_DRIVER=redis
```

### 3. Configurează CDN (opțional)
Pentru assets statice, poți configura un CDN și actualiza `APP_URL`.

## Suport

Pentru probleme specifice Plesk, consultă documentația oficială Plesk sau contactează suportul hosting-ului.

