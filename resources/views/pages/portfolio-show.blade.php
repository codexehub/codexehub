@extends('layouts.app')

@section('content')
    @include('partials.portfolio.show-hero')

    <section class="section">
        <div class="container-xxl">
            <div class="row g-5">
                <div class="col-lg-8">
                    @if ($portfolio->banner_url)
                        <img src="{{ $portfolio->banner_url }}" alt="{{ $portfolio->title }}" class="img-fluid rounded-4 mb-4">
                    @endif

                    <h2 class="mb-3">Project Overview</h2>
                    <div class="text-secondary mb-4" style="white-space: pre-line;">{{ $portfolio->description ?: $portfolio->short_description }}</div>

                    @if ($portfolio->galleryImages->isNotEmpty())
                        <h2 class="mb-3">Gallery</h2>
                        <div class="row g-3 mb-4">
                            @foreach ($portfolio->galleryImages as $image)
                                <div class="col-md-4">
                                    <a href="{{ $image->url }}" class="glightbox" data-gallery="project-gallery">
                                        <img src="{{ $image->url }}" alt="{{ $portfolio->title }} screenshot" class="img-fluid rounded-3">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="portfolio-sidebar">
                        <div class="sidebar-cta-card">
                            <h4>Project Details</h4>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><strong>Category:</strong> {{ $portfolio->category->name }}</li>
                                @if ($portfolio->client_name)
                                    <li class="mb-2"><strong>Client:</strong> {{ $portfolio->client_name }}</li>
                                @endif
                                @if ($portfolio->project_url)
                                    <li class="mb-2"><a href="{{ $portfolio->project_url }}" target="_blank" rel="noopener">Live Project <i class="bi bi-box-arrow-up-right"></i></a></li>
                                @endif
                                @if ($portfolio->github_url)
                                    <li><a href="{{ $portfolio->github_url }}" target="_blank" rel="noopener">GitHub Repository <i class="bi bi-box-arrow-up-right"></i></a></li>
                                @endif
                            </ul>
                        </div>

                        @if ($portfolio->technologies->isNotEmpty())
                            <div class="sidebar-cta-card">
                                <h4>Technology Stack</h4>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($portfolio->technologies as $technology)
                                        <span class="badge-soft badge">{{ $technology->technology_name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($relatedPortfolios->isNotEmpty())
                <div class="mt-5">
                    <h2 class="mb-4">Related Projects</h2>
                    <div class="row g-4">
                        @foreach ($relatedPortfolios as $related)
                            <div class="col-md-4">
                                <div class="project-card">
                                    <div class="project-thumb">
                                        @if ($related->thumbnail_url)
                                            <img src="{{ $related->thumbnail_url }}" alt="{{ $related->title }}">
                                        @else
                                            <i class="bi bi-briefcase"></i>
                                        @endif
                                    </div>
                                    <div class="project-body">
                                        <h3>{{ $related->title }}</h3>
                                        <a href="{{ route('portfolio.show', $related) }}" class="btn-link-arrow">View Details</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    @include('partials.home.cta')
@endsection
