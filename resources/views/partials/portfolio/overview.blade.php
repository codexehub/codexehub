@php
    $highlights = $portfolio->highlights->isNotEmpty()
        ? $portfolio->highlights
        : collect([
            ['icon' => 'bi-phone', 'text' => 'Modern &amp; responsive design for all devices'],
            ['icon' => 'bi-shield-lock', 'text' => 'Secure payment gateways and SSL encryption'],
            ['icon' => 'bi-search', 'text' => 'Advanced search and filtering capabilities'],
            ['icon' => 'bi-boxes', 'text' => 'Efficient inventory &amp; data management'],
            ['icon' => 'bi-people', 'text' => 'User accounts, roles and permissions'],
            ['icon' => 'bi-speedometer2', 'text' => 'Admin dashboard with analytics and reports'],
        ]);

    $completedLabel = $portfolio->completed_date?->format('F Y') ?? $portfolio->created_at->format('F Y');
@endphp

<section class="section">
    <div class="container-xxl">
        <div class="row g-5">
            <div class="col-lg-8">
                <h2 class="mb-3">Project Overview</h2>
                <div class="text-secondary mb-4" style="white-space: pre-line;">{{ $portfolio->description ?: $portfolio->short_description }}</div>

                <div class="overview-meta-grid">
                    <div class="overview-meta-col">
                        <div class="overview-meta-item">
                            <i class="bi bi-person-badge"></i>
                            <div>
                                <strong>Client</strong>
                                <span>{{ $portfolio->client_name ?: 'Private Client' }}</span>
                            </div>
                        </div>
                        <div class="overview-meta-item">
                            <i class="bi bi-diagram-3"></i>
                            <div>
                                <strong>Industry</strong>
                                <span>{{ $portfolio->industry ?: $portfolio->category->name }}</span>
                            </div>
                        </div>
                        <div class="overview-meta-item">
                            <i class="bi bi-geo-alt"></i>
                            <div>
                                <strong>Location</strong>
                                <span>{{ $portfolio->location ?: 'Remote' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="overview-meta-col">
                        <div class="overview-meta-item">
                            <i class="bi bi-clock-history"></i>
                            <div>
                                <strong>Project Duration</strong>
                                <span>{{ $portfolio->duration ?: '—' }}</span>
                            </div>
                        </div>
                        <div class="overview-meta-item">
                            <i class="bi bi-people-fill"></i>
                            <div>
                                <strong>Team Size</strong>
                                <span>{{ $portfolio->team_size ?: '—' }}</span>
                            </div>
                        </div>
                        <div class="overview-meta-item">
                            <i class="bi bi-calendar-check"></i>
                            <div>
                                <strong>Completed</strong>
                                <span>{{ $completedLabel }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-3">
                    @if ($portfolio->project_url)
                        <a href="{{ $portfolio->project_url }}" target="_blank" rel="noopener" class="btn btn-primary">Live Preview <i class="bi bi-box-arrow-up-right"></i></a>
                    @endif
                    @if ($portfolio->case_study_file_url)
                        <a href="{{ $portfolio->case_study_file_url }}" target="_blank" rel="noopener" class="btn btn-ghost">Download Case Study <i class="bi bi-download"></i></a>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                <div class="highlights-panel h-100">
                    <h4>Project Highlights</h4>
                    <ul class="list-unstyled mb-0">
                        @foreach ($highlights as $item)
                            <li>
                                <span class="highlight-icon-circle"><i class="bi {{ is_array($item) ? $item['icon'] : $item->icon }}"></i></span>
                                <span>{!! is_array($item) ? $item['text'] : $item->text !!}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
