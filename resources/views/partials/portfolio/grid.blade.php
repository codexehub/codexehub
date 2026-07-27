<section class="section" id="portfolio-grid">
    <div class="container-xxl">
        <div class="portfolio-toolbar" data-aos="fade-up">
            <div class="portfolio-filters" id="portfolio">
                <button type="button" class="is-active" data-filter="*"><i class="bi bi-grid-1x2-fill"></i> All Projects</button>
                @foreach ($categories as $category)
                    <button type="button" data-filter=".{{ $category->slug }}"><i class="bi bi-code-slash"></i> {{ $category->name }}</button>
                @endforeach
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-9">
                <div class="row g-4 portfolio-grid">
                    @forelse ($portfolios as $project)
                        <div class="col-md-6 col-lg-4 portfolio-item {{ $project->category->slug }}" data-aos="fade-up">
                            <div class="project-card">
                                <div class="project-thumb">
                                    @if ($project->thumbnail_url)
                                        <img src="{{ $project->thumbnail_url }}" alt="{{ $project->title }}">
                                    @else
                                        <i class="bi bi-briefcase"></i>
                                    @endif
                                    <div class="project-overlay">
                                        <a href="{{ route('portfolio.show', $project) }}" aria-label="View {{ $project->title }} details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="project-body">
                                    <div class="project-tags">
                                        <span class="badge-soft badge">{{ $project->category->name }}</span>
                                        @foreach ($project->technologies->take(2) as $technology)
                                            <span class="badge-soft badge">{{ $technology->technology_name }}</span>
                                        @endforeach
                                    </div>
                                    <h3>{{ $project->title }}</h3>
                                    <p>{{ $project->short_description }}</p>
                                    <div class="d-flex align-items-center justify-content-between mt-3">
                                        <a href="{{ route('portfolio.show', $project) }}" class="btn-link-arrow">View Details</a>
                                        <a href="{{ route('portfolio.show', $project) }}" class="btn-icon-circle btn-icon-circle-sm" aria-label="View {{ $project->title }} details"><i class="bi bi-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-secondary">No projects published yet — check back soon.</p>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-3">
                <div class="portfolio-sidebar">
                    <div class="sidebar-cta-card">
                        <h4>Let's Build Something Great Together</h4>
                        <p><strong>Have a project in mind?</strong><br>We'd love to hear about it. Let's create something amazing for your business.</p>
                        <a href="{{ request()->routeIs('home') ? '#footer-contact' : route('home').'#footer-contact' }}" class="btn btn-primary">Start Your Project <i class="bi bi-arrow-right"></i></a>
                    </div>

                    <div class="sidebar-stats-card">
                        @foreach ([
                            ['icon' => 'bi-briefcase-fill', 'value' => '100+', 'label' => 'Projects Completed'],
                            ['icon' => 'bi-people-fill', 'value' => '50+', 'label' => 'Happy Clients'],
                            ['icon' => 'bi-rocket-takeoff-fill', 'value' => '8+', 'label' => 'Years of Experience'],
                            ['icon' => 'bi-geo-alt-fill', 'value' => '99%', 'label' => 'Client Satisfaction'],
                        ] as $stat)
                            <div class="sidebar-stat-item">
                                <div class="icon-tile"><i class="bi {{ $stat['icon'] }}"></i></div>
                                <div>
                                    <strong>{{ $stat['value'] }}</strong>
                                    <span>{{ $stat['label'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
