@php
    $languages = ['English', 'Bangla', 'Other'];

    $servicesList = [
        'Custom Software Development', 'Website Development', 'E-Commerce Development', 'ERP Solution',
        'CRM Solution', 'Mobile App Development', 'UI/UX Design', 'AI Automation', 'API Development',
        'Cloud & DevOps', 'SEO & Digital Marketing', 'Maintenance & Support', 'Other',
    ];

    $projectTypes = ['Startup', 'Small Business', 'Enterprise', 'Government', 'NGO', 'Personal'];

    $budgets = ['Under $1,000', '$1,000–$5,000', '$5,000–$10,000', '$10,000–$25,000', 'Above $25,000', "Let's Discuss"];

    $timelines = ['Immediately', 'Within 1 Month', '1–3 Months', '3–6 Months', 'Flexible'];

    $contactMethods = [
        ['value' => 'Email', 'icon' => 'bi-envelope'],
        ['value' => 'Phone Call', 'icon' => 'bi-telephone'],
        ['value' => 'WhatsApp', 'icon' => 'bi-whatsapp'],
        ['value' => 'Google Meet', 'icon' => 'bi-camera-video'],
        ['value' => 'Zoom', 'icon' => 'bi-camera-reels'],
    ];

    $timezones = [
        'Asia/Dhaka' => 'Dhaka (GMT+6)', 'Asia/Kolkata' => 'Kolkata (GMT+5:30)', 'Asia/Dubai' => 'Dubai (GMT+4)',
        'Asia/Singapore' => 'Singapore (GMT+8)', 'Asia/Tokyo' => 'Tokyo (GMT+9)', 'Europe/London' => 'London (GMT+0/+1)',
        'Europe/Berlin' => 'Berlin (GMT+1/+2)', 'America/New_York' => 'New York (GMT-5/-4)',
        'America/Los_Angeles' => 'Los Angeles (GMT-8/-7)', 'Australia/Sydney' => 'Sydney (GMT+10/+11)', 'UTC' => 'UTC (GMT+0)',
    ];
@endphp

<div class="modal fade consultation-modal" id="consultationModal" tabindex="-1" aria-labelledby="consultationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="consultationForm" action="{{ route('consultation.store') }}" method="POST" enctype="multipart/form-data" novalidate data-consultation-form>
                @csrf

                <div class="consultation-header">
                    <button type="button" class="btn-close consultation-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    <div class="consultation-header-brand">
                        <i class="bi bi-braces-asterisk"></i>
                        <span>Codexe<span>Hub</span></span>
                    </div>

                    <h2 id="consultationModalLabel">Get Your Free Consultation</h2>
                    <p>Let&rsquo;s discuss your project and recommend the best solution for your business.</p>

                    <div class="consultation-progress">
                        <div class="consultation-progress-bar">
                            <span data-consultation-progress-fill></span>
                        </div>
                        <div class="consultation-step-label" data-consultation-step-label>Step 1 of 3</div>
                    </div>
                </div>

                <div class="consultation-body">
                    <div class="consultation-alert consultation-alert-error d-none" data-consultation-form-error>
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Something went wrong. Please check the highlighted fields and try again.</span>
                    </div>

                    @include('partials.consultation.step-1', ['languages' => $languages])
                    @include('partials.consultation.step-2', ['servicesList' => $servicesList, 'projectTypes' => $projectTypes, 'budgets' => $budgets, 'timelines' => $timelines])
                    @include('partials.consultation.step-3', ['contactMethods' => $contactMethods, 'timezones' => $timezones])
                    @include('partials.consultation.success')
                </div>

                <div class="consultation-footer" data-consultation-footer>
                    <button type="button" class="btn btn-outline-secondary consultation-btn-prev d-none" data-consultation-prev>
                        <i class="bi bi-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="btn btn-primary consultation-btn-next ms-auto" data-consultation-next>
                        Next <i class="bi bi-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn btn-accent consultation-btn-submit ms-auto d-none" data-consultation-submit>
                        <span class="consultation-btn-text">Submit Consultation Request</span>
                        <span class="spinner-border spinner-border-sm d-none" data-consultation-spinner role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
