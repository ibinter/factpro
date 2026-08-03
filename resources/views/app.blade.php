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

        <!-- SEO & Social -->
        <meta name="description" content="IBIG FactPro — Logiciel de facturation en ligne pour entreprises africaines. Créez devis, factures, bons de livraison et gérez vos finances en toute simplicité.">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="IBIG FactPro">
        <meta property="og:title" content="IBIG FactPro — Facturation simple et professionnelle">
        <meta property="og:description" content="Gérez vos devis, factures et bons de livraison depuis une seule plateforme. Essai gratuit 7 jours, sans carte bancaire.">
        <meta property="og:image" content="{{ asset('images/og-image.png') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="IBIG FactPro — Facturation simple et professionnelle">
        <meta name="twitter:description" content="Gérez vos devis, factures et bons de livraison depuis une seule plateforme.">
        <meta name="twitter:image" content="{{ asset('images/og-image.png') }}">

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

        {{-- IBIG SOFT universal script retiré — remplacé par composant Vue IbigSoftSolutions --}}
    </body>
</html>
