<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'CodexeHub — Custom Software, Web & AI Solutions' }}</title>
    <meta name="description" content="{{ $description ?? 'CodexeHub builds custom software, web platforms, mobile apps and AI-powered automation for growing businesses.' }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="CodexeHub">
    <meta property="og:title" content="{{ $title ?? 'CodexeHub — Custom Software, Web & AI Solutions' }}">
    <meta property="og:description" content="{{ $description ?? 'CodexeHub builds custom software, web platforms, mobile apps and AI-powered automation for growing businesses.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('assets/images/og-cover.jpg') }}">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'CodexeHub — Custom Software, Web & AI Solutions' }}">
    <meta name="twitter:description" content="{{ $description ?? 'CodexeHub builds custom software, web platforms, mobile apps and AI-powered automation for growing businesses.' }}">
    <meta name="twitter:image" content="{{ asset('assets/images/og-cover.jpg') }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "CodexeHub",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('assets/images/logo.svg') }}",
        "sameAs": [
            "https://www.linkedin.com/company/codexehub",
            "https://www.upwork.com/agencies/codexehub",
            "https://www.fiverr.com/codexehub"
        ]
    }
    </script>

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    @include('partials.topbar')
    @include('partials.header')

    <main>
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.consultation.modal')
</body>
</html>
