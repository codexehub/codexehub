@extends('admin.layouts.app', ['title' => 'Customer — ' . $customer->name])

@php
    $statusBadge = [
        'prospect' => 'info',
        'active' => 'success',
        'inactive' => 'warning',
        'suspended' => 'danger',
    ];

    // SRS §77 tab order. `phase` marks a tab whose data arrives in a later
    // build phase — those render the shared placeholder pane for now.
    $tabs = [
        ['key' => 'information', 'label' => 'Information', 'icon' => 'bi-person-vcard'],
        ['key' => 'products', 'label' => 'Products', 'icon' => 'bi-box-seam', 'phase' => 'Phase 1 — Product catalogue (PROD-001)'],
        ['key' => 'packages', 'label' => 'Packages', 'icon' => 'bi-stack', 'phase' => 'Phase 2 — Package Management (PKG-001)'],
        ['key' => 'features', 'label' => 'Package Features', 'icon' => 'bi-puzzle', 'phase' => 'Phase 2 — Package feature assignment (PKG-003)'],
        ['key' => 'services', 'label' => 'Services', 'icon' => 'bi-tools', 'phase' => 'Phase 2 — Package service assignment (PKG-004)'],
        ['key' => 'requirements', 'label' => 'Requirements', 'icon' => 'bi-card-checklist', 'phase' => 'Phase 4 — Requirement & Development (REQ-001)'],
        ['key' => 'custom-development', 'label' => 'Custom Development', 'icon' => 'bi-code-slash', 'phase' => 'Phase 4 — Custom-dev tracking (CDEV-001)'],
        ['key' => 'invoices', 'label' => 'Invoices', 'icon' => 'bi-receipt', 'phase' => 'Phase 3 — Offer & Billing (INV-001)'],
        ['key' => 'payments', 'label' => 'Payments', 'icon' => 'bi-cash-coin', 'phase' => 'Phase 3 — Offer & Billing (PAY-001)'],
        ['key' => 'license', 'label' => 'License', 'icon' => 'bi-key-fill'],
        ['key' => 'installation', 'label' => 'Installation', 'icon' => 'bi-hdd-network', 'phase' => 'Phase 6 — Vendor Client & Sync (INST-001)'],
        ['key' => 'version', 'label' => 'Software Version', 'icon' => 'bi-tag', 'phase' => 'Phase 6 — Vendor Client & Sync (SYNC-001)'],
        ['key' => 'deployments', 'label' => 'Deployments', 'icon' => 'bi-rocket-takeoff', 'phase' => 'Phase 7 — Deployment (DEP-001)'],
        ['key' => 'ownership', 'label' => 'Ownership', 'icon' => 'bi-award', 'phase' => 'Phase 10 — Ownership (OWN-001)'],
        ['key' => 'audit', 'label' => 'Audit History', 'icon' => 'bi-clock-history'],
    ];

    $licenseBadge = [
        'active' => 'success',
        'completed' => 'success',
        'pending' => 'warning',
        'payment_due' => 'warning',
        'grace_period' => 'info',
        'reactivated' => 'info',
        'suspended' => 'secondary',
        'expired' => 'dark',
        'revoked' => 'danger',
    ];
@endphp

