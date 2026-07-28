@php
    $highlightRows = collect(old('highlights_text'))->isNotEmpty()
        ? collect(old('highlights_text'))->map(fn ($text, $i) => ['icon' => old('highlights_icon')[$i] ?? '', 'text' => $text])
        : ($portfolio->relationLoaded('highlights') ? $portfolio->highlights->map(fn ($h) => ['icon' => $h->icon, 'text' => $h->text]) : collect());

    $resultRows = collect(old('results_value'))->isNotEmpty()
        ? collect(old('results_value'))->map(fn ($value, $i) => ['icon' => old('results_icon')[$i] ?? '', 'value' => $value, 'label' => old('results_label')[$i] ?? ''])
        : ($portfolio->relationLoaded('results') ? $portfolio->results->map(fn ($r) => ['icon' => $r->icon, 'value' => $r->value, 'label' => $r->label]) : collect());
@endphp

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
        <h5 class="mb-3">Business Challenge &amp; Solution</h5>

        <div class="mb-3">
            <label for="challenge" class="form-label">The Challenge</label>
            <textarea name="challenge" id="challenge" rows="3" class="form-control @error('challenge') is-invalid @enderror" placeholder="The client pain points and business goals this project solved.">{{ old('challenge', $portfolio->challenge) }}</textarea>
            @error('challenge')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="solution" class="form-label">Our Solution</label>
            <textarea name="solution" id="solution" rows="3" class="form-control @error('solution') is-invalid @enderror" placeholder="The approach, architecture and strategy used to solve it.">{{ old('solution', $portfolio->solution) }}</textarea>
            @error('solution')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <hr class="admin-form-divider">
        <h5 class="mb-3">Media</h5>

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
                <small class="text-secondary">Add more screenshots to the project gallery (shown as the details page slider).</small>
            </div>
        @endif

        <div class="mb-3">
            <label class="form-label">Case Study PDF</label>
            @if ($portfolio->exists && $portfolio->case_study_file)
                <div class="mb-2"><a href="{{ $portfolio->case_study_file_url }}" target="_blank" rel="noopener">View current file <i class="bi bi-box-arrow-up-right"></i></a></div>
            @endif
            <input type="file" name="case_study_file" class="form-control @error('case_study_file') is-invalid @enderror" accept="application/pdf">
            @error('case_study_file')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-secondary">Downloadable PDF for the details page's "Download Case Study" button.</small>
        </div>

        <hr class="admin-form-divider">
        <h5 class="mb-3">Project Highlights</h5>

        <div class="mb-3">
            <div data-repeater="highlights">
                @foreach ($highlightRows as $row)
                    <div class="admin-repeater-row d-flex gap-2 mb-2" data-repeater-row>
                        <input type="text" name="highlights_icon[]" value="{{ $row['icon'] }}" class="form-control" style="max-width:170px" placeholder="bi-shield-lock">
                        <input type="text" name="highlights_text[]" value="{{ $row['text'] }}" class="form-control" placeholder="Modern & responsive design for all devices">
                        <button type="button" class="btn btn-outline-danger" data-repeater-remove aria-label="Remove highlight">&times;</button>
                    </div>
                @endforeach
            </div>
            <template data-repeater-template="highlights">
                <div class="admin-repeater-row d-flex gap-2 mb-2" data-repeater-row>
                    <input type="text" name="highlights_icon[]" class="form-control" style="max-width:170px" placeholder="bi-shield-lock">
                    <input type="text" name="highlights_text[]" class="form-control" placeholder="Modern & responsive design for all devices">
                    <button type="button" class="btn btn-outline-danger" data-repeater-remove aria-label="Remove highlight">&times;</button>
                </div>
            </template>
            <button type="button" class="btn btn-sm btn-outline-primary mt-1" data-repeater-add="highlights">+ Add Highlight</button>
            <small class="text-secondary d-block mt-2">Icon = a <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener">Bootstrap Icons</a> class (e.g. <code>bi-shield-lock</code>). Leave a row's text blank to drop it on save.</small>
        </div>

        <hr class="admin-form-divider">
        <h5 class="mb-3">Results &amp; Impact</h5>

        <div class="mb-3">
            <div data-repeater="results">
                @foreach ($resultRows as $row)
                    <div class="admin-repeater-row d-flex gap-2 mb-2" data-repeater-row>
                        <input type="text" name="results_icon[]" value="{{ $row['icon'] }}" class="form-control" style="max-width:150px" placeholder="bi-graph-up-arrow">
                        <input type="text" name="results_value[]" value="{{ $row['value'] }}" class="form-control" style="max-width:110px" placeholder="150%">
                        <input type="text" name="results_label[]" value="{{ $row['label'] }}" class="form-control" placeholder="Increase in Online Sales">
                        <button type="button" class="btn btn-outline-danger" data-repeater-remove aria-label="Remove result">&times;</button>
                    </div>
                @endforeach
            </div>
            <template data-repeater-template="results">
                <div class="admin-repeater-row d-flex gap-2 mb-2" data-repeater-row>
                    <input type="text" name="results_icon[]" class="form-control" style="max-width:150px" placeholder="bi-graph-up-arrow">
                    <input type="text" name="results_value[]" class="form-control" style="max-width:110px" placeholder="150%">
                    <input type="text" name="results_label[]" class="form-control" placeholder="Increase in Online Sales">
                    <button type="button" class="btn btn-outline-danger" data-repeater-remove aria-label="Remove result">&times;</button>
                </div>
            </template>
            <button type="button" class="btn btn-sm btn-outline-primary mt-1" data-repeater-add="results">+ Add Result</button>
        </div>

        <hr class="admin-form-divider">
        <h5 class="mb-3">Client Review</h5>

        <div class="mb-3">
            <label for="testimonial_quote" class="form-label">Testimonial Quote</label>
            <textarea name="testimonial_quote" id="testimonial_quote" rows="3" class="form-control @error('testimonial_quote') is-invalid @enderror" placeholder="What the client said about working with CodexeHub.">{{ old('testimonial_quote', $portfolio->testimonial_quote) }}</textarea>
            @error('testimonial_quote')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <label for="testimonial_name" class="form-label">Client Name</label>
                <input type="text" name="testimonial_name" id="testimonial_name" value="{{ old('testimonial_name', $portfolio->testimonial_name) }}" class="form-control @error('testimonial_name') is-invalid @enderror">
                @error('testimonial_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label for="testimonial_role" class="form-label">Role / Company</label>
                <input type="text" name="testimonial_role" id="testimonial_role" value="{{ old('testimonial_role', $portfolio->testimonial_role) }}" class="form-control @error('testimonial_role') is-invalid @enderror" placeholder="CEO, FashionHub">
                @error('testimonial_role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label for="testimonial_rating" class="form-label">Rating</label>
                <select name="testimonial_rating" id="testimonial_rating" class="form-select @error('testimonial_rating') is-invalid @enderror">
                    @foreach ([5, 4, 3, 2, 1] as $rating)
                        <option value="{{ $rating }}" @selected(old('testimonial_rating', $portfolio->testimonial_rating ?? 5) == $rating)>{{ $rating }} Star{{ $rating > 1 ? 's' : '' }}</option>
                    @endforeach
                </select>
                @error('testimonial_rating')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3 mt-3">
            <label class="form-label">Client Photo</label>
            @if ($portfolio->exists && $portfolio->testimonial_avatar_url)
                <div class="mb-2"><img src="{{ $portfolio->testimonial_avatar_url }}" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:50%;"></div>
            @endif
            <input type="file" name="testimonial_avatar" class="form-control @error('testimonial_avatar') is-invalid @enderror" accept="image/*">
            @error('testimonial_avatar')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-secondary">Optional — falls back to initials if not set.</small>
        </div>
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
            <label for="industry" class="form-label">Industry</label>
            <input type="text" name="industry" id="industry" value="{{ old('industry', $portfolio->industry) }}" class="form-control @error('industry') is-invalid @enderror" placeholder="E-Commerce, Retail">
            @error('industry')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" name="location" id="location" value="{{ old('location', $portfolio->location) }}" class="form-control @error('location') is-invalid @enderror" placeholder="Bangladesh">
            @error('location')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="row g-3">
            <div class="col-6">
                <label for="duration" class="form-label">Project Duration</label>
                <input type="text" name="duration" id="duration" value="{{ old('duration', $portfolio->duration) }}" class="form-control @error('duration') is-invalid @enderror" placeholder="4 Months">
                @error('duration')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-6">
                <label for="team_size" class="form-label">Team Size</label>
                <input type="text" name="team_size" id="team_size" value="{{ old('team_size', $portfolio->team_size) }}" class="form-control @error('team_size') is-invalid @enderror" placeholder="6 Members">
                @error('team_size')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3 mt-3">
            <label for="completed_date" class="form-label">Completed Date</label>
            <input type="date" name="completed_date" id="completed_date" value="{{ old('completed_date', optional($portfolio->completed_date)->format('Y-m-d')) }}" class="form-control @error('completed_date') is-invalid @enderror">
            @error('completed_date')
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
