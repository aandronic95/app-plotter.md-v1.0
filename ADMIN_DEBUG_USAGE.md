# Script Admin Debug - Instrucțiuni de Utilizare

## Comenzi Disponibile

### 1. Listează toți adminii

```bash
php artisan admin:debug --list
```

Afișează un tabel cu toți utilizatorii care au rolul admin.

### 2. Verifică un utilizator specific

```bash
php artisan admin:debug --check --check-email=devhub.md@gmail.com
```

Afișează informații detaliate despre utilizator, inclusiv dacă poate accesa panoul Filament.

### 3. Șterge utilizatorul cu ID 1

```bash
php artisan admin:debug --delete-user-1
```

Șterge utilizatorul cu ID 1 (cu confirmare).

### 4. Creează un admin nou

```bash
# Cu parolă default (password)
php artisan admin:debug --create-admin --email=admin@example.com --name="Admin User"

# Cu parolă personalizată
php artisan admin:debug --create-admin --email=admin@example.com --name="Admin User" --password=secret123
```

Creează un utilizator nou cu rolul admin. Dacă utilizatorul există deja, poți actualiza rolul la admin.

## Exemple Complete

### Scenariu 1: Șterge user 1 și creează admin nou

```bash
# 1. Șterge utilizatorul cu ID 1
php artisan admin:debug --delete-user-1

# 2. Creează admin nou
php artisan admin:debug --create-admin --email=admin@devhub.md --name="Admin DevHub" --password=YourSecurePassword123

# 3. Verifică că a fost creat corect
php artisan admin:debug --check --check-email=admin@devhub.md
```

### Scenariu 2: Verifică toți adminii și creează unul nou dacă nu există

```bash
# 1. Listează toți adminii
php artisan admin:debug --list

# 2. Dacă nu există admini, creează unul
php artisan admin:debug --create-admin --email=admin@example.com --name="Super Admin"
```

### Scenariu 3: Debug complet pentru un utilizator

```bash
# Verifică utilizatorul
php artisan admin:debug --check --check-email=devhub.md@gmail.com

# Dacă nu este admin, setează rolul
php artisan user:set-role devhub.md@gmail.com admin

# Verifică din nou
php artisan admin:debug --check --check-email=devhub.md@gmail.com
```

## Notițe Importante

1. **Parola default**: Dacă nu specifici `--password`, parola default este `password`. Schimbă-o imediat după prima autentificare!

2. **Confirmare ștergere**: Când ștergi utilizatorul cu ID 1, vei fi întrebat de confirmare pentru siguranță.

3. **Utilizator existent**: Dacă încerci să creezi un admin cu un email care există deja, vei putea alege să actualizezi rolul la admin.

4. **Verificare Filament**: Comanda `--check` verifică și dacă utilizatorul poate accesa panoul Filament prin metoda `canAccessPanel()`.

## Troubleshooting

### Dacă comanda nu este recunoscută:

```bash
# Curăță cache-ul comenzilor
php artisan clear-compiled
php artisan optimize:clear
```

### Dacă primești erori la creare:

- Verifică dacă email-ul este valid
- Verifică dacă parola are minimum 8 caractere
- Verifică logurile: `tail -f storage/logs/laravel.log`

### Dacă utilizatorul nu poate accesa panoul:

1. Verifică rolul: `php artisan admin:debug --check --check-email=...`
2. Setează rolul: `php artisan user:set-role email@example.com admin`
3. Curăță cache-ul: `php artisan optimize:clear`
4. Verifică din nou: `php artisan admin:debug --check --check-email=...`
