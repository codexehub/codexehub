<section class="section" id="contact-faq">
    <div class="container-xxl">
        <div class="section-heading mb-5" data-aos="fade-up">
            <span class="section-eyebrow">FAQ</span>
            <h2 class="text-balance">Frequently Asked Questions</h2>
        </div>

        <div class="row g-4">
            <div class="col-lg-6 contact-faq-col">
                <div class="accordion faq-accordion" id="contactFaqLeft" data-aos="fade-up">
                    @foreach ([
                        ['q' => 'How quickly will you respond to my inquiry?', 'a' => 'We typically respond within 24 hours, and often the same business day.'],
                        ['q' => 'Do you provide free consultation?', 'a' => 'Yes — every new inquiry starts with a free, no-obligation consultation call.'],
                        ['q' => 'What information do you need to start a project?', 'a' => 'A brief overview of your goals, timeline and budget is enough for us to begin scoping.'],
                    ] as $i => $faq)
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#contactFaqLeft{{ $i }}" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="contactFaqLeft{{ $i }}">
                                    {{ $faq['q'] }}
                                </button>
                            </h3>
                            <div id="contactFaqLeft{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#contactFaqLeft">
                                <div class="accordion-body">{{ $faq['a'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-6 contact-faq-col">
                <div class="accordion faq-accordion" id="contactFaqRight" data-aos="fade-up" data-aos-delay="100">
                    @foreach ([
                        ['q' => 'Can you work on an existing project?', 'a' => 'Absolutely — we start with a technical audit before committing to a timeline or quote.'],
                        ['q' => 'Do you sign NDA for projects?', 'a' => 'Yes, we\'re happy to sign an NDA before any confidential details are shared.'],
                        ['q' => 'What is your typical project timeline?', 'a' => 'Most engagements run 8–16 weeks depending on scope, with a working demo from sprint two.'],
                    ] as $i => $faq)
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#contactFaqRight{{ $i }}" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="contactFaqRight{{ $i }}">
                                    {{ $faq['q'] }}
                                </button>
                            </h3>
                            <div id="contactFaqRight{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#contactFaqRight">
                                <div class="accordion-body">{{ $faq['a'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
