<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin — CodexeHub' }}</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/scss/admin.scss', 'resources/js/admin.js'])
</head>
<body class="admin-guest-body">
    <div class="admin-auth-wrap">
        <div class="admin-auth-card">
            <a href="{{ route('home') }}" class="admin-auth-brand">
                <i class="bi bi-braces-asterisk"></i>
                <span>Codexe<span class="text-accent">Hub</span></span>
            </a>

            @yield('content')
        </div>
    </div>
</body>
</html>
