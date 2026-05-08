<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'E-Class Record System')</title>
    <meta name="description" content="@yield('meta_description', 'A Laravel-based e-class record system for teacher and student workflows.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templatemo-glass-admin-style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/laravel-bridge.css') }}">
    @stack('styles')
</head>
<body data-page="@yield('page_name', 'guest')">
    <div class="background"></div>

    @yield('floating_theme_toggle')

    @yield('content')

    <script src="{{ asset('js/eclass.js') }}"></script>
    @stack('scripts')
</body>
</html>
