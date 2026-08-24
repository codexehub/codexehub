@php
    $licensingNav = [
        ['route' => 'admin.licensing.licenses.index', 'active' => 'admin.licensing.licenses.*', 'label' => 'Licenses', 'icon' => 'bi-key-fill'],
        ['route' => 'admin.licensing.transactions.index', 'label' => 'Transactions', 'icon' => 'bi-cash-coin'],
        ['route' => 'admin.licensing.customers.index', 'active' => 'admin.licensing.customers.*', 'label' => 'Customers', 'icon' => 'bi-people'],
        ['route' => 'admin.licensing.apps.index', 'active' => 'admin.licensing.apps.*', 'label' => 'Apps', 'icon' => 'bi-box-seam'],
        ['route' => 'admin.licensing.packages.index', 'active' => 'admin.licensing.packages.*', 'label' => 'Packages', 'icon' => 'bi-stack'],
    ];
@endphp

<div class="btn-group mb-4 flex-wrap" role="group" aria-label="Licensing sections">
    @foreach ($licensingNav as $item)
        <a href="{{ route($item['route']) }}" class="btn btn-sm {{ request()->routeIs($item['active'] ?? $item['route']) ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi {{ $item['icon'] }}"></i> {{ $item['label'] }}
        </a>
    @endforeach
</div>
