@csrf
@if ($package->exists)
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-12">
        <label for="licensed_app_id" class="form-label">Licensed App</label>
        <select name="licensed_app_id" id="licensed_app_id" class="form-select @error('licensed_app_id') is-invalid @enderror" required>
            <option value="">Select an app</option>
            @foreach ($licensedApps as $app)
                <option value="{{ $app->id }}" @selected(old('licensed_app_id', $package->licensed_app_id) == $app->id)>{{ $app->name }}</option>
            @endforeach
        </select>
        @error('licensed_app_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $package->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="Basic / Pro / Enterprise" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="price" class="form-label">Price</label>
        <input type="number" step="0.01" name="price" id="price" value="{{ old('price', $package->price) }}" class="form-control @error('price') is-invalid @enderror" required>
        @error('price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="duration_days" class="form-label">Duration (days)</label>
        <input type="number" name="duration_days" id="duration_days" value="{{ old('duration_days', $package->duration_days ?? 365) }}" class="form-control @error('duration_days') is-invalid @enderror" required>
        @error('duration_days')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="max_activations" class="form-label">Max Activations</label>
        <input type="number" name="max_activations" id="max_activations" value="{{ old('max_activations', $package->max_activations ?? 1) }}" class="form-control @error('max_activations') is-invalid @enderror" required>
        @error('max_activations')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Installs allowed per license.</small>
    </div>

    <div class="col-md-12">
        <label for="features" class="form-label">Features</label>
        <input type="text" name="features" id="features" value="{{ old('features', $package->features ? implode(', ', $package->features) : '') }}" class="form-control @error('features') is-invalid @enderror" placeholder="multi-currency, pdf-invoices, priority-support">
        @error('features')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Comma-separated feature flags, sent to the product in the license token.</small>
    </div>

    <div class="col-md-12">
        <div class="form-check">
            <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input" @checked(old('is_active', $package->is_active ?? true))>
            <label for="is_active" class="form-check-label">Active</label>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ $package->exists ? 'Update Package' : 'Create Package' }}</button>
    <a href="{{ route('admin.licensing.packages.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
