<section class="section-sm bg-soft">
    <div class="container-xxl">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-5">
                <div class="mini-panel" data-aos="fade-up">
                    <h4>Our Development Process</h4>

                    <div class="mini-process">
                        @foreach ([
                            ['icon' => 'bi-clipboard-check', 'title' => 'Discovery', 'desc' => 'We understand your business and goals.'],
                            ['icon' => 'bi-map', 'title' => 'Planning', 'desc' => 'We plan, analyse and create the roadmap.'],
                            ['icon' => 'bi-pencil-square', 'title' => 'Design', 'desc' => 'We design intuitive and modern UI/UX.'],
                            ['icon' => 'bi-code-slash', 'title' => 'Development', 'desc' => 'We build scalable and secure solution.'],
                            ['icon' => 'bi-patch-check', 'title' => 'Testing', 'desc' => 'We test thoroughly to ensure quality.'],
                            ['icon' => 'bi-cloud-arrow-up', 'title' => 'Deployment', 'desc' => 'We deploy and provide ongoing support.'],
                        ] as $i => $step)
                            <div class="mini-process-step">
                                <div class="step-icon"><i class="bi {{ $step['icon'] }}"></i></div>
                                <span class="step-number">{{ sprintf('%02d', $i + 1) }}</span>
                                <strong>{{ $step['title'] }}</strong>
                                <p>{{ $step['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="mini-panel" data-aos="fade-up" data-aos-delay="60">
                    <h4>Industries We Serve</h4>

                    <div class="mini-industries">
                        @foreach ([
                            ['icon' => 'bi-heart-pulse', 'title' => 'Healthcare'],
                            ['icon' => 'bi-shop', 'title' => 'Retail'],
                            ['icon' => 'bi-mortarboard', 'title' => 'Education'],
                            ['icon' => 'bi-gear', 'title' => 'Manufacturing'],
                            ['icon' => 'bi-bank', 'title' => 'Finance'],
                            ['icon' => 'bi-truck', 'title' => 'Logistics'],
                            ['icon' => 'bi-building', 'title' => 'Real Estate'],
                            ['icon' => 'bi-globe2', 'title' => 'NGO &amp; Nonprofit'],
                        ] as $industry)
                            <div class="mini-industry-item">
                                <i class="bi {{ $industry['icon'] }}"></i> {!! $industry['title'] !!}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="mini-cta-dark" data-aos="fade-up" data-aos-delay="120">
                    <span class="hero-label"><i class="bi bi-rocket-takeoff"></i> Let&rsquo;s Work Together</span>
                    <h4>Have a Project in Mind?</h4>
                    <p>Let&rsquo;s discuss how CodexeHub can help you bring your idea to life with the right technology and strategy.</p>
                    <a href="#footer-contact" class="btn btn-accent">Start Your Project <i class="bi bi-arrow-right"></i></a>
                    <i class="bi bi-boxes mini-cta-decor"></i>
                </div>
            </div>
        </div>
    </div>
</section>
