<section class="section" id="tech-stack">
    <div class="container-xxl">
        <div class="section-heading mx-auto text-center mb-5" data-aos="fade-up">
            <span class="section-eyebrow justify-content-center">Technology</span>
            <h2 class="text-balance">Our stack, battle-tested</h2>
        </div>

        <div class="row g-3">
            @foreach ([
                ['name' => 'Laravel', 'icon' => 'bi-server'],
                ['name' => 'PHP', 'icon' => 'bi-code-slash'],
                ['name' => 'React', 'icon' => 'bi-atom'],
                ['name' => 'Vue', 'icon' => 'bi-triangle'],
                ['name' => 'Next.js', 'icon' => 'bi-lightning'],
                ['name' => 'Bootstrap', 'icon' => 'bi-bootstrap'],
                ['name' => 'MySQL', 'icon' => 'bi-database'],
                ['name' => 'Redis', 'icon' => 'bi-hdd-stack'],
                ['name' => 'Docker', 'icon' => 'bi-box-seam'],
                ['name' => 'AWS', 'icon' => 'bi-cloud'],
                ['name' => 'Git', 'icon' => 'bi-git'],
                ['name' => 'Linux', 'icon' => 'bi-terminal'],
            ] as $tech)
                <div class="col-4 col-md-3 col-lg-2">
                    <div class="tech-badge">
                        <i class="bi {{ $tech['icon'] }}"></i> {{ $tech['name'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
