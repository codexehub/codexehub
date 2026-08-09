@php
    $featureRows = collect(old('features_text'))->isNotEmpty()
        ? collect(old('features_text'))->map(fn ($text, $i) => ['icon' => old('features_icon')[$i] ?? '', 'text' => $text])
        : ($service->relationLoaded('features') ? $service->features->map(fn ($f) => ['icon' => $f->icon, 'text' => $f->text]) : collect());
@endphp

@csrf
@if ($service->exists)
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-3">
        <label for="icon" class="form-label">Icon</label>
        <input type="text" name="icon" id="icon" value="{{ old('icon', $service->icon) }}" class="form-control @error('icon') is-invalid @enderror" placeholder="bi-code-slash" required>
        @error('icon')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">A <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener">Bootstrap Icons</a> class.</small>
    </div>

    <div class="col-md-9">
        <label for="title" class="form-label">Title</label>
        <input type="text" name="title" id="title" value="{{ old('title', $service->title) }}" class="form-control @error('title') is-invalid @enderror" placeholder="Web Development" required>
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="slug" class="form-label">Slug</label>
        <input type="text" name="slug" id="slug" value="{{ old('slug', $service->slug) }}" class="form-control @error('slug') is-invalid @enderror" placeholder="Auto-generated from the title if left blank">
        @error('slug')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Used for the <code>#service-{slug}</code> anchor linked from the navigation and footer.</small>
    </div>

    <div class="col-md-12">
        <label for="short_description" class="form-label">Short Description</label>
        <textarea name="short_description" id="short_description" rows="3" class="form-control @error('short_description') is-invalid @enderror" placeholder="Shown on the homepage and Services page card." required>{{ old('short_description', $service->short_description) }}</textarea>
        @error('short_description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="description" class="form-label">Full Description</label>
        <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" placeholder="Used on the service details page.">{{ old('description', $service->description) }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="image" class="form-label">Service Image</label>
        @if ($service->exists && $service->image_url)
            <div class="mb-2"><img src="{{ $service->image_url }}" alt="" style="max-width:160px;border-radius:8px;"></div>
        @endif
        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
        @error('image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Shown on the service details page hero (falls back to the icon if not set).</small>
    </div>

    <div class="col-md-12">
        <hr class="admin-form-divider">
        <h5 class="mb-3">What's Included</h5>

        <div data-repeater="features">
            @foreach ($featureRows as $row)
                <div class="admin-repeater-row d-flex gap-2 mb-2" data-repeater-row>
                    <input type="text" name="features_icon[]" value="{{ $row['icon'] }}" class="form-control" style="max-width:160px;" placeholder="bi-check-circle">
                    <input type="text" name="features_text[]" value="{{ $row['text'] }}" class="form-control" placeholder="Feature text">
                    <button type="button" class="btn btn-outline-danger" data-repeater-remove aria-label="Remove feature">&times;</button>
                </div>
            @endforeach
        </div>
        <template data-repeater-template="features">
            <div class="admin-repeater-row d-flex gap-2 mb-2" data-repeater-row>
                <input type="text" name="features_icon[]" class="form-control" style="max-width:160px;" placeholder="bi-check-circle">
                <input type="text" name="features_text[]" class="form-control" placeholder="Feature text">
                <button type="button" class="btn btn-outline-danger" data-repeater-remove aria-label="Remove feature">&times;</button>
            </div>
        </template>
        <button type="button" class="btn btn-sm btn-outline-primary mt-1" data-repeater-add="features">+ Add Feature</button>
    </div>

    <div class="col-md-12"><hr class="admin-form-divider"></div>

    <div class="col-md-6">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="active" @selected(old('status', $service->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $service->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Inactive services are hidden from both the homepage and the Services page.</small>
    </div>

    <div class="col-md-6">
        <label for="sort_order" class="form-label">Sort Order</label>
        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $service->sort_order ?? 0) }}" class="form-control @error('sort_order') is-invalid @enderror">
        @error('sort_order')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Lower numbers appear first.</small>
    </div>

    <div class="col-md-12">
        <div class="form-check">
            <input type="checkbox" name="featured" id="featured" value="1" class="form-check-input" @checked(old('featured', $service->featured)) >
            <label for="featured" class="form-check-label">Show on homepage (Featured)</label>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ $service->exists ? 'Update Service' : 'Create Service' }}</button>
    <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
