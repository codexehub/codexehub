@extends('admin.layouts.app', ['title' => 'Service Management'])

@section('content')
    @php
        $sortUrl = fn (string $column) => request()->fullUrlWithQuery([
            'sort' => $column,
            'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc',
        ]);
    @endphp

    <div class="admin-page-heading d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>Service Management</h1>
            <p>Manage the services listed on the homepage and the public Services page.</p>
        </div>
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Service</a>
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
        <form method="GET" action="{{ route('admin.services.index') }}" class="admin-panel-header admin-panel-header--toolbar flex-wrap gap-3">
            <div class="admin-table-search">
                <i class="bi bi-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title...">
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th><a href="{{ $sortUrl('title') }}" class="admin-sort-link">Title <i class="bi bi-arrow-down-up"></i></a></th>
                        <th>Anchor</th>
                        <th><a href="{{ $sortUrl('featured') }}" class="admin-sort-link">Homepage <i class="bi bi-arrow-down-up"></i></a></th>
                        <th><a href="{{ $sortUrl('status') }}" class="admin-sort-link">Status <i class="bi bi-arrow-down-up"></i></a></th>
                        <th><a href="{{ $sortUrl('sort_order') }}" class="admin-sort-link">Sort Order <i class="bi bi-arrow-down-up"></i></a></th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td><i class="bi {{ $service->icon }} text-primary" style="font-size:1.4rem;"></i></td>
                            <td>{{ $service->title }}</td>
                            <td><code>#service-{{ $service->slug }}</code></td>
                            <td>
                                <form method="POST" action="{{ route('admin.services.featured', $service) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="admin-table-action" title="Toggle homepage visibility">
                                        <i class="bi {{ $service->featured ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.services.status', $service) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="admin-badge admin-badge--{{ $service->status === 'active' ? 'success' : 'danger' }}" style="border:0;cursor:pointer;">
                                        {{ ucfirst($service->status) }}
                                    </button>
                                </form>
                            </td>
                            <td>{{ $service->sort_order }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.services.edit', $service) }}" class="admin-table-action" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Delete this service? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-table-action admin-table-action--danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="admin-table-empty">No services found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">{{ $services->links() }}</div>
    </div>
@endsection
