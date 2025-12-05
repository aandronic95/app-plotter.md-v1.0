<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AdminerController extends Controller
{
    /**
     * Serve Adminer interface.
     * Accesibil doar pentru utilizatori cu rolul admin.
     */
    public function index(Request $request)
    {
        // Verifică dacă utilizatorul este autentificat și are rolul admin
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            abort(403, 'Acces interzis. Doar administratorii pot accesa Adminer.');
        }

        // Calea către fișierul Adminer
        $adminerPath = public_path('adminer.php');

        // Dacă fișierul nu există, afișează instrucțiuni
        if (!File::exists($adminerPath)) {
            return $this->showInstallInstructions();
        }

        // Servește Adminer
        return response()->file($adminerPath);
    }

    /**
     * Afișează instrucțiuni de instalare dacă Adminer nu este instalat.
     */
    private function showInstallInstructions()
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adminer - Instalare</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 700px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 25px;
            color: #856404;
        }
        .alert strong {
            display: block;
            margin-bottom: 5px;
        }
        .steps {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .steps ol {
            margin-left: 20px;
            line-height: 2;
        }
        .steps li {
            margin-bottom: 10px;
        }
        .steps code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .command {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s;
            margin-top: 10px;
        }
        .btn:hover {
            background: #5568d3;
        }
        .link {
            color: #667eea;
            text-decoration: none;
        }
        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Adminer nu este instalat</h1>
        <p class="subtitle">Urmează pașii de mai jos pentru a instala Adminer</p>
        
        <div class="alert">
            <strong>⚠️ Atenție:</strong>
            Adminer este un instrument puternic pentru gestionarea bazei de date. Asigură-te că doar administratorii pot accesa această pagină.
        </div>

        <div class="steps">
            <h3 style="margin-bottom: 15px;">📋 Pași de instalare:</h3>
            <ol>
                <li>
                    <strong>Opțiunea 1 - Automat (Recomandat):</strong><br>
                    Rulează în terminal:
                    <div class="command">php artisan adminer:download</div>
                </li>
                <li>
                    <strong>Opțiunea 2 - Manual:</strong><br>
                    <ol style="margin-top: 10px; margin-left: 20px;">
                        <li>Accesează <a href="https://www.adminer.org/" target="_blank" class="link">https://www.adminer.org/</a></li>
                        <li>Descarcă fișierul PHP (versiunea simplă)</li>
                        <li>Salvează-l ca <code>adminer.php</code> în directorul <code>public/</code></li>
                    </ol>
                </li>
                <li>După instalare, reîncarcă această pagină</li>
            </ol>
        </div>

        <div style="text-align: center;">
            <a href="/adminer" class="btn">🔄 Reîncarcă pagina</a>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #666; font-size: 14px;">
            <p><strong>💡 Configurare baza de date:</strong></p>
            <p style="margin-top: 10px;">
                După instalare, conectează-te la baza de date folosind credențialele din fișierul <code>.env</code>:
            </p>
            <ul style="margin-top: 10px; margin-left: 20px;">
                <li><strong>Sistem:</strong> SQLite (sau MySQL/PostgreSQL dacă folosești)</li>
                <li><strong>Server:</strong> localhost (sau calea către fișierul SQLite)</li>
                <li><strong>Utilizator:</strong> (lăsat gol pentru SQLite)</li>
                <li><strong>Parolă:</strong> (lăsat gol pentru SQLite)</li>
                <li><strong>Baza de date:</strong> calea către <code>database/database.sqlite</code></li>
            </ul>
        </div>
    </div>
</body>
</html>
HTML;

        return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Descarcă Adminer de la sursa oficială.
     */
    private function downloadAdminer(string $path): void
    {
        // URL-ul oficial pentru Adminer (versiunea cu design modern)
        $adminerUrl = 'https://www.adminer.org/latest.php';

        // Descarcă fișierul
        $content = @file_get_contents($adminerUrl);

        if ($content === false) {
            // Fallback: creează un fișier Adminer minimal
            $content = $this->getAdminerFallback();
        }

        // Salvează fișierul
        File::put($path, $content);
    }

    /**
     * Returnează un Adminer minimal dacă descărcarea eșuează.
     */
    private function getAdminerFallback(): string
    {
        // Returnează un mesaj de eroare dacă nu putem descărca Adminer
        return <<<'PHP'
<?php
// Adminer - Database management tool
// Descarcă manual de la: https://www.adminer.org/
// Sau rulează: php artisan adminer:download

if (!isset($_GET['server'])) {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Adminer - Download Required</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            .error { background: #fee; border: 1px solid #fcc; padding: 15px; border-radius: 5px; }
            .info { background: #eef; border: 1px solid #ccf; padding: 15px; border-radius: 5px; margin-top: 20px; }
            code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class="error">
            <h2>Adminer nu este instalat</h2>
            <p>Fișierul Adminer nu a putut fi descărcat automat.</p>
        </div>
        <div class="info">
            <h3>Instalare manuală:</h3>
            <ol>
                <li>Descarcă Adminer de la: <a href="https://www.adminer.org/" target="_blank">https://www.adminer.org/</a></li>
                <li>Salvează fișierul ca <code>adminer.php</code> în directorul <code>public/</code></li>
                <li>Reîncarcă această pagină</li>
            </ol>
            <p><strong>Sau rulează comanda:</strong></p>
            <pre><code>php artisan adminer:download</code></pre>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Dacă ajungem aici, încercăm să includem Adminer real
if (file_exists(__DIR__ . '/adminer.php')) {
    include __DIR__ . '/adminer.php';
} else {
    die('Adminer nu este instalat. Vezi instrucțiunile de mai sus.');
}
PHP;
    }
}
