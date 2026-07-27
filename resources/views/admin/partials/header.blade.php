<header class="admin-header">
    <div class="admin-header-left">
        <button type="button" class="admin-header-icon-btn d-lg-none" data-sidebar-mobile-toggle aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>

        <nav class="admin-breadcrumb" aria-label="breadcrumb">
            <a href="{{ route('admin.dashboard') }}">Admin</a>
            <i class="bi bi-chevron-right"></i>
            <span>{{ $title ?? 'Dashboard' }}</span>
        </nav>
    </div>

    <div class="admin-header-right">
        <div class="dropdown admin-header-dropdown">
            <button type="button" class="admin-header-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                <i class="bi bi-bell-fill"></i>
                <span class="admin-notif-dot"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end admin-dropdown-menu">
                <h6 class="admin-dropdown-title">Notifications</h6>
                <a href="#" class="admin-dropdown-item">
                    <i class="bi bi-person-plus-fill"></i>
                    <div><strong>New user registered</strong><span>2 minutes ago</span></div>
                </a>
                <a href="#" class="admin-dropdown-item">
                    <i class="bi bi-briefcase-fill"></i>
                    <div><strong>Portfolio project updated</strong><span>1 hour ago</span></div>
                </a>
                <a href="#" class="admin-dropdown-item">
                    <i class="bi bi-envelope-fill"></i>
                    <div><strong>New contact message</strong><span>3 hours ago</span></div>
                </a>
            </div>
        </div>

        <div class="dropdown admin-header-dropdown">
            <button type="button" class="admin-user-btn" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="admin-user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                <span class="admin-user-name d-none d-md-inline">{{ auth()->user()->name ?? 'Admin' }}</span>
                <i class="bi bi-chevron-down d-none d-md-inline"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end admin-dropdown-menu">
                <a href="{{ route('admin.profile') }}" class="admin-dropdown-item">
                    <i class="bi bi-person-fill"></i>
                    <div><strong>My Profile</strong></div>
                </a>
                <a href="{{ route('admin.settings') }}" class="admin-dropdown-item">
                    <i class="bi bi-gear-fill"></i>
                    <div><strong>Settings</strong></div>
                </a>
                <hr class="admin-dropdown-divider">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="admin-dropdown-item admin-dropdown-item-btn">
                        <i class="bi bi-box-arrow-right"></i>
                        <div><strong>Logout</strong></div>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
