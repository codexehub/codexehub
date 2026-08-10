@php
    $portfolioSettings = \App\Models\PortfolioSetting::current();

    $featuredProjects = $portfolioSettings->section_visible
        ? \App\Models\Portfolio::query()
            ->active()
            ->featured()
            ->with('technologies', 'images')
            ->orderedFor($portfolioSettings->ordering)
            ->limit(min($portfolioSettings->projects_count, 5))
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
                    <div class="recent-work-swiper-wrap">
                        <button type="button" class="swiper-arrow recent-work-prev" aria-label="Previous project"><i class="bi bi-chevron-left"></i></button>

                        <div class="swiper recent-work-swiper" data-aos="fade-up">
                            <div class="swiper-wrapper">
                                @foreach ($featuredProjects as $project)
                                    <div class="swiper-slide">
                                        <a href="{{ route('portfolio.show', $project) }}" class="project-card project-card-cover"
                                           @if ($project->thumbnail_url) style="background-image: url('{{ $project->thumbnail_url }}')" @endif>
                                            @unless ($project->thumbnail_url)
                                                <span class="project-card-cover-icon"><i class="bi bi-briefcase"></i></span>
                                            @endunless
                                            <span class="project-card-content">
                                                <span class="project-tags">
                                                    @foreach ($project->technologies->take(3) as $technology)
                                                        <span class="badge-soft badge">{{ $technology->technology_name }}</span>
                                                    @endforeach
                                                </span>
                                                <span class="project-card-title">{{ $project->title }}</span>
                                                <span class="btn-link-arrow">View Project <i class="bi bi-arrow-right"></i></span>
                                            </span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                            <div class="swiper-pagination recent-work-pagination"></div>
                        </div>

                        <button type="button" class="swiper-arrow recent-work-next" aria-label="Next project"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
