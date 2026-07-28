@if ($relatedPortfolios->isNotEmpty())
    <section class="section">
        <div class="container-xxl">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-5" data-aos="fade-up">
                <div class="section-heading mb-0">
                    <span class="section-eyebrow">More Work</span>
                    <h2 class="text-balance mb-0">Related Projects</h2>
                </div>
                <a href="{{ route('portfolio') }}" class="btn-link-arrow">View All Projects <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="row g-4">
                @foreach ($relatedPortfolios as $i => $related)
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="{{ $i * 80 }}">
                        <div class="project-card">
                            <div class="project-thumb">
                                @if ($related->thumbnail_url)
                                    <img src="{{ $related->thumbnail_url }}" alt="{{ $related->title }}">
                                @else
                                    <i class="bi bi-briefcase"></i>
                                @endif
                                <div class="project-overlay">
                                    <a href="{{ route('portfolio.show', $related) }}" aria-label="View {{ $related->title }} details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="project-body">
                                <div class="project-tags">
                                    <span class="badge-soft badge">{{ $related->category->name }}</span>
                                    @foreach ($related->technologies->take(2) as $technology)
                                        <span class="badge-soft badge">{{ $technology->technology_name }}</span>
                                    @endforeach
                                </div>
                                <h3>{{ $related->title }}</h3>
                                <p>{{ $related->short_description }}</p>
                                <a href="{{ route('portfolio.show', $related) }}" class="btn-link-arrow">View Details <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
