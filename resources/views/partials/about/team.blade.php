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

        @if ($teamMembers->isNotEmpty())
            <div class="swiper team-swiper">
                <div class="swiper-wrapper">
                    @foreach ($teamMembers as $member)
                        <div class="swiper-slide">
                            <div class="team-card">
                                <div class="team-photo">
                                    @if ($member->photo_url)
                                        <img src="{{ $member->photo_url }}" alt="{{ $member->name }}">
                                    @else
                                        <i class="bi bi-person-fill"></i>
                                    @endif

                                    @if ($member->description)
                                        <button type="button" class="team-info-btn" data-bs-toggle="modal" data-bs-target="#teamMemberModal"
                                            data-team-name="{{ $member->name }}"
                                            data-team-role="{{ $member->role }}"
                                            data-team-photo="{{ $member->photo_url }}"
                                            data-team-description="{{ $member->description }}"
                                            data-team-linkedin="{{ $member->linkedin_url }}"
                                            data-team-github="{{ $member->github_url }}"
                                            data-team-images="{{ $member->images->pluck('url')->toJson() }}"
                                            aria-label="View {{ $member->name }}'s bio">
                                            <i class="bi bi-info-circle-fill"></i>
                                        </button>
                                    @endif
                                </div>
                                <div class="team-body">
                                    <h4>{{ $member->name }}</h4>
                                    <span>{{ $member->role }}</span>
                                    @if ($member->linkedin_url || $member->github_url)
                                        <div class="team-social">
                                            @if ($member->linkedin_url)
                                                <a href="{{ $member->linkedin_url }}" target="_blank" rel="noopener" aria-label="{{ $member->name }} on LinkedIn"><i class="bi bi-linkedin"></i></a>
                                            @endif
                                            @if ($member->github_url)
                                                <a href="{{ $member->github_url }}" target="_blank" rel="noopener" aria-label="{{ $member->name }} on GitHub"><i class="bi bi-github"></i></a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="modal fade team-member-modal" id="teamMemberModal" tabindex="-1" aria-hidden="true" aria-labelledby="teamMemberModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="visually-hidden" id="teamMemberModalLabel">Team Member Bio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="" alt="" class="team-modal-photo d-none" data-modal-photo>
                        <div>
                            <h4 class="mb-0" data-modal-name></h4>
                            <span class="text-accent" data-modal-role></span>
                        </div>
                    </div>
                    <p class="mb-0" data-modal-description></p>
                    <div class="team-modal-gallery d-none" data-modal-gallery></div>
                    <div class="d-flex gap-2 mt-3">
                        <a href="#" class="btn-icon-circle btn-icon-circle-sm d-none" target="_blank" rel="noopener" data-modal-linkedin aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="btn-icon-circle btn-icon-circle-sm d-none" target="_blank" rel="noopener" data-modal-github aria-label="GitHub"><i class="bi bi-github"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
