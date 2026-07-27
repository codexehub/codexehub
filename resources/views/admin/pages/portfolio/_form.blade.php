@csrf
@if ($portfolio->exists)
    @method('PUT')
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="mb-3">
            <label for="title" class="form-label">Project Title</label>
            <input type="text" name="title" id="title" value="{{ old('title', $portfolio->title) }}" class="form-control @error('title') is-invalid @enderror" placeholder="E-Commerce Platform" required>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $portfolio->slug) }}" class="form-control @error('slug') is-invalid @enderror" placeholder="Auto-generated from the title if left blank">
            @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="short_description" class="form-label">Short Description</label>
            <textarea name="short_description" id="short_description" rows="2" class="form-control @error('short_description') is-invalid @enderror" placeholder="Used for the homepage/portfolio card." required>{{ old('short_description', $portfolio->short_description) }}</textarea>
            @error('short_description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Full Description</label>
            <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" placeholder="Used on the project details page.">{{ old('description', $portfolio->description) }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Technologies</label>
            <div class="admin-tag-input" data-tag-input data-initial="{{ old('technologies', $portfolio->relationLoaded('technologies') ? $portfolio->technologies->pluck('technology_name')->implode(',') : '') }}">
                <div class="admin-tag-input-chips" data-tag-input-chips></div>
                <input type="text" class="form-control" data-tag-input-field placeholder="Type a technology and press Enter (e.g. Laravel)">
                <input type="hidden" name="technologies" data-tag-input-value>
            </div>
        </div>

        <hr class="admin-form-divider">

        <div class="mb-3">
            <label class="form-label">Thumbnail Image</label>
            @if ($portfolio->exists && $portfolio->thumbnail_url)
                <div class="mb-2"><img src="{{ $portfolio->thumbnail_url }}" alt="" style="max-width:160px;border-radius:8px;"></div>
            @endif
            <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" accept="image/*">
            @error('thumbnail')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-secondary">Shown on homepage and portfolio grid cards. Uploading a new image replaces the current one.</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Banner Image</label>
            @if ($portfolio->exists && $portfolio->images->firstWhere('type', 'banner'))
                <div class="mb-2"><img src="{{ $portfolio->images->firstWhere('type', 'banner')->url }}" alt="" style="max-width:220px;border-radius:8px;"></div>
            @endif
            <input type="file" name="banner" class="form-control @error('banner') is-invalid @enderror" accept="image/*">
            @error('banner')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-secondary">Optional wide image shown on the project details page hero (falls back to the thumbnail if not set).</small>
        </div>

        @if ($portfolio->exists)
            <div class="mb-3">
                <label class="form-label">Gallery Images</label>
                @if ($portfolio->images->where('type', 'gallery')->count())
                    <div class="d-flex flex-wrap gap-3 mb-3">
                        @foreach ($portfolio->images->where('type', 'gallery') as $image)
                            <div class="text-center">
                                <img src="{{ $image->url }}" alt="" style="width:100px;height:75px;object-fit:cover;border-radius:6px;">
                                <form method="POST" action="{{ route('admin.portfolio.images.destroy', $image) }}" onsubmit="return confirm('Remove this image?')" class="mt-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
                <input type="file" name="gallery[]" class="form-control @error('gallery.*') is-invalid @enderror" accept="image/*" multiple>
                @error('gallery.*')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <small class="text-secondary">Add more screenshots to the project gallery (shown on the details page).</small>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">Select a category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $portfolio->category_id) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="client_name" class="form-label">Client Name</label>
            <input type="text" name="client_name" id="client_name" value="{{ old('client_name', $portfolio->client_name) }}" class="form-control @error('client_name') is-invalid @enderror">
            @error('client_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="project_url" class="form-label">Live Project URL</label>
            <input type="url" name="project_url" id="project_url" value="{{ old('project_url', $portfolio->project_url) }}" class="form-control @error('project_url') is-invalid @enderror" placeholder="https://">
            @error('project_url')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="github_url" class="form-label">GitHub URL</label>
            <input type="url" name="github_url" id="github_url" value="{{ old('github_url', $portfolio->github_url) }}" class="form-control @error('github_url') is-invalid @enderror" placeholder="https://">
            @error('github_url')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="active" @selected(old('status', $portfolio->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $portfolio->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="sort_order" class="form-label">Sort Order</label>
            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $portfolio->sort_order ?? 0) }}" class="form-control @error('sort_order') is-invalid @enderror">
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-secondary">Lower numbers appear first.</small>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="featured" id="featured" value="1" class="form-check-input" @checked(old('featured', $portfolio->featured)) >
            <label for="featured" class="form-check-label">Show on homepage (Featured)</label>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-2">
    <button type="submit" class="btn btn-primary">{{ $portfolio->exists ? 'Update Project' : 'Create Project' }}</button>
    <a href="{{ route('admin.portfolio.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
