@extends('admin.layouts.app', ['title' => 'Product Management'])

@section('content')
    @php
        $sortUrl = fn (string $column) => request()->fullUrlWithQuery([
            'sort' => $column,
            'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc',
        ]);
    @endphp

    <div class="admin-page-heading d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>Product Management</h1>
            <p>Manage the software products listed on the homepage and the public Products pages.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.products.categories.index') }}" class="btn btn-outline-secondary"><i class="bi bi-tags"></i> Categories</a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Product</a>
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
        <form method="GET" action="{{ route('admin.products.index') }}" class="admin-panel-header admin-panel-header--toolbar flex-wrap gap-3">
            <div class="admin-table-search">
                <i class="bi bi-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title...">
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="software" @selected(request('type') === 'software')>Software</option>
                    <option value="physical" @selected(request('type') === 'physical')>Physical</option>
                </select>
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if (request()->hasAny(['search', 'status', 'type', 'category']))
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th><a href="{{ $sortUrl('title') }}" class="admin-sort-link">Title <i class="bi bi-arrow-down-up"></i></a></th>
                        <th><a href="{{ $sortUrl('type') }}" class="admin-sort-link">Type <i class="bi bi-arrow-down-up"></i></a></th>
                        <th>Category</th>
                        <th>Price</th>
                        <th><a href="{{ $sortUrl('featured') }}" class="admin-sort-link">Homepage <i class="bi bi-arrow-down-up"></i></a></th>
                        <th><a href="{{ $sortUrl('status') }}" class="admin-sort-link">Status <i class="bi bi-arrow-down-up"></i></a></th>
                        <th><a href="{{ $sortUrl('sort_order') }}" class="admin-sort-link">Sort Order <i class="bi bi-arrow-down-up"></i></a></th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:6px;">
                                @else
                                    <i class="bi {{ $product->icon }} text-primary" style="font-size:1.4rem;"></i>
                                @endif
                            </td>
                            <td>{{ $product->title }}</td>
                            <td><span class="admin-badge admin-badge--{{ $product->type === 'software' ? 'success' : 'warning' }}">{{ ucfirst($product->type) }}</span></td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>{{ $product->price_label }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.products.featured', $product) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="admin-table-action" title="Toggle homepage visibility">
                                        <i class="bi {{ $product->featured ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.products.status', $product) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="admin-badge admin-badge--{{ $product->status === 'active' ? 'success' : 'danger' }}" style="border:0;cursor:pointer;">
                                        {{ ucfirst($product->status) }}
                                    </button>
                                </form>
                            </td>
                            <td>{{ $product->sort_order }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('products.show', $product) }}" class="admin-table-action" target="_blank" rel="noopener" title="View on site"><i class="bi bi-box-arrow-up-right"></i></a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="admin-table-action" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-table-action admin-table-action--danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="admin-table-empty">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">{{ $products->links() }}</div>
    </div>
@endsection
