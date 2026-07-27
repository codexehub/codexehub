<section class="breadcrumb-hero">
    <div class="container-xxl">
        <div class="row align-items-center gy-3">
            <div class="col-lg-8">
                <h1 class="text-balance">{{ $portfolio->title }}</h1>
                <nav class="hero-breadcrumb" aria-label="breadcrumb">
                    <i class="bi bi-house-door-fill"></i>
                    <a href="{{ route('home') }}">Home</a>
                    <i class="bi bi-chevron-right"></i>
                    <a href="{{ route('portfolio') }}">Portfolio</a>
                    <i class="bi bi-chevron-right"></i>
                    <span>{{ $portfolio->title }}</span>
                </nav>
            </div>

            <div class="col-lg-4">
                <p class="mb-0">{{ $portfolio->short_description }}</p>
            </div>
        </div>
    </div>
</section>
