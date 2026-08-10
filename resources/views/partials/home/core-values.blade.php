<section class="section-sm bg-soft" id="core-values">
    <div class="container-xxl">
        <h2 class="text-center mb-5">Our Core Values</h2>

        <div class="row g-4">
            @foreach ([
                ['icon' => 'bi-lightbulb', 'title' => 'Innovation', 'desc' => 'We embrace new ideas and modern technologies.'],
                ['icon' => 'bi-shield-check', 'title' => 'Integrity', 'desc' => 'We build trust through honesty and transparency.'],
                ['icon' => 'bi-people', 'title' => 'Client Success', 'desc' => 'Your growth is our success.'],
                ['icon' => 'bi-rocket-takeoff', 'title' => 'Excellence', 'desc' => 'We deliver quality beyond expectations.'],
            ] as $i => $value)
                <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="{{ $i * 60 }}">
                    <div class="value-card">
                        <span class="value-index">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="icon-tile icon-circle"><i class="bi {{ $value['icon'] }}"></i></div>
                        <h3>{{ $value['title'] }}</h3>
                        <p>{{ $value['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
