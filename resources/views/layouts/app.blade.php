<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Demo Restaurant</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: system-ui, sans-serif; max-width: 720px; margin: 2rem auto; padding: 0 1rem; }
        nav a { margin-right: 1rem; }
        .success { color: #064e3b; padding: 1rem; background: #d1fae5; border-radius: 6px; }
        input, select, button { padding: 0.4rem 0.6rem; font: inherit; }
        button { padding: 0.5rem 1rem; }
    </style>
</head>
<body>
    <nav>
        <a href="/">Home</a>
        <a href="/menu">Menu</a>
        <a href="/order">Order</a>
    </nav>
    <main>@yield('content')</main>

    <script>
        window.ANALYTICS_TOKEN = '{{ config('analytics.public_token') }}';
        window.ANALYTICS_ENDPOINT = '/api/events';
    </script>
    <script src="{{ asset('js/analytics.js') }}"></script>
</body>
</html>