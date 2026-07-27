@extends('admin.layouts.app', ['title' => 'Homepage Portfolio Settings'])

@section('content')
    <div class="admin-page-heading d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>Homepage Portfolio Settings</h1>
            <p>Control how the Featured Projects section appears on the homepage.</p>
        </div>
        <a href="{{ route('admin.portfolio.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Projects</a>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.portfolio.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="form-check form-switch mb-3">
                <input type="checkbox" name="section_visible" id="section_visible" value="1" class="form-check-input" role="switch" @checked(old('section_visible', $settings->section_visible))>
                <label for="section_visible" class="form-check-label">Show Featured Projects section on the homepage</label>
            </div>

            <div class="mb-3">
                <label for="section_title" class="form-label">Section Title</label>
                <input type="text" name="section_title" id="section_title" value="{{ old('section_title', $settings->section_title) }}" class="form-control @error('section_title') is-invalid @enderror" required>
                @error('section_title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="section_subtitle" class="form-label">Section Subtitle</label>
                <input type="text" name="section_subtitle" id="section_subtitle" value="{{ old('section_subtitle', $settings->section_subtitle) }}" class="form-control @error('section_subtitle') is-invalid @enderror">
                @error('section_subtitle')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="projects_count" class="form-label">Number of Projects to Display</label>
                <select name="projects_count" id="projects_count" class="form-select">
                    @foreach ([6, 9, 12] as $count)
                        <option value="{{ $count }}" @selected(old('projects_count', $settings->projects_count) == $count)>{{ $count }} Projects</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="ordering" class="form-label">Portfolio Ordering</label>
                <select name="ordering" id="ordering" class="form-select">
                    <option value="featured_first" @selected(old('ordering', $settings->ordering) === 'featured_first')>Featured First</option>
                    <option value="newest" @selected(old('ordering', $settings->ordering) === 'newest')>Newest First</option>
                    <option value="oldest" @selected(old('ordering', $settings->ordering) === 'oldest')>Oldest First</option>
                    <option value="manual" @selected(old('ordering', $settings->ordering) === 'manual')>Manual Ordering (uses each project's Sort Order)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
@endsection
