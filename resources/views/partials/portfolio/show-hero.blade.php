<section class="hero project-hero">
    <div class="container-xxl">
        <div class="row align-items-center gy-5">
            <div class="col-lg-7">
                <nav class="hero-breadcrumb mb-3" aria-label="breadcrumb">
                    <i class="bi bi-house-door-fill"></i>
                    <a href="{{ route('home') }}">Home</a>
                    <i class="bi bi-chevron-right"></i>
                    <a href="{{ route('portfolio') }}">Portfolio</a>
                    <i class="bi bi-chevron-right"></i>
                    <span>{{ $portfolio->title }}</span>
                </nav>

                <span class="project-category-badge"><i class="bi bi-tag-fill"></i> {{ $portfolio->category->name }}</span>

                <h1 class="text-balance">{{ $portfolio->title }}</h1>
                <p class="lead-text">{{ $portfolio->short_description }}</p>

                @if ($portfolio->technologies->isNotEmpty())
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        @foreach ($portfolio->technologies as $technology)
                            <span class="badge-soft badge">{{ $technology->technology_name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="col-lg-5">
                <div class="project-hero-visual" data-aos="fade-left" data-aos-delay="150">
                    <div class="project-hero-frame">
                        @if ($portfolio->banner_url)
                            <div class="mockup-bar"><span></span><span></span><span></span></div>
                            <img src="{{ $portfolio->banner_url }}" alt="{{ $portfolio->title }}">
                        @else
                            <div class="project-hero-frame-fallback"><i class="bi bi-briefcase"></i></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
