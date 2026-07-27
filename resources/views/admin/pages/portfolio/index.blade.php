@extends('admin.layouts.app', ['title' => 'Portfolio Management'])

@section('content')
    @php
        $sortUrl = fn (string $column) => request()->fullUrlWithQuery([
            'sort' => $column,
            'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc',
        ]);
    @endphp

    <div class="admin-page-heading d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>Portfolio Management</h1>
            <p>Manage the projects shown on the homepage and the public portfolio page.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.portfolio.categories.index') }}" class="btn btn-outline-secondary"><i class="bi bi-tags"></i> Categories</a>
            <a href="{{ route('admin.portfolio.settings') }}" class="btn btn-outline-secondary"><i class="bi bi-sliders"></i> Homepage Settings</a>
            <a href="{{ route('admin.portfolio.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Project</a>
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
        <form method="GET" action="{{ route('admin.portfolio.index') }}" class="admin-panel-header admin-panel-header--toolbar flex-wrap gap-3">
            <div class="admin-table-search">
                <i class="bi bi-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title or client...">
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if (request()->hasAny(['search', 'category', 'status']))
                    <a href="{{ route('admin.portfolio.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>

        <form id="bulk-form" method="POST" action="{{ route('admin.portfolio.bulk') }}" class="d-flex align-items-center gap-2 mb-3" onsubmit="return confirm('Apply this action to the selected projects?')">
            @csrf
            <select name="action" class="form-select" style="max-width: 220px">
                <option value="">Bulk Action</option>
                <option value="activate">Set Active</option>
                <option value="deactivate">Set Inactive</option>
                <option value="delete">Delete</option>
            </select>
            <button type="submit" class="btn btn-outline-secondary">Apply</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" data-select-all="ids[]"></th>
                        <th>Image</th>
                        <th><a href="{{ $sortUrl('title') }}" class="admin-sort-link">Project Name <i class="bi bi-arrow-down-up"></i></a></th>
                        <th>Category</th>
                        <th><a href="{{ $sortUrl('client_name') }}" class="admin-sort-link">Client <i class="bi bi-arrow-down-up"></i></a></th>
                        <th><a href="{{ $sortUrl('featured') }}" class="admin-sort-link">Featured <i class="bi bi-arrow-down-up"></i></a></th>
                        <th><a href="{{ $sortUrl('status') }}" class="admin-sort-link">Status <i class="bi bi-arrow-down-up"></i></a></th>
                        <th><a href="{{ $sortUrl('sort_order') }}" class="admin-sort-link">Sort Order <i class="bi bi-arrow-down-up"></i></a></th>
                        <th><a href="{{ $sortUrl('created_at') }}" class="admin-sort-link">Created <i class="bi bi-arrow-down-up"></i></a></th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($portfolios as $portfolio)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $portfolio->id }}" form="bulk-form"></td>
                            <td>
                                @if ($portfolio->thumbnail_url)
                                    <img src="{{ $portfolio->thumbnail_url }}" alt="" style="width:56px;height:42px;object-fit:cover;border-radius:6px;">
                                @else
                                    <i class="bi bi-image text-secondary" style="font-size:1.4rem;"></i>
                                @endif
                            </td>
                            <td>{{ $portfolio->title }}</td>
                            <td>{{ $portfolio->category->name }}</td>
                            <td>{{ $portfolio->client_name ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.portfolio.featured', $portfolio) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="admin-table-action" title="Toggle homepage visibility">
                                        <i class="bi {{ $portfolio->featured ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.portfolio.status', $portfolio) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="admin-badge admin-badge--{{ $portfolio->status === 'active' ? 'success' : 'danger' }}" style="border:0;cursor:pointer;">
                                        {{ ucfirst($portfolio->status) }}
                                    </button>
                                </form>
                            </td>
                            <td>{{ $portfolio->sort_order }}</td>
                            <td>{{ $portfolio->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('portfolio.show', $portfolio) }}" target="_blank" class="admin-table-action" title="View"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('admin.portfolio.edit', $portfolio) }}" class="admin-table-action" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('admin.portfolio.duplicate', $portfolio) }}">
                                        @csrf
                                        <button type="submit" class="admin-table-action" title="Duplicate"><i class="bi bi-copy"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.portfolio.destroy', $portfolio) }}" onsubmit="return confirm('Delete this project? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-table-action admin-table-action--danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="admin-table-empty">No projects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">{{ $portfolios->links() }}</div>
    </div>
@endsection
