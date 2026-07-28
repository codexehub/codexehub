<section class="section bg-soft" id="our-team">
    <div class="container-xxl">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-5">
            <div class="section-heading" data-aos="fade-up">
                <span class="section-eyebrow">Our Team</span>
                <h2 class="text-balance mb-2">Meet the Experts Behind CodexeHub</h2>
                <p class="mb-0">Our team of talented professionals is passionate about technology and committed to delivering excellence.</p>
            </div>
            <div class="team-nav-arrows d-none d-lg-flex gap-2">
                <button type="button" class="btn-icon-circle btn-icon-circle-sm team-prev" aria-label="Previous team member"><i class="bi bi-arrow-left"></i></button>
                <button type="button" class="btn-icon-circle btn-icon-circle-sm team-next" aria-label="Next team member"><i class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <div class="swiper team-swiper">
            <div class="swiper-wrapper">
                @foreach ([
                    ['name' => 'Rashed Hossain', 'role' => 'CEO & Founder'],
                    ['name' => 'Nusrat Jahan', 'role' => 'Project Manager'],
                    ['name' => 'Sabbir Ahmed', 'role' => 'Lead Developer'],
                    ['name' => 'Tanvir Hasan', 'role' => 'UI/UX Designer'],
                    ['name' => 'Mariya Islam', 'role' => 'QA Engineer'],
                    ['name' => 'Fahim Rahman', 'role' => 'DevOps Engineer'],
                ] as $member)
                    <div class="swiper-slide">
                        <div class="team-card">
                            <div class="team-photo"><i class="bi bi-person-fill"></i></div>
                            <div class="team-body">
                                <h4>{{ $member['name'] }}</h4>
                                <span>{{ $member['role'] }}</span>
                                <div class="team-social">
                                    <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                                    <a href="#" aria-label="GitHub"><i class="bi bi-github"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
