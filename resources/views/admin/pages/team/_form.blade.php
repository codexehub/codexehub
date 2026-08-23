@csrf
@if ($member->exists)
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $member->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="Rashed Hossain" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="role" class="form-label">Role / Position</label>
        <input type="text" name="role" id="role" value="{{ old('role', $member->role) }}" class="form-control @error('role') is-invalid @enderror" placeholder="CEO & Founder" required>
        @error('role')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" id="description" rows="5" class="form-control @error('description') is-invalid @enderror" placeholder="A short bio shown in a popup when a visitor clicks the info icon on this member's card.">{{ old('description', $member->description) }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Only shown when non-empty — an info icon appears on the card that opens it in a modal.</small>
    </div>

    <div class="col-md-6">
        <label for="linkedin_url" class="form-label">LinkedIn URL</label>
        <input type="url" name="linkedin_url" id="linkedin_url" value="{{ old('linkedin_url', $member->linkedin_url) }}" class="form-control @error('linkedin_url') is-invalid @enderror" placeholder="https://www.linkedin.com/in/...">
        @error('linkedin_url')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="github_url" class="form-label">GitHub URL</label>
        <input type="url" name="github_url" id="github_url" value="{{ old('github_url', $member->github_url) }}" class="form-control @error('github_url') is-invalid @enderror" placeholder="https://github.com/...">
        @error('github_url')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12"><hr class="admin-form-divider"><h5 class="mb-0">Media</h5></div>

    <div class="col-md-6">
        <label for="photo" class="form-label">Photo</label>
        @if ($member->exists && $member->photo_url)
            <div class="mb-2"><img src="{{ $member->photo_url }}" alt="" style="width:96px;height:96px;object-fit:cover;border-radius:50%;"></div>
        @endif
        <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
        @error('photo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Shown on the team card (falls back to a placeholder icon if not set).</small>
    </div>

    <div class="col-md-6">
        <label for="gallery" class="form-label">Additional Photos</label>
        <input type="file" name="gallery[]" id="gallery" class="form-control @error('gallery.*') is-invalid @enderror" accept="image/*" multiple>
        @error('gallery.*')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Shown in the bio popup alongside the description.</small>

        @if ($member->exists && $member->relationLoaded('images') && $member->images->isNotEmpty())
            <div class="d-flex flex-wrap gap-2 mt-2">
                @foreach ($member->images as $image)
                    <div class="position-relative">
                        <img src="{{ $image->url }}" alt="" style="width:96px;height:64px;object-fit:cover;border-radius:6px;">
                        <button type="submit" form="delete-team-image-{{ $image->id }}" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0" aria-label="Remove photo">&times;</button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="col-md-12"><hr class="admin-form-divider"></div>

    <div class="col-md-6">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="active" @selected(old('status', $member->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $member->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Inactive members are hidden from the About Us page.</small>
    </div>

    <div class="col-md-6">
        <label for="sort_order" class="form-label">Sort Order</label>
        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $member->sort_order ?? 0) }}" class="form-control @error('sort_order') is-invalid @enderror">
        @error('sort_order')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-secondary">Lower numbers appear first.</small>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ $member->exists ? 'Update Team Member' : 'Add Team Member' }}</button>
    <a href="{{ route('admin.team.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
