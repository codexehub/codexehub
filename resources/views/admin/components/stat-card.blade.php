@props(['label', 'value', 'icon', 'change' => null, 'trend' => 'flat'])

<div class="admin-stat-card">
    <div class="admin-stat-icon"><i class="bi {{ $icon }}"></i></div>
    <div class="admin-stat-body">
        <span class="admin-stat-label">{{ $label }}</span>
        <span class="admin-stat-value">{{ $value }}</span>
        @if ($change)
            <span class="admin-stat-change admin-stat-change--{{ $trend }}">
                <i class="bi {{ $trend === 'up' ? 'bi-arrow-up-right' : ($trend === 'down' ? 'bi-arrow-down-right' : 'bi-dash') }}"></i>
                {{ $change }}
            </span>
        @endif
    </div>
</div>
