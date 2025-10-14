<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="#191e24">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#191e24">
    <link rel="icon" type="image/png" href="{{ asset('/favicon/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('/favicon/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('/favicon/favicon.ico')  }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/favicon/apple-touch-icon.png') }}" />
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Reisetagebuch') }}" />
    <link rel="shortcut icon" sizes="512x512" href="{{ asset('/favicon/pwa-512x512.png') }}">
    <link rel="shortcut icon" sizes="128x128" href="{{ asset('/favicon/pwa-192x192.png') }}">
    <link rel="manifest" href="{{ asset('/build/manifest.webmanifest') }}" />
    <meta name="name" content="{{ config('app.name', 'Reisetagebuch') }}">

    <title inertia>{{ config('app.name', 'Reisetagebuch') }}</title>

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>
<body class="font-sans antialiased bg-base-200 min-h-screen">
@inertia
</body>
</html>
