@extends('admin.layouts.app', ['title' => 'Edit Product Category'])

@section('content')
    <div class="admin-page-heading">
        <h1>Edit Category</h1>
        <p>Update "{{ $category->name }}".</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.products.categories.update', $category) }}" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-md-6">
                <label for="name" class="form-label">Category Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="slug" class="form-label">Slug</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $category->slug) }}" class="form-control @error('slug') is-invalid @enderror" required>
                @error('slug')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="type" class="form-label">Type</label>
                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
                    <option value="software" @selected(old('type', $category->type) === 'software')>Software</option>
                    <option value="physical" @selected(old('type', $category->type) === 'physical')>Physical</option>
                </select>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="sort_order" class="form-label">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $category->sort_order) }}" class="form-control @error('sort_order') is-invalid @enderror">
                @error('sort_order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Category</button>
                <a href="{{ route('admin.products.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
