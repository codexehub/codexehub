@php
    $portfolioSettings = \App\Models\PortfolioSetting::current();

    $featuredProjects = $portfolioSettings->section_visible
        ? \App\Models\Portfolio::query()
            ->active()
            ->featured()
            ->with('technologies', 'images')
            ->orderedFor($portfolioSettings->ordering)
            ->limit($portfolioSettings->projects_count)
            ->get()
        : collect();
@endphp

@if ($portfolioSettings->section_visible && $featuredProjects->isNotEmpty())
    <section class="section" id="projects">
        <div class="container-xxl">
            <div class="row gy-5 align-items-center">
                <div class="col-lg-3" data-aos="fade-right">
                    <span class="section-eyebrow">Our Work</span>
                    <h2 class="text-balance mb-3">{{ $portfolioSettings->section_title }}</h2>
                    @if ($portfolioSettings->section_subtitle)
                        <p class="text-secondary mb-4">{{ $portfolioSettings->section_subtitle }}</p>
                    @endif
                    <a href="{{ route('portfolio') }}" class="btn btn-primary">View All Projects <i class="bi bi-arrow-right"></i></a>
                </div>

                <div class="col-lg-9">
                    <div class="row g-4">
                        @foreach ($featuredProjects as $i => $project)
                            <div class="col-md-4" data-aos="fade-up" data-aos-delay="{{ $i * 80 }}">
                                <div class="project-card">
                                    <div class="project-thumb">
                                        @if ($project->thumbnail_url)
                                            <img src="{{ $project->thumbnail_url }}" alt="{{ $project->title }}">
                                        @else
                                            <i class="bi bi-briefcase"></i>
                                        @endif
                                    </div>
                                    <div class="project-body">
                                        <h3>{{ $project->title }}</h3>
                                        <div class="project-tags">
                                            @foreach ($project->technologies->take(3) as $technology)
                                                <span class="badge-soft badge">{{ $technology->technology_name }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
