<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        @php
            $siteSettings = \App\Models\SiteSetting::current();
            $siteName = $siteSettings->site_name ?? config('app.name', 'Laravel');
            $siteDescription = $siteSettings->site_meta_description ?? $siteSettings->site_description ?? '';
            $siteUrl = config('app.url');
            $siteLogo = $siteSettings->logo_url ?? asset('images/logo.png');
        @endphp
        
        {{-- Basic Meta Tags --}}
        <meta name="description" content="{{ $siteDescription }}">
        @if($siteSettings->site_meta_keywords)
        <meta name="keywords" content="{{ $siteSettings->site_meta_keywords }}">
        @endif
        <meta name="author" content="{{ $siteName }}">
        <meta name="robots" content="index, follow">
        <meta name="language" content="{{ str_replace('_', '-', app()->getLocale()) }}">
        
        {{-- Open Graph / Facebook --}}
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $siteUrl }}">
        <meta property="og:title" content="{{ $siteName }}">
        <meta property="og:description" content="{{ $siteDescription }}">
        <meta property="og:image" content="{{ $siteLogo }}">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
        
        {{-- Twitter Card --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ $siteUrl }}">
        <meta name="twitter:title" content="{{ $siteName }}">
        <meta name="twitter:description" content="{{ $siteDescription }}">
        <meta name="twitter:image" content="{{ $siteLogo }}">
        
        {{-- Canonical URL (will be overridden by Inertia Head if needed) --}}
        <link rel="canonical" href="{{ $siteUrl }}{{ request()->getRequestUri() }}">
        
        {{-- Favicon --}}
        @if($siteSettings->favicon_url)
        <link rel="icon" href="{{ $siteSettings->favicon_url }}" type="image/x-icon">
        @endif

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

            /* Initial loading screen */
            #initial-loading-screen {
                position: fixed;
                inset: 0;
                z-index: 99999;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                transition: opacity 0.5s ease, visibility 0.5s ease;
            }

            html.dark #initial-loading-screen {
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            }

            #initial-loading-screen.hidden {
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
            }

            .loading-main-text {
                font-size: 4rem;
                font-weight: 700;
                font-style: italic;
                letter-spacing: -0.02em;
                background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 50%, #ffffff 100%);
                background-size: 200% 200%;
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                animation: gradientShift 3s ease infinite, fadeInUp 0.8s ease;
                margin-bottom: 0.5rem;
                text-shadow: 0 0 30px rgba(255, 255, 255, 0.3);
            }

            html.dark .loading-main-text {
                background: linear-gradient(135deg, #ffffff 0%, #e0e0e0 50%, #ffffff 100%);
                background-size: 200% 200%;
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .loading-sub-text {
                font-size: 1rem;
                font-weight: 400;
                color: rgba(255, 255, 255, 0.8);
                letter-spacing: 0.1em;
                animation: fadeInUp 0.8s ease 0.2s both;
                margin-bottom: 2rem;
            }

            html.dark .loading-sub-text {
                color: rgba(255, 255, 255, 0.7);
            }

            .loading-dots {
                display: flex;
                gap: 0.5rem;
                animation: fadeInUp 0.8s ease 0.4s both;
            }

            .loading-dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.9);
                animation: loadingBounce 1.4s ease-in-out infinite;
            }

            html.dark .loading-dot {
                background: rgba(255, 255, 255, 0.8);
            }

            .loading-dot:nth-child(1) {
                animation-delay: 0s;
            }

            .loading-dot:nth-child(2) {
                animation-delay: 0.2s;
            }

            .loading-dot:nth-child(3) {
                animation-delay: 0.4s;
            }

            @keyframes gradientShift {
                0%, 100% {
                    background-position: 0% 50%;
                }
                50% {
                    background-position: 100% 50%;
                }
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes loadingBounce {
                0%, 80%, 100% {
                    transform: scale(0.8);
                    opacity: 0.5;
                }
                40% {
                    transform: scale(1.2);
                    opacity: 1;
                }
            }

            @media (max-width: 640px) {
                .loading-main-text {
                    font-size: 2.5rem;
                }
                .loading-sub-text {
                    font-size: 0.875rem;
                }
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
        {{-- Initial loading screen (shows before JavaScript loads) --}}
        <div id="initial-loading-screen">
            <div class="loading-main-text" id="loading-main-text">tanavius</div>
            <div class="loading-sub-text" id="loading-sub-text">www.plotter.md</div>
            <div class="loading-dots">
                <div class="loading-dot"></div>
                <div class="loading-dot"></div>
                <div class="loading-dot"></div>
            </div>
        </div>

        <script>
            // Get loading text from server-side settings
            @php
                $siteSettings = \App\Models\SiteSetting::current();
                $loadingMain = $siteSettings->loading_text_main ?? 'tanavius';
                $loadingSub = $siteSettings->loading_text_sub ?? 'www.plotter.md';
            @endphp
            
            const loadingMainText = document.getElementById('loading-main-text');
            const loadingSubText = document.getElementById('loading-sub-text');
            
            if (loadingMainText) {
                loadingMainText.textContent = @json($loadingMain);
            }
            if (loadingSubText) {
                loadingSubText.textContent = @json($loadingSub);
            }

            // Hide initial loading screen after 1 second
            setTimeout(function() {
                const loadingScreen = document.getElementById('initial-loading-screen');
                if (loadingScreen) {
                    loadingScreen.classList.add('hidden');
                    // Remove from DOM after transition
                    setTimeout(function() {
                        loadingScreen.remove();
                    }, 500);
                }
            }, 1000);

            // Register Service Worker for caching
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js')
                        .then(function(registration) {
                            console.log('[Service Worker] Registered successfully:', registration.scope);
                        })
                        .catch(function(error) {
                            console.log('[Service Worker] Registration failed:', error);
                        });
                });
            }
        </script>

        @inertia
    </body>
</html>
