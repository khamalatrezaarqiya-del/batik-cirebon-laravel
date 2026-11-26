<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#F7F5F0">
        <meta name="description" content="Batik Daffa — Koleksi kain batik tulis, batik cap, busana, dan aksesori karya pengrajin Trusmi, Cirebon.">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Batik Daffa">

        <title inertia>{{ config('app.name', 'Batik Daffa') }}</title>

        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='10' fill='%2324231F'/%3E%3Cpath d='M12 24q10-8 20 0t20 0M12 36q10-8 20 0t20 0M12 48q10-8 20 0t20 0' fill='none' stroke='%23A27B55' stroke-width='3.5' stroke-linecap='round'/%3E%3C/svg%3E">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

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
