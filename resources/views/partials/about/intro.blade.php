<section class="section" id="about-intro">
    <div class="container-xxl">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6">
                <div class="about-photo-panel" data-aos="fade-right">
                    <div class="photo-panel-window">
                        <div class="photo-panel-header"><span></span><span></span><span></span></div>
                        <div class="photo-panel-body">
                            <span class="photo-panel-title"><i class="bi bi-easel2"></i> Our Vision</span>
                            <ul class="photo-panel-list">
                                <li><i class="bi bi-check-circle-fill"></i> Innovation</li>
                                <li><i class="bi bi-check-circle-fill"></i> Integrity</li>
                                <li><i class="bi bi-check-circle-fill"></i> Excellence</li>
                                <li><i class="bi bi-check-circle-fill"></i> Client Success</li>
                            </ul>
                        </div>
                    </div>

                    <div class="photo-stat-badge">
                        <div class="icon-tile icon-circle"><i class="bi bi-briefcase-fill"></i></div>
                        <div>
                            <strong>150+</strong>
                            <span>Projects Completed</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <span class="section-eyebrow">About CodexeHub</span>
                <h2 class="text-balance">Your Trusted Partner for <span class="text-accent">Digital Transformation</span></h2>
                <p>CodexeHub was founded with a simple mission — to help businesses succeed in the digital world through innovative technology, creative design, and a commitment to excellence.</p>
                <p>We combine strategy, creativity, and technology to build digital products that are not only visually stunning but also highly functional and scalable.</p>

                <div class="about-info-grid">
                    @foreach ([
                        ['icon' => 'bi-bullseye', 'title' => 'Our Mission', 'desc' => 'Deliver high-quality digital solutions that empower businesses to grow.'],
                        ['icon' => 'bi-eye', 'title' => 'Our Vision', 'desc' => 'To become a global technology partner of choice.'],
                        ['icon' => 'bi-shield-check', 'title' => 'Our Values', 'desc' => 'Integrity, innovation, transparency, and client success.'],
                        ['icon' => 'bi-heart', 'title' => 'Our Commitment', 'desc' => 'We are committed to delivering value with passion and precision.'],
                    ] as $item)
                        <div class="about-info-item">
                            <div class="icon-tile icon-circle"><i class="bi {{ $item['icon'] }}"></i></div>
                            <div>
                                <h4>{{ $item['title'] }}</h4>
                                <p>{{ $item['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
