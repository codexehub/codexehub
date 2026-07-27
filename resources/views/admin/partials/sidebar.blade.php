@php
    $adminNav = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2'],
        ['route' => 'admin.users.index', 'label' => 'User Management', 'icon' => 'bi-people-fill'],
        ['route' => 'admin.roles', 'label' => 'Role & Permission Management', 'icon' => 'bi-shield-lock-fill'],
        ['route' => 'admin.content', 'label' => 'Content Management', 'icon' => 'bi-file-earmark-richtext-fill'],
        ['route' => 'admin.services', 'label' => 'Service Management', 'icon' => 'bi-grid-fill'],
        ['route' => 'admin.portfolio.index', 'active' => 'admin.portfolio.*', 'label' => 'Portfolio Management', 'icon' => 'bi-briefcase-fill'],
        ['route' => 'admin.blog', 'label' => 'Blog Management', 'icon' => 'bi-journal-richtext'],
        ['route' => 'admin.settings', 'label' => 'Settings', 'icon' => 'bi-gear-fill'],
        ['route' => 'admin.reports', 'label' => 'Reports', 'icon' => 'bi-bar-chart-fill'],
    ];
@endphp

<aside class="admin-sidebar" data-sidebar>
    <div class="admin-sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="admin-brand-link">
            <i class="bi bi-braces-asterisk"></i>
            <span class="admin-brand-text">Codexe<span class="text-accent">Hub</span></span>
        </a>
        <button type="button" class="admin-sidebar-collapse-btn d-none d-lg-inline-flex" data-sidebar-collapse-toggle aria-label="Collapse sidebar">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>

    <nav class="admin-sidebar-nav">
        <ul>
            @foreach ($adminNav as $item)
                <li>
                    <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['active'] ?? $item['route']) ? 'active' : '' }}" data-bs-toggle="tooltip" title="{{ $item['label'] }}">
                        <i class="bi {{ $item['icon'] }}"></i>
                        <span class="admin-nav-label">{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="admin-sidebar-footer">
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="admin-logout-btn">
                <i class="bi bi-box-arrow-right"></i>
                <span class="admin-nav-label">Logout</span>
            </button>
        </form>
    </div>
</aside>
