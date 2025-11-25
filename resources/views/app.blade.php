<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">


        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
        
        {{-- Script pentru a ascunde datele sensibile din data-page după încărcarea Inertia --}}
        <script>
            (function() {
                // Șterge atributul data-page după ce Inertia a încărcat datele
                function removeDataPage() {
                    const appElement = document.getElementById('app');
                    if (appElement && appElement.hasAttribute('data-page')) {
                        // Așteaptă puțin pentru ca Inertia să citească datele
                        setTimeout(function() {
                            appElement.removeAttribute('data-page');
                        }, 100);
                    }
                }
                
                // Încearcă imediat și după ce DOM-ul este gata
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', removeDataPage);
                } else {
                    removeDataPage();
                }
                
                // Fallback pentru cazul în care Inertia se încarcă mai târziu
                window.addEventListener('load', function() {
                    setTimeout(removeDataPage, 500);
                });
            })();
        </script>
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
