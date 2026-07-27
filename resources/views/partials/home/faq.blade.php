<section class="section" id="faq">
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="section-heading mx-auto text-center mb-5" data-aos="fade-up">
                    <span class="section-eyebrow justify-content-center">FAQ</span>
                    <h2 class="text-balance">Frequently asked questions</h2>
                </div>

                <div class="accordion faq-accordion" id="faqAccordion" data-aos="fade-up">
                    @foreach ([
                        ['q' => 'How long does a typical project take?', 'a' => 'Most engagements run 8–16 weeks depending on scope, with a working demo available from sprint two onward.'],
                        ['q' => 'Do you work with startups or only enterprises?', 'a' => 'Both. We scale our process to fit early-stage MVPs as well as multi-team enterprise platforms.'],
                        ['q' => 'What does the pricing model look like?', 'a' => 'Fixed-price for well-defined scopes, or dedicated-team retainers for ongoing product work.'],
                        ['q' => 'Can you take over an existing codebase?', 'a' => 'Yes — we start with a technical audit before committing to a timeline or quote.'],
                        ['q' => 'Do you provide post-launch support?', 'a' => 'Every project includes a support window, with optional SLA-backed maintenance plans after.'],
                    ] as $i => $faq)
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $i }}" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="faq{{ $i }}">
                                    {{ $faq['q'] }}
                                </button>
                            </h3>
                            <div id="faq{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">{{ $faq['a'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
