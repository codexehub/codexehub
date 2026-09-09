@php
    $featureRows = collect(old('features_text'))->isNotEmpty()
        ? collect(old('features_text'))->map(fn ($text, $i) => ['icon' => old('features_icon')[$i] ?? '', 'text' => $text])
        : ($product->relationLoaded('features') ? $product->features->map(fn ($f) => ['icon' => $f->icon, 'text' => $f->text]) : collect());
@endphp

@csrf
@if ($product->exists)
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-4">
        <label for="type" class="form-label">Product Type</label>
        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
            <option value="software" @selected(old('type', $product->type ?? 'software') === 'software')>Software Product</option>
            <option value="physical" disabled>Physical Product (coming soon)</option>
        </select>
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Physical products unlock with the inventory module.</small>
    </div>

    <div class="col-md-4">
        <label for="product_category_id" class="form-label">Category</label>
        <select name="product_category_id" id="product_category_id" class="form-select @error('product_category_id') is-invalid @enderror">
            <option value="">— No category —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('product_category_id', $product->product_category_id) === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('product_category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary"><a href="{{ route('admin.products.categories.index') }}">Manage categories</a></small>
    </div>

    <div class="col-md-4">
        <label for="icon" class="form-label">Icon</label>
        <input type="text" name="icon" id="icon" value="{{ old('icon', $product->icon ?? 'bi-box-seam') }}" class="form-control @error('icon') is-invalid @enderror" placeholder="bi-box-seam" required>
        @error('icon')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">A <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener">Bootstrap Icons</a> class.</small>
    </div>

    <div class="col-md-6">
        <label for="title" class="form-label">Title</label>
        <input type="text" name="title" id="title" value="{{ old('title', $product->title) }}" class="form-control @error('title') is-invalid @enderror" placeholder="CodexeERP" required>
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="slug" class="form-label">Slug</label>
        <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug) }}" class="form-control @error('slug') is-invalid @enderror" placeholder="Auto from title">
        @error('slug')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Public URL <code>/products/{slug}</code> — safe to change.</small>
    </div>

    <div class="col-md-3">
        <label for="key" class="form-label">Control-plane Key</label>
        <input type="text" name="key" id="key" value="{{ old('key', $product->key) }}" class="form-control @error('key') is-invalid @enderror" placeholder="Auto from slug">
        @error('key')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Stable machine id — packages, offers, releases and the Vendor API bind to this. Change with care.</small>
    </div>

    <div class="col-md-12">
        <label for="tagline" class="form-label">Tagline</label>
        <input type="text" name="tagline" id="tagline" value="{{ old('tagline', $product->tagline) }}" class="form-control @error('tagline') is-invalid @enderror" placeholder="One system for accounts, inventory and operations">
        @error('tagline')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">A short line shown under the product name on the details page.</small>
    </div>

    <div class="col-md-12">
        <label for="short_description" class="form-label">Short Description</label>
        <textarea name="short_description" id="short_description" rows="3" class="form-control @error('short_description') is-invalid @enderror" placeholder="Shown on the homepage and Products page card." required>{{ old('short_description', $product->short_description) }}</textarea>
        @error('short_description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="description" class="form-label">Full Description</label>
        <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" placeholder="Used on the product details page.">{{ old('description', $product->description) }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12"><hr class="admin-form-divider"><h5 class="mb-0">Pricing &amp; Links</h5></div>

    <div class="col-md-4">
        <label for="price" class="form-label">Price</label>
        <input type="number" step="0.01" min="0" name="price" id="price" value="{{ old('price', $product->price) }}" class="form-control @error('price') is-invalid @enderror" placeholder="1200">
        @error('price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Leave blank to show "Custom Pricing".</small>
    </div>

    <div class="col-md-4">
        <label for="price_unit" class="form-label">Price Unit</label>
        <input type="text" name="price_unit" id="price_unit" value="{{ old('price_unit', $product->price_unit) }}" class="form-control @error('price_unit') is-invalid @enderror" placeholder="one-time / per month">
        @error('price_unit')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="version" class="form-label">Version</label>
        <input type="text" name="version" id="version" value="{{ old('version', $product->version) }}" class="form-control @error('version') is-invalid @enderror" placeholder="4.2">
        @error('version')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="pricing_note" class="form-label">Pricing Note</label>
        <input type="text" name="pricing_note" id="pricing_note" value="{{ old('pricing_note', $product->pricing_note) }}" class="form-control @error('pricing_note') is-invalid @enderror" placeholder="Includes 12 months of updates and priority support.">
        @error('pricing_note')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="demo_url" class="form-label">Live Demo URL</label>
        <input type="url" name="demo_url" id="demo_url" value="{{ old('demo_url', $product->demo_url) }}" class="form-control @error('demo_url') is-invalid @enderror" placeholder="https://demo.codexehub.com/erp">
        @error('demo_url')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="docs_url" class="form-label">Documentation URL</label>
        <input type="url" name="docs_url" id="docs_url" value="{{ old('docs_url', $product->docs_url) }}" class="form-control @error('docs_url') is-invalid @enderror" placeholder="https://docs.codexehub.com/erp">
        @error('docs_url')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12"><hr class="admin-form-divider"><h5 class="mb-0">Media</h5></div>

    <div class="col-md-6">
        <label for="image" class="form-label">Cover Image</label>
        @if ($product->exists && $product->image_url)
            <div class="mb-2"><img src="{{ $product->image_url }}" alt="" style="max-width:160px;border-radius:8px;"></div>
        @endif
        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
        @error('image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Shown on the product card and details hero (falls back to the icon if not set).</small>
    </div>

    <div class="col-md-6">
        <label for="screenshots" class="form-label">Screenshots</label>
        <input type="file" name="screenshots[]" id="screenshots" class="form-control @error('screenshots.*') is-invalid @enderror" accept="image/*" multiple>
        @error('screenshots.*')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Added to the screenshots carousel on the details page.</small>

        @if ($product->exists && $product->relationLoaded('images') && $product->images->isNotEmpty())
            <div class="d-flex flex-wrap gap-2 mt-2">
                @foreach ($product->images as $image)
                    <div class="position-relative">
                        <img src="{{ $image->url }}" alt="" style="width:96px;height:64px;object-fit:cover;border-radius:6px;">
                        <button type="submit" form="delete-product-image-{{ $image->id }}" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0" aria-label="Remove screenshot">&times;</button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="col-md-12">
        <hr class="admin-form-divider">
        <h5 class="mb-3">Key Features</h5>

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
            <option value="active" @selected(old('status', $product->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $product->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Inactive products are hidden from the homepage and the Products pages.</small>
    </div>

    <div class="col-md-6">
        <label for="sort_order" class="form-label">Sort Order</label>
        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" class="form-control @error('sort_order') is-invalid @enderror">
        @error('sort_order')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Lower numbers appear first.</small>
    </div>

    <div class="col-md-12">
        <div class="form-check">
            <input type="checkbox" name="featured" id="featured" value="1" class="form-check-input" @checked(old('featured', $product->featured)) >
            <label for="featured" class="form-check-label">Show on homepage (Featured)</label>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ $product->exists ? 'Update Product' : 'Create Product' }}</button>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
