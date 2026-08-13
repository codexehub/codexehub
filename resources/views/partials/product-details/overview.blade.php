@php
    $features = $product->features->isNotEmpty()
        ? $product->features
        : collect([
            ['icon' => 'bi-patch-check', 'text' => 'Production-tested with real business workloads'],
            ['icon' => 'bi-arrow-repeat', 'text' => 'Regular updates and security patches'],
            ['icon' => 'bi-sliders', 'text' => 'Customizable to match your workflow'],
            ['icon' => 'bi-headset', 'text' => 'Setup assistance and ongoing support'],
        ]);
@endphp

<section class="section">
    <div class="container-xxl">
        <div class="row g-5">
            <div class="col-lg-8">
                <h2 class="mb-3">Product Overview</h2>
                <div class="text-secondary" style="white-space: pre-line;">{{ $product->description ?: $product->short_description }}</div>

                <div class="overview-meta-grid mt-5">
                    <div class="overview-meta-col">
                        <div class="overview-meta-item">
                            <i class="bi bi-tag"></i>
                            <div><strong>Pricing</strong><span>{{ $product->price_label }}</span></div>
                        </div>
                        <div class="overview-meta-item">
                            <i class="bi bi-collection"></i>
                            <div><strong>Category</strong><span>{{ $product->category?->name ?? 'Software Product' }}</span></div>
                        </div>
                    </div>
                    <div class="overview-meta-col">
                        <div class="overview-meta-item">
                            <i class="bi bi-box"></i>
                            <div><strong>Version</strong><span>{{ $product->version ?: 'Latest release' }}</span></div>
                        </div>
                        <div class="overview-meta-item">
                            <i class="bi bi-shield-check"></i>
                            <div><strong>Deployment</strong><span>Self-hosted or managed by CodexeHub</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="highlights-panel h-100">
                    <h4>Key Features</h4>
                    <ul class="list-unstyled mb-0">
                        @foreach ($features as $item)
                            <li>
                                <span class="highlight-icon-circle"><i class="bi {{ is_array($item) ? $item['icon'] : $item->icon }}"></i></span>
                                <span>{{ is_array($item) ? $item['text'] : $item->text }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
