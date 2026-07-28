<section class="section-sm">
    <div class="container-xxl">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-6">
                <div class="why-choose-panel" data-aos="fade-up">
                    <h3>Why Choose CodexeHub?</h3>

                    <div class="why-choose-grid">
                        @foreach ([
                            ['icon' => 'bi-people', 'title' => 'Experienced Team', 'desc' => 'Skilled professionals with years of industry experience.'],
                            ['icon' => 'bi-patch-check', 'title' => 'Quality Assurance', 'desc' => 'Rigorous testing ensures high performance and reliability.'],
                            ['icon' => 'bi-sliders', 'title' => 'Custom Solutions', 'desc' => 'We build solutions tailored to your unique business needs.'],
                            ['icon' => 'bi-eye', 'title' => 'Transparent Process', 'desc' => 'We believe in clear communication and full transparency.'],
                            ['icon' => 'bi-arrow-repeat', 'title' => 'Agile Approach', 'desc' => 'We follow modern agile methodologies to ensure on-time delivery.'],
                            ['icon' => 'bi-headset', 'title' => 'Long-Term Support', 'desc' => 'We provide ongoing support and maintenance for every project.'],
                        ] as $item)
                            <div class="why-choose-item">
                                <div class="icon-tile icon-circle"><i class="bi {{ $item['icon'] }}"></i></div>
                                <div>
                                    <h4>{{ $item['title'] }}</h4>
                                    <p>{{ $item['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <i class="bi bi-hexagon why-choose-decor"></i>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="journey-panel" data-aos="fade-up" data-aos-delay="80">
                    <h3>Our Journey</h3>

                    <ul class="journey-timeline">
                        @foreach ([
                            ['year' => '2016', 'desc' => 'CodexeHub was founded with a vision to provide innovative digital solutions.'],
                            ['year' => '2018', 'desc' => 'Expanded our team and started delivering enterprise web &amp; mobile applications.'],
                            ['year' => '2020', 'desc' => 'Launched AI &amp; automation services and served clients from different industries.'],
                            ['year' => '2022', 'desc' => 'Reached 100+ successful projects and global client presence.'],
                            ['year' => '2024', 'desc' => 'Continuing our journey towards becoming a global technology leader.'],
                        ] as $milestone)
                            <li>
                                <span class="journey-year">{{ $milestone['year'] }}</span>
                                <p>{!! $milestone['desc'] !!}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
