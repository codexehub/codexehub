@csrf
@if ($licensedApp->exists)
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-12">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $licensedApp->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="Ecommerce Laravel" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="slug" class="form-label">Slug</label>
        <input type="text" name="slug" id="slug" value="{{ old('slug', $licensedApp->slug) }}" class="form-control @error('slug') is-invalid @enderror" placeholder="ecommerce-laravel" required>
        @error('slug')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Must match <code>CODEXEHUB_PRODUCT_SLUG</code> configured in the product's <code>.env</code>.</small>
    </div>

    <div class="col-md-12">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $licensedApp->description) }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <div class="form-check">
            <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input" @checked(old('is_active', $licensedApp->is_active ?? true))>
            <label for="is_active" class="form-check-label">Active</label>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ $licensedApp->exists ? 'Update App' : 'Create App' }}</button>
    <a href="{{ route('admin.licensing.apps.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
