<section class="section-sm bg-soft" id="freelance-profiles">
    <div class="container-xxl">
        <div class="section-heading mx-auto text-center mb-5" data-aos="fade-up">
            <span class="section-eyebrow justify-content-center">Find Us On Freelance Platforms</span>
            <h2 class="text-balance">We're also available on trusted global platforms</h2>
        </div>

        <div class="row g-4">
            @foreach ([
                ['icon' => 'bi-linkedin', 'name' => 'LinkedIn', 'desc' => "Let's connect, professionally and build opportunities together.", 'href' => 'https://linkedin.com', 'brand' => 'linkedin'],
                ['icon' => 'bi-briefcase', 'name' => 'Upwork', 'desc' => 'Top Rated on Upwork with 100% Job Success Score.', 'href' => 'https://upwork.com', 'brand' => 'upwork'],
                ['icon' => 'bi-lightning-charge', 'name' => 'Fiverr', 'desc' => 'Providing quality services and happy clients worldwide.', 'href' => 'https://fiverr.com', 'brand' => 'fiverr'],
            ] as $profile)
                <div class="col-md-4">
                    <div class="profile-card profile-card-{{ $profile['brand'] }}">
                        <div class="profile-logo"><i class="bi {{ $profile['icon'] }}"></i></div>
                        <h4>{{ $profile['name'] }}</h4>
                        <p>{{ $profile['desc'] }}</p>
                        <a href="{{ $profile['href'] }}" target="_blank" rel="noopener" class="btn-link-arrow">View {{ $profile['name'] }} Profile <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
