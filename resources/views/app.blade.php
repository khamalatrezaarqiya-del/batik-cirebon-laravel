<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#faf7f2">
        <meta name="description" content="Batik Daffa — Koleksi batik tulis dan cap pilihan dari pengrajin Trusmi, Cirebon. Motif Mega Mendung, Kawung, Parang, Truntum, dan Wadasan.">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Batik Daffa">

        <title inertia>{{ config('app.name', 'Batik Daffa') }}</title>

        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='12' fill='%23261e18'/%3E%3Cpath d='M10 26q11-9 22 0t22 0M10 38q11-9 22 0t22 0M10 50q11-9 22 0t22 0' fill='none' stroke='%23d6c4ae' stroke-width='3.5' stroke-linecap='round'/%3E%3C/svg%3E">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Literata:ital,opsz,wght@0,7..72,400;0,7..72,500;0,7..72,600;1,7..72,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        @routes
        @viteReactRefresh
        @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
        @inertiaHead
    </head>
    <body>
        <script>document.documentElement.classList.add('js')</script>
        @inertia
    </body>
</html>
