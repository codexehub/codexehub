@extends('admin.layouts.app', ['title' => 'License ' . $license->license_key])

@section('content')
    @php
        $badge = match ($license->status) {
            'active' => 'success',
            'pending' => 'warning',
            'suspended' => 'secondary',
            'expired' => 'dark',
            'revoked' => 'danger',
            default => 'info',
        };
    @endphp

    <div class="admin-page-heading d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1><code>{{ $license->license_key }}</code></h1>
            <p>{{ $license->customer->name }} ({{ $license->customer->email }}) &middot; {{ $license->licensedApp->name }} / {{ $license->package->name }}</p>
        </div>
        <span class="admin-badge admin-badge--{{ $badge }}" style="font-size:.9rem;">{{ strtoupper($license->status) }}</span>
    </div>

    @include('admin.pages.licensing._nav')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="admin-stat-grid">
        <x-admin::stat-card label="Expires" :value="optional($license->expires_at)->format('Y-m-d') ?? 'Not set'" icon="bi-calendar-event" />
        <x-admin::stat-card label="Last Verified" :value="optional($license->last_verified_at)->diffForHumans() ?? 'Never'" icon="bi-arrow-repeat" />
        <x-admin::stat-card label="Activations" :value="$license->activations->count() . ' / ' . $license->package->max_activations" icon="bi-hdd-stack" />
    </div>

    <div class="d-flex gap-2 mb-4">
        <a href="{{ route('admin.licensing.licenses.edit', $license) }}" class="btn btn-outline-secondary btn-sm">Edit</a>

        @if ($license->status === 'active')
            <form method="POST" action="{{ route('admin.licensing.licenses.suspend', $license) }}" onsubmit="return confirm('Suspend this license? The product will stop working on its next check-in.')">
                @csrf
                <button class="btn btn-outline-warning btn-sm">Suspend</button>
            </form>
        @endif

        @if ($license->status === 'suspended')
            <form method="POST" action="{{ route('admin.licensing.licenses.reactivate', $license) }}">
                @csrf
                <button class="btn btn-outline-success btn-sm">Reactivate</button>
            </form>
        @endif

        @if ($license->status !== 'revoked')
            <form method="POST" action="{{ route('admin.licensing.licenses.revoke', $license) }}" onsubmit="return confirm('Revoke this license permanently? This cannot be undone from here.')">
                @csrf
                <button class="btn btn-outline-danger btn-sm">Revoke</button>
            </form>
        @endif
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="admin-panel mb-4">
                <div class="admin-panel-header">
                    <h2>Record a bKash Payment</h2>
                </div>
                <div class="p-3">
                    <form method="POST" action="{{ route('admin.licensing.transactions.store', $license) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small">bKash Transaction ID</label>
                            <input type="text" name="txn_id" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Amount</label>
                            <input type="number" step="0.01" name="amount" class="form-control form-control-sm" value="{{ $license->package->price }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Notes</label>
                            <input type="text" name="notes" class="form-control form-control-sm">
                        </div>
                        <button class="btn btn-sm btn-primary">Record Transaction</button>
                    </form>
                </div>
            </div>

            <div class="admin-panel">
                <div class="admin-panel-header">
                    <h2>Transactions</h2>
                </div>
                <x-admin::data-table>
                    <x-slot:head>
                        <tr><th>Txn ID</th><th>Amount</th><th>Status</th><th></th></tr>
                    </x-slot:head>

                    @forelse ($license->transactions->sortByDesc('created_at') as $txn)
                        <tr>
                            <td>{{ $txn->txn_id }}</td>
                            <td>{{ number_format($txn->amount, 2) }}</td>
                            <td><span class="admin-badge admin-badge--{{ $txn->status === 'approved' ? 'success' : ($txn->status === 'rejected' ? 'danger' : 'warning') }}">{{ $txn->status }}</span></td>
                            <td>
                                @if ($txn->status === 'pending')
                                    <div class="admin-table-actions">
                                        <form method="POST" action="{{ route('admin.licensing.transactions.approve', $txn) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.licensing.transactions.reject', $txn) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger">Reject</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-secondary small">{{ optional($txn->approved_at)->format('Y-m-d H:i') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="admin-table-empty">No transactions recorded.</td></tr>
                    @endforelse
                </x-admin::data-table>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="admin-panel">
                <div class="admin-panel-header">
                    <h2>Activations ({{ $license->activations->count() }} / {{ $license->package->max_activations }})</h2>
                </div>
                <x-admin::data-table>
                    <x-slot:head>
                        <tr><th>Domain</th><th>Fingerprint</th><th>Last Seen</th></tr>
                    </x-slot:head>

                    @forelse ($license->activations as $activation)
                        <tr>
                            <td>{{ $activation->domain }}</td>
                            <td><code class="small">{{ \Illuminate\Support\Str::limit($activation->fingerprint, 16) }}</code></td>
                            <td>{{ optional($activation->last_seen_at)->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="admin-table-empty">Not activated on any install yet.</td></tr>
                    @endforelse
                </x-admin::data-table>
            </div>
        </div>
    </div>
@endsection
