<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <link rel="icon" type="image/svg+xml" href="{{ asset('/favicon/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('/favicon/favicon.ico') }}" />

    <title>Admin - {{ config('app.name', 'Reisetagebuch') }}</title>

    @vite(['resources/admin/admin.ts'])
</head>
<body class="font-sans antialiased bg-base-200 min-h-screen">
    <div id="app"></div>
</body>
</html>
