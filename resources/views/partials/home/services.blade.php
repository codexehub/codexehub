<section class="section" id="services">
    <div class="container-xxl">
        <div class="section-heading mx-auto text-center mb-5" data-aos="fade-up">
            <span class="section-eyebrow justify-content-center">What We Do</span>
            <h2 class="text-balance">Services built around your roadmap</h2>
        </div>

        <div class="row g-4">
            @foreach ([
                ['slug' => 'web-development', 'icon' => 'bi-code-slash', 'title' => 'Web Development', 'desc' => 'Fast, scalable web platforms built on Laravel, React and modern tooling.'],
                ['slug' => 'mobile-apps', 'icon' => 'bi-phone', 'title' => 'Mobile Apps', 'desc' => 'Native and cross-platform apps that feel native, ship on schedule.'],
                ['slug' => 'ui-ux-design', 'icon' => 'bi-palette', 'title' => 'UI / UX Design', 'desc' => 'Interfaces grounded in research, tested with real users before launch.'],
                ['slug' => 'ai-automation', 'icon' => 'bi-cpu', 'title' => 'AI Automation', 'desc' => 'Workflow and decision automation powered by practical, production AI.'],
                ['slug' => 'cloud-devops', 'icon' => 'bi-cloud-arrow-up', 'title' => 'Cloud &amp; DevOps', 'desc' => 'CI/CD, infrastructure as code, and monitoring that scales with load.'],
                ['slug' => 'maintenance-support', 'icon' => 'bi-tools', 'title' => 'Maintenance &amp; Support', 'desc' => 'Ongoing releases, security patching and SLA-backed support.'],
            ] as $i => $service)
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $i * 60 }}">
                    <div class="service-card" id="service-{{ $service['slug'] }}">
                        <div class="icon-tile"><i class="bi {{ $service['icon'] }}"></i></div>
                        <h3>{!! $service['title'] !!}</h3>
                        <p>{{ $service['desc'] }}</p>
                        <a href="#footer-contact" class="btn-link-arrow">Learn More <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
