<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#1f3a5f">
        <meta name="description" content="Batik tulis dan cap asli Cirebon. Motif Mega Mendung, Kawung, Parang, dan Truntum oleh pengrajin Trusmi.">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Batik Cirebon">

        <title inertia>{{ config('app.name', 'Batik Cirebon') }}</title>

        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='12' fill='%231f3a5f'/%3E%3Cpath d='M8 26q12-10 24 0t24 0M8 38q12-10 24 0t24 0M8 50q12-10 24 0t24 0' fill='none' stroke='%23d4a574' stroke-width='4' stroke-linecap='round'/%3E%3C/svg%3E">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700;900&family=Literata:ital,opsz,wght@0,7..72,400;0,7..72,600;1,7..72,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        @routes
        @viteReactRefresh
        @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
