@php
    $selectedDeps = collect(old('dependencies', $feature->dependencies ?? []))->map(fn ($d) => (string) $d)->all();
    $schemaValue = old('config_schema', $feature->config_schema
        ? json_encode($feature->config_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        : '');
@endphp

@csrf
@if ($feature->exists)
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $feature->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="Payment Gateway" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="key" class="form-label">Machine Key</label>
        <input type="text" name="key" id="key" value="{{ old('key', $feature->key) }}" class="form-control @error('key') is-invalid @enderror" placeholder="Auto from name (e.g. payment_gateway)">
        @error('key')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Lower-case, digits and underscores. Packages, feature sync and the Vendor API bind to this — change with care.</small>
    </div>

    <div class="col-md-4">
        <label for="type" class="form-label">Type</label>
        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach (\App\Models\Feature::TYPES as $type)
                <option value="{{ $type }}" @selected(old('type', $feature->type ?? 'module') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach (\App\Models\Feature::STATUSES as $status)
                <option value="{{ $status }}" @selected(old('status', $feature->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Deprecated features stay assignable to existing packages but are hidden from new ones.</small>
    </div>

    <div class="col-md-2">
        <label for="version" class="form-label">Version</label>
        <input type="text" name="version" id="version" value="{{ old('version', $feature->version ?? '1.0.0') }}" class="form-control @error('version') is-invalid @enderror" required>
        @error('version')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-2">
        <label for="default_price" class="form-label">Default Price</label>
        <input type="number" step="0.01" min="0" name="default_price" id="default_price" value="{{ old('default_price', $feature->default_price ?? 0) }}" class="form-control @error('default_price') is-invalid @enderror">
        @error('default_price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Per-package price can override this.</small>
    </div>

    <div class="col-md-12">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" id="description" rows="2" class="form-control @error('description') is-invalid @enderror" placeholder="What this feature does, in one or two sentences.">{{ old('description', $feature->description) }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <hr class="admin-form-divider">
        <h5 class="mb-2">Dependencies</h5>
        <label for="dependencies" class="form-label">Requires these features</label>
        <select name="dependencies[]" id="dependencies" multiple size="6" class="form-select @error('dependencies') is-invalid @enderror">
            @forelse ($featureOptions as $option)
                <option value="{{ $option->key }}" @selected(in_array($option->key, $selectedDeps, true))>{{ $option->name }} ({{ $option->key }})</option>
            @empty
                <option disabled>No other features in the catalog yet.</option>
            @endforelse
        </select>
        @error('dependencies')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        <small class="text-secondary">A package that enables this feature must also enable everything selected here. Self- and circular dependencies are rejected.</small>

        @isset($dependents)
            @if ($dependents->isNotEmpty())
                <p class="mt-2 mb-0 small text-secondary">Depended on by: {{ $dependents->pluck('name')->join(', ') }}.</p>
            @endif
        @endisset
    </div>

    <div class="col-md-12">
        <hr class="admin-form-divider">
        <h5 class="mb-2">Configuration Schema</h5>
        <label for="config_schema" class="form-label">JSON schema for per-package configuration</label>
        <textarea name="config_schema" id="config_schema" rows="10" class="form-control font-monospace @error('config_schema') is-invalid @enderror" placeholder='{&#10;  "type": "object",&#10;  "properties": {&#10;    "provider": { "type": "string", "enum": ["pathao", "steadfast"] },&#10;    "inside_city_charge": { "type": "number" }&#10;  },&#10;  "required": ["provider"]&#10;}'>{{ $schemaValue }}</textarea>
        @error('config_schema')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Must be a JSON object declaring <code>type</code> or <code>properties</code>. Per-package config values are validated against this (SRS §79). Leave blank if the feature has no configuration.</small>
    </div>

    <div class="col-md-12">
        <label for="documentation" class="form-label">Documentation</label>
        <textarea name="documentation" id="documentation" rows="3" class="form-control @error('documentation') is-invalid @enderror" placeholder="Setup notes, links, gotchas — shown to the team, not the customer.">{{ old('documentation', $feature->documentation) }}</textarea>
        @error('documentation')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ $feature->exists ? 'Update Feature' : 'Create Feature' }}</button>
    <a href="{{ route('admin.features.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
