@extends('admin.layouts.app', ['title' => 'Product Categories'])

@section('content')
    <div class="admin-page-heading d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>Product Categories</h1>
            <p>Manage the categories used to group and filter products on the public Products page.</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Products</a>
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

    <div class="admin-panel admin-panel--narrow">
        <div class="admin-panel-header">
            <h2>Add Category</h2>
        </div>
        <form method="POST" action="{{ route('admin.products.categories.store') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-5">
                <label for="name" class="form-label">Category Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="ERP Solutions" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label for="type" class="form-label">Type</label>
                <select name="type" id="type" class="form-select">
                    <option value="software" @selected(old('type', 'software') === 'software')>Software</option>
                    <option value="physical" @selected(old('type') === 'physical')>Physical</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="sort_order" class="form-label">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Add</button>
            </div>
        </form>
    </div>

    <div class="admin-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Type</th>
                        <th>Products</th>
                        <th>Sort Order</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td><span class="admin-badge admin-badge--{{ $category->type === 'software' ? 'success' : 'warning' }}">{{ ucfirst($category->type) }}</span></td>
                            <td>{{ $category->products_count }}</td>
                            <td>{{ $category->sort_order }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.products.categories.edit', $category) }}" class="admin-table-action" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('admin.products.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-table-action admin-table-action--danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-table-empty">No categories yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">{{ $categories->links() }}</div>
    </div>
@endsection
