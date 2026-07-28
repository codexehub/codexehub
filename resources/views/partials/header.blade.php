@php
    $megaMenuGroups = [
        [
            'title' => 'Build',
            'items' => [
                ['slug' => 'web-development', 'icon' => 'bi-code-slash', 'title' => 'Web Development', 'desc' => 'Laravel, React & modern web platforms.'],
                ['slug' => 'mobile-apps', 'icon' => 'bi-phone', 'title' => 'Mobile Apps', 'desc' => 'Native & cross-platform apps that ship on time.'],
            ],
        ],
        [
            'title' => 'Design &amp; AI',
            'items' => [
                ['slug' => 'ui-ux-design', 'icon' => 'bi-palette', 'title' => 'UI / UX Design', 'desc' => 'Research-led interfaces, tested with real users.'],
                ['slug' => 'ai-automation', 'icon' => 'bi-cpu', 'title' => 'AI Automation', 'desc' => 'Practical automation powered by production AI.'],
            ],
        ],
        [
            'title' => 'Run &amp; Scale',
            'items' => [
                ['slug' => 'cloud-devops', 'icon' => 'bi-cloud-arrow-up', 'title' => 'Cloud &amp; DevOps', 'desc' => 'CI/CD & infrastructure that scales with load.'],
                ['slug' => 'maintenance-support', 'icon' => 'bi-tools', 'title' => 'Maintenance &amp; Support', 'desc' => 'SLA-backed releases & ongoing support.'],
            ],
        ],
    ];
    $servicesBase = request()->routeIs('services') ? '' : route('services');
@endphp
<header class="site-header">
    <nav class="navbar navbar-expand-lg h-100">
        <div class="container-xxl h-100 align-items-center">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-braces-asterisk"></i>
                <span class="brand-text">Codexe<span>Hub</span><small>Building Digital Excellence</small></span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="Toggle navigation">
                <i class="bi bi-list fs-3"></i>
            </button>

            <div class="collapse navbar-collapse d-none d-lg-flex">
                <ul class="navbar-nav main-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item dropdown mega-menu-item position-static">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('services') ? 'active' : '' }}" href="{{ route('services') }}" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            Services <i class="bi bi-chevron-down mega-caret"></i>
                        </a>
                        <div class="dropdown-menu mega-menu">
                            <div class="container-xxl">
                                <div class="row g-4">
                                    <div class="col-lg-8">
                                        <div class="row g-4">
                                            @foreach ($megaMenuGroups as $group)
                                                <div class="col-md-4">
                                                    <span class="mega-menu-group-title">{!! $group['title'] !!}</span>
                                                    <ul class="mega-menu-list list-unstyled">
                                                        @foreach ($group['items'] as $item)
                                                            <li>
                                                                <a href="{{ $servicesBase }}#service-{{ $item['slug'] }}" class="mega-menu-link">
                                                                    <span class="icon-tile icon-tile-sm"><i class="bi {{ $item['icon'] }}"></i></span>
                                                                    <span class="mega-menu-link-text">
                                                                        <strong>{!! $item['title'] !!}</strong>
                                                                        <small>{{ $item['desc'] }}</small>
                                                                    </span>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="mega-menu-cta">
                                            <span class="mega-menu-group-title">Not sure where to start?</span>
                                            <p>Tell us about your project and we&rsquo;ll map the right team and stack.</p>
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#consultationModal">Get Free Consultation <i class="bi bi-arrow-right"></i></button>
                                            <a href="{{ route('services') }}" class="btn-link-arrow">View All Services <i class="bi bi-arrow-right"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    {{-- <li class="nav-item"><a class="nav-link" href="{{ request()->routeIs('home') ? '#projects' : route('home').'#projects' }}">Projects</a></li> --}}
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('portfolio') ? 'active' : '' }}" href="{{ route('portfolio') }}">Portfolio</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About Us</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact Us</a></li>
                </ul>
            </div>

            <div class="nav-cta d-none d-lg-flex">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#consultationModal">Get Free Consultation <i class="bi bi-arrow-right"></i></button>
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileNav">
        <div class="offcanvas-header">
            <span class="navbar-brand mb-0"><i class="bi bi-braces-asterisk"></i> Codexe<span>Hub</span></span>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('services') ? 'active' : '' }}" href="{{ route('services') }}">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ request()->routeIs('home') ? '#projects' : route('home').'#projects' }}">Projects</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('portfolio') ? 'active' : '' }}" href="{{ route('portfolio') }}">Portfolio</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About Us</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact Us</a></li>
            </ul>
            <button type="button" class="btn btn-primary w-100 mt-3" data-bs-toggle="modal" data-bs-target="#consultationModal" data-bs-dismiss="offcanvas">Get Free Consultation <i class="bi bi-arrow-right"></i></button>
        </div>
    </div>
</header>
