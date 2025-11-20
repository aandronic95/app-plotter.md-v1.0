#!/bin/bash

# Script de deployment pentru Plesk
# Utilizare: bash deploy.sh

set -e

echo "🚀 Începem deployment-ul..."

# Culori pentru output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verifică dacă suntem în directorul corect
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Eroare: Nu sunteți în directorul root al aplicației Laravel!${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Director corect${NC}"

# Verifică dacă .env există
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚠ .env nu există. Se creează din .env.example...${NC}"
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${YELLOW}⚠ Vă rugăm să actualizați .env cu configurațiile corecte!${NC}"
    else
        echo -e "${RED}❌ .env.example nu există!${NC}"
        exit 1
    fi
fi

# Instalează dependențele Composer
echo -e "${GREEN}📦 Instalăm dependențele Composer...${NC}"
composer install --optimize-autoloader --no-dev --no-interaction

# Instalează dependențele npm și build assets
if [ -f "package.json" ]; then
    echo -e "${GREEN}📦 Instalăm dependențele npm...${NC}"
    npm install --production
    
    echo -e "${GREEN}🔨 Construim assets-urile...${NC}"
    npm run build
fi

# Generează cheia aplicației dacă nu există
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo -e "${GREEN}🔑 Generăm cheia aplicației...${NC}"
    php artisan key:generate --force
fi

# Rulează migrările
echo -e "${GREEN}🗄️  Rulăm migrările...${NC}"
php artisan migrate --force

# Creează link-ul simbolic pentru storage
if [ ! -L "public/storage" ]; then
    echo -e "${GREEN}🔗 Creăm link-ul simbolic pentru storage...${NC}"
    php artisan storage:link
fi

# Optimizează aplicația
echo -e "${GREEN}⚡ Optimizăm aplicația pentru production...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Setează permisiunile
echo -e "${GREEN}🔐 Setăm permisiunile...${NC}"
chmod -R 775 storage bootstrap/cache
chmod -R 755 public

# Verifică permisiunile
if [ -w "storage" ] && [ -w "bootstrap/cache" ]; then
    echo -e "${GREEN}✓ Permisiuni corecte${NC}"
else
    echo -e "${YELLOW}⚠ Verificați manual permisiunile pentru storage și bootstrap/cache${NC}"
fi

echo -e "${GREEN}✅ Deployment finalizat cu succes!${NC}"
echo -e "${YELLOW}⚠ Nu uitați să:${NC}"
echo -e "  1. Verificați configurațiile din .env"
echo -e "  2. Configurați cron job-ul pentru scheduler"
echo -e "  3. Configurați queue worker-ul (dacă este necesar)"
echo -e "  4. Testați aplicația în browser"

