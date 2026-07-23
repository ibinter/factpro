<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- PWA -->
        <link rel="manifest" href="/manifest.webmanifest">
        <meta name="theme-color" content="#002D5B">
        <meta name="application-name" content="IBIG FactPro">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
        <link rel="icon" type="image/svg+xml" href="/logo_icon.svg">

        <link rel="canonical" href="{{ url()->current() }}">
        <link rel="dns-prefetch" href="https://www.googletagmanager.com">
        <link rel="dns-prefetch" href="https://connect.facebook.net">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        <script>window.Ziggy = Ziggy;</script>
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia

        {{-- Service Worker registered once in app.js --}}

        {{-- IBIG SOFT — Script universel : section "Nos solutions" + footer sur les pages publiques --}}
        @php
            $publicRoutes = ['home','public.pricing','about','contact','contact.store','demo','demo.store','blog.index','blog.show','testimonials','roadmap','roadmap.vote','security','partners'];
            $ibigRender = request()->routeIs(...$publicRoutes) ? 'all' : 'none';
        @endphp
        <script src="/assets/js/ibigsoft-universal.js"
                data-solution="factpro"
                data-accent="#0284C7"
                data-render="{{ $ibigRender }}"
                data-masquer-courante="true"></script>
    </body>
</html>
