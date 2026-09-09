@extends('admin.layouts.app', ['title' => 'Customer Management'])

@php
    $statusBadge = [
        'prospect' => 'info',
        'active' => 'success',
        'inactive' => 'warning',
        'suspended' => 'danger',
    ];
@endphp

@section('content')
    @php
        $sortUrl = fn (string $column) => request()->fullUrlWithQuery([
            'sort' => $column,
            'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc',
        ]);
    @endphp

    <div class="admin-page-heading d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>Customer Management</h1>
            <p>The control plane's customers — the party a package, offer, invoice and license belong to.</p>
        </div>
        @can('customers.manage')
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Customer</a>
        @endcan
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
        <form method="GET" action="{{ route('admin.customers.index') }}" class="admin-panel-header admin-panel-header--toolbar flex-wrap gap-3">
            <div class="admin-table-search">
                <i class="bi bi-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, company or email...">
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    @foreach (\App\Models\Customer::STATUSES as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>

        <x-admin::data-table>
            <x-slot:head>
                <tr>
                    <th><a href="{{ $sortUrl('name') }}" class="admin-sort-link">Name <i class="bi bi-arrow-down-up"></i></a></th>
                    <th><a href="{{ $sortUrl('company_name') }}" class="admin-sort-link">Company <i class="bi bi-arrow-down-up"></i></a></th>
                    <th><a href="{{ $sortUrl('email') }}" class="admin-sort-link">Email <i class="bi bi-arrow-down-up"></i></a></th>
                    <th>Phone</th>
                    <th><a href="{{ $sortUrl('status') }}" class="admin-sort-link">Status <i class="bi bi-arrow-down-up"></i></a></th>
                    <th class="text-center">Licenses</th>
                    <th>Action</th>
                </tr>
            </x-slot:head>

            @forelse ($customers as $customer)
                <tr>
                    <td><a href="{{ route('admin.customers.show', $customer) }}">{{ $customer->name }}</a></td>
                    <td class="text-secondary">{{ $customer->company_name ?: '—' }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone ?: '—' }}</td>
                    <td>
                        @can('customers.manage')
                            <form method="POST" action="{{ route('admin.customers.status', $customer) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-select form-select-sm" style="width:auto;display:inline-block;" onchange="this.form.submit()">
                                    @foreach (\App\Models\Customer::STATUSES as $status)
                                        <option value="{{ $status }}" @selected($customer->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @else
                            <span class="admin-badge admin-badge--{{ $statusBadge[$customer->status] ?? 'info' }}">{{ ucfirst($customer->status) }}</span>
                        @endcan
                    </td>
                    <td class="text-center">{{ $customer->licenses_count }}</td>
                    <td>
                        <div class="admin-table-actions">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="admin-table-action" title="View"><i class="bi bi-eye"></i></a>
                            @can('customers.manage')
                                <a href="{{ route('admin.customers.edit', $customer) }}" class="admin-table-action" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm('Delete this customer? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-table-action admin-table-action--danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="admin-table-empty">No customers found.</td>
                </tr>
            @endforelse
        </x-admin::data-table>

        <div class="admin-pagination">{{ $customers->links() }}</div>
    </div>
@endsection
