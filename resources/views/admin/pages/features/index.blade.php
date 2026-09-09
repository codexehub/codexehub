@extends('admin.layouts.app', ['title' => 'Feature Catalog'])

@php
    $statusBadge = ['active' => 'success', 'inactive' => 'warning', 'deprecated' => 'danger'];
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
            <h1>Feature Catalog</h1>
            <p>Reusable entitlements sold inside customer packages — priced, versioned and synced to installations by key.</p>
        </div>
        @can('features.manage')
            <a href="{{ route('admin.features.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Feature</a>
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
        <form method="GET" action="{{ route('admin.features.index') }}" class="admin-panel-header admin-panel-header--toolbar flex-wrap gap-3">
            <div class="admin-table-search">
                <i class="bi bi-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, key or description...">
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    @foreach (\App\Models\Feature::TYPES as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    @foreach (\App\Models\Feature::STATUSES as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if (request()->hasAny(['search', 'status', 'type']))
                    <a href="{{ route('admin.features.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>

        <x-admin::data-table>
            <x-slot:head>
                <tr>
                    <th><a href="{{ $sortUrl('name') }}" class="admin-sort-link">Name <i class="bi bi-arrow-down-up"></i></a></th>
                    <th><a href="{{ $sortUrl('key') }}" class="admin-sort-link">Key <i class="bi bi-arrow-down-up"></i></a></th>
                    <th><a href="{{ $sortUrl('type') }}" class="admin-sort-link">Type <i class="bi bi-arrow-down-up"></i></a></th>
                    <th>Dependencies</th>
                    <th><a href="{{ $sortUrl('default_price') }}" class="admin-sort-link">Default Price <i class="bi bi-arrow-down-up"></i></a></th>
                    <th>Version</th>
                    <th><a href="{{ $sortUrl('status') }}" class="admin-sort-link">Status <i class="bi bi-arrow-down-up"></i></a></th>
                    <th>Action</th>
                </tr>
            </x-slot:head>

            @forelse ($features as $feature)
                <tr>
                    <td>{{ $feature->name }}</td>
                    <td><code>{{ $feature->key }}</code></td>
                    <td class="text-secondary">{{ ucfirst($feature->type) }}</td>
                    <td>
                        @forelse ($feature->dependencies ?? [] as $dep)
                            <span class="admin-badge admin-badge--info">{{ $dep }}</span>
                        @empty
                            <span class="text-secondary">—</span>
                        @endforelse
                    </td>
                    <td>{{ (float) $feature->default_price > 0 ? '৳'.number_format((float) $feature->default_price) : 'Free' }}</td>
                    <td class="text-secondary">{{ $feature->version }}</td>
                    <td>
                        @can('features.manage')
                            <form method="POST" action="{{ route('admin.features.status', $feature) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-select form-select-sm" style="width:auto;display:inline-block;" onchange="this.form.submit()">
                                    @foreach (\App\Models\Feature::STATUSES as $status)
                                        <option value="{{ $status }}" @selected($feature->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @else
                            <span class="admin-badge admin-badge--{{ $statusBadge[$feature->status] ?? 'info' }}">{{ ucfirst($feature->status) }}</span>
                        @endcan
                    </td>
                    <td>
                        <div class="admin-table-actions">
                            @can('features.manage')
                                <a href="{{ route('admin.features.edit', $feature) }}" class="admin-table-action" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.features.destroy', $feature) }}" onsubmit="return confirm('Delete this feature? This cannot be undone.')">
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
                    <td colspan="8" class="admin-table-empty">No features found.</td>
                </tr>
            @endforelse
        </x-admin::data-table>

        <div class="admin-pagination">{{ $features->links() }}</div>
    </div>
@endsection
