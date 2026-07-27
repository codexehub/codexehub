@extends('admin.layouts.app', ['title' => 'Portfolio Categories'])

@section('content')
    <div class="admin-page-heading d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>Portfolio Categories</h1>
            <p>Manage the categories used to organize and filter portfolio projects.</p>
        </div>
        <a href="{{ route('admin.portfolio.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Projects</a>
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
        <form method="POST" action="{{ route('admin.portfolio.categories.store') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-6">
                <label for="name" class="form-label">Category Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Web Application" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
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
                        <th>Projects</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->slug }}</td>
                            <td>{{ $category->portfolios_count }}</td>
                            <td>
                                <span class="admin-badge admin-badge--{{ $category->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($category->status) }}</span>
                            </td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.portfolio.categories.edit', $category) }}" class="admin-table-action" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('admin.portfolio.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-table-action admin-table-action--danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="admin-table-empty">No categories yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">{{ $categories->links() }}</div>
    </div>
@endsection
