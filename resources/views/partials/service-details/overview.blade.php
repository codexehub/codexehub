@php
    $features = $service->features->isNotEmpty()
        ? $service->features
        : collect([
            ['icon' => 'bi-patch-check', 'text' => 'Delivered by an experienced, dedicated team'],
            ['icon' => 'bi-gear', 'text' => 'Built on modern, scalable technology'],
            ['icon' => 'bi-headset', 'text' => 'Ongoing support after launch'],
            ['icon' => 'bi-graph-up-arrow', 'text' => 'Focused on measurable business results'],
        ]);
@endphp

<section class="section">
    <div class="container-xxl">
        <div class="row g-5">
            <div class="col-lg-8">
                <h2 class="mb-3">Service Overview</h2>
                <div class="text-secondary" style="white-space: pre-line;">{{ $service->description ?: $service->short_description }}</div>
            </div>

            <div class="col-lg-4">
                <div class="highlights-panel h-100">
                    <h4>What's Included</h4>
                    <ul class="list-unstyled mb-0">
                        @foreach ($features as $item)
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
