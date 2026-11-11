<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @yield('meta')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    @include('partials.ga4')
</head>
<body class="@yield('body_class')">
    <header class="site-header">
        <div class="container">
            <a href="{{ route('home') }}" class="logo">{{ config('app.name') }}</a>
            <nav>
                <a href="{{ route('calculators.fd') }}" @class(['active' => request()->routeIs('calculators.fd')])>FD Calculator</a>
                <a href="{{ route('calculators.sip') }}" @class(['active' => request()->routeIs('calculators.sip')])>SIP Calculator</a>
                <a href="{{ route('admin.pages.index') }}" @class(['active' => request()->routeIs('admin.*')])>Admin</a>
            </nav>
        </div>
    </header>

    <main class="container">
        @if (session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container">
            &copy; {{ date('Y') }} {{ config('app.name') }} &middot;
            <a href="{{ route('sitemap') }}">Sitemap</a>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
