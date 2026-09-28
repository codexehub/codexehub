@extends('admin.layouts.app', ['title' => 'Licenses'])

@section('content')
    <div class="admin-page-heading d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>Licenses</h1>
            <p>Issued license keys and their current status.</p>
        </div>
        <a href="{{ route('admin.licensing.licenses.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New License</a>
    </div>

    @include('admin.pages.licensing._nav')

    <div class="admin-panel">
        <x-admin::data-table>
            <x-slot:head>
                <tr>
                    <th>Key</th>
                    <th>Customer</th>
                    <th>App</th>
                    <th>Package</th>
                    <th>Status</th>
                    <th>Expires</th>
                    <th>Action</th>
                </tr>
            </x-slot:head>

            @forelse ($licenses as $license)
                @php
                    $badge = match ($license->status) {
                        'active', 'completed' => 'success',
                        'pending', 'payment_due' => 'warning',
                        'grace_period', 'reactivated' => 'info',
                        'suspended' => 'secondary',
                        'expired' => 'dark',
                        'revoked' => 'danger',
                        default => 'info',
                    };
                @endphp
                <tr>
                    <td><code>{{ $license->license_key }}</code></td>
                    <td>{{ $license->customer->name }}</td>
                    <td>{{ $license->licensedApp->name }}</td>
                    <td>{{ $license->package->name }}</td>
                    <td><span class="admin-badge admin-badge--{{ $badge }}">{{ ucwords(str_replace('_', ' ', $license->status)) }}</span></td>
                    <td>{{ optional($license->expires_at)->format('Y-m-d') ?? '-' }}</td>
                    <td>
                        <div class="admin-table-actions">
                            <a href="{{ route('admin.licensing.licenses.show', $license) }}" class="admin-table-action" title="Manage"><i class="bi bi-gear"></i></a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="admin-table-empty">No licenses yet.</td>
                </tr>
            @endforelse
        </x-admin::data-table>

        <div class="admin-pagination">{{ $licenses->links() }}</div>
    </div>
@endsection