@section('content')
    <div class="admin-page-heading d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>{{ $customer->name }}</h1>
            <p>
                {{ $customer->company_name ? $customer->company_name . ' · ' : '' }}{{ $customer->email }}
                @if ($customer->phone)
                    · {{ $customer->phone }}
                @endif
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="admin-badge admin-badge--{{ $statusBadge[$customer->status] ?? 'info' }}" style="font-size:.9rem;">{{ ucfirst($customer->status) }}</span>
            @can('customers.manage')
                <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
            @endcan
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> All Customers</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="admin-panel">
        <div class="admin-panel-header p-0">
            <ul class="nav nav-tabs flex-nowrap overflow-auto px-3 pt-2" role="tablist" style="border-bottom:0;">
                @foreach ($tabs as $i => $tab)
                    <li class="nav-item flex-shrink-0" role="presentation">
                        <button class="nav-link {{ $i === 0 ? 'active' : '' }}" id="tab-{{ $tab['key'] }}-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-{{ $tab['key'] }}" type="button" role="tab"
                            aria-controls="tab-{{ $tab['key'] }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                            <i class="bi {{ $tab['icon'] }}"></i> {{ $tab['label'] }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="tab-content p-3 p-md-4">
            {{-- Information --}}
            <div class="tab-pane fade show active" id="tab-information" role="tabpanel" aria-labelledby="tab-information-btn">
                <div class="row g-4">
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-secondary">Contact Name</dt>
                            <dd class="col-sm-8">{{ $customer->name }}</dd>

                            <dt class="col-sm-4 text-secondary">Company</dt>
                            <dd class="col-sm-8">{{ $customer->company_name ?: '—' }}</dd>

                            <dt class="col-sm-4 text-secondary">Email</dt>
                            <dd class="col-sm-8">{{ $customer->email }}</dd>

                            <dt class="col-sm-4 text-secondary">Phone</dt>
                            <dd class="col-sm-8">{{ $customer->phone ?: '—' }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-secondary">Status</dt>
                            <dd class="col-sm-8"><span class="admin-badge admin-badge--{{ $statusBadge[$customer->status] ?? 'info' }}">{{ ucfirst($customer->status) }}</span></dd>

                            <dt class="col-sm-4 text-secondary">Licenses</dt>
                            <dd class="col-sm-8">{{ $customer->licenses_count }}</dd>

                            <dt class="col-sm-4 text-secondary">Added</dt>
                            <dd class="col-sm-8">{{ $customer->created_at?->format('Y-m-d') ?? '—' }}</dd>

                            <dt class="col-sm-4 text-secondary">Updated</dt>
                            <dd class="col-sm-8">{{ $customer->updated_at?->diffForHumans() ?? '—' }}</dd>
                        </dl>
                    </div>
                    <div class="col-12">
                        <h2 class="h6 text-secondary">Notes</h2>
                        <p class="mb-0">{{ $customer->notes ?: 'No notes recorded.' }}</p>
                    </div>
                </div>
            </div>

            {{-- License --}}
            <div class="tab-pane fade" id="tab-license" role="tabpanel" aria-labelledby="tab-license-btn">
                <x-admin::data-table>
                    <x-slot:head>
                        <tr><th>License Key</th><th>App / Package</th><th>Status</th><th>Expires</th><th>Last Verified</th></tr>
                    </x-slot:head>

                    @forelse ($licenses as $license)
                        <tr>
                            <td><code>{{ $license->license_key }}</code></td>
                            <td>{{ $license->licensedApp?->name ?? '—' }} / {{ $license->package?->name ?? '—' }}</td>
                            <td><span class="admin-badge admin-badge--{{ $licenseBadge[$license->status] ?? 'info' }}">{{ ucwords(str_replace('_', ' ', $license->status)) }}</span></td>
                            <td>{{ $license->expires_at?->format('Y-m-d') ?? 'Not set' }}</td>
                            <td>{{ $license->last_verified_at?->diffForHumans() ?? 'Never' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="admin-table-empty">No licenses issued to this customer yet.</td></tr>
                    @endforelse
                </x-admin::data-table>
                <p class="text-secondary small mt-2 mb-0">
                    The SRS §39 license lifecycle (payment&nbsp;due / grace&nbsp;period / reactivated / completed) and installation binding arrive with LIC-CP-001.
                </p>
            </div>

            {{-- Audit History --}}
            <div class="tab-pane fade" id="tab-audit" role="tabpanel" aria-labelledby="tab-audit-btn">
                <x-admin::data-table>
                    <x-slot:head>
                        <tr><th>When</th><th>Action</th><th>Changed</th><th>Actor</th></tr>
                    </x-slot:head>

                    @forelse ($auditLogs as $log)
                        <tr>
                            <td title="{{ $log->created_at }}">{{ $log->created_at?->diffForHumans() }}</td>
                            <td><code>{{ $log->action }}</code></td>
                            <td class="small text-secondary">{{ implode(', ', array_keys($log->new_values ?? $log->old_values ?? [])) ?: '—' }}</td>
                            <td>{{ $log->actor_label ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="admin-table-empty">No audit history for this customer yet.</td></tr>
                    @endforelse
                </x-admin::data-table>
            </div>

            {{-- Placeholder panes --}}
            @foreach ($tabs as $tab)
                @isset($tab['phase'])
                    <div class="tab-pane fade" id="tab-{{ $tab['key'] }}" role="tabpanel" aria-labelledby="tab-{{ $tab['key'] }}-btn">
                        <div class="text-center text-secondary py-5">
                            <i class="bi {{ $tab['icon'] }}" style="font-size:2rem;"></i>
                            <p class="mt-3 mb-1"><strong>{{ $tab['label'] }}</strong> for this customer will appear here.</p>
                            <p class="small mb-0">Arrives in {{ $tab['phase'] }}.</p>
                        </div>
                    </div>
                @endisset
            @endforeach
        </div>
    </div>
@endsection
