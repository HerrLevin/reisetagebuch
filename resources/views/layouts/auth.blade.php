<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#191e24">
    <link rel="icon" type="image/svg+xml" href="{{ asset('/favicon/favicon.svg') }}" />
    <title>@yield('title') – {{ config('app.name', 'Reisetagebuch') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-base-200 font-sans antialiased">
    <div class="flex min-h-screen items-center">
        <div class="card mx-auto w-full max-w-5xl shadow-xl">
            <div class="bg-base-100 grid grid-cols-1 md:grid-cols-2 md:rounded-xl">
                <div class="hero bg-base-200 min-h-full md:rounded-l-xl"
                     style="background-image: url('/assets/login/pexels-nurseryart-346885.jpg'); background-size: cover; background-position: center;">
                    <div class="hero-content py-12 text-shadow-md">
                        <h1 class="text-center text-3xl font-bold text-white mix-blend-difference">
                            <img src="/assets/logo.webp" alt="" class="inline-block h-12 w-12">
                            &nbsp; {{ config('app.name', 'Reisetagebuch') }}
                        </h1>
                    </div>
                </div>
                <div class="px-10 py-24">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
