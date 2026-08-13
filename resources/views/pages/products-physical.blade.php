@extends('layouts.app')

@section('content')
    <section class="breadcrumb-hero">
        <div class="container-xxl">
            <div class="row align-items-center gy-3">
                <div class="col-lg-7">
                    <h1 class="text-balance">Hardware <span>Products</span></h1>
                    <nav class="hero-breadcrumb" aria-label="breadcrumb">
                        <i class="bi bi-house-door-fill"></i>
                        <a href="{{ route('home') }}">Home</a>
                        <i class="bi bi-chevron-right"></i>
                        <span>Hardware Products</span>
                    </nav>
                </div>

                <div class="col-lg-5">
                    <p class="mb-0">Hardware and software-related equipment, backed by a full inventory system. Coming soon.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container-xxl">
            <div class="product-coming-soon text-center" data-aos="fade-up">
                <div class="icon-tile mx-auto mb-4"><i class="bi bi-cpu"></i></div>
                <h2 class="text-balance">Our hardware catalogue is on the way</h2>
                <p class="mx-auto">We&rsquo;re putting together a range of hardware and equipment to go alongside our software — with live stock levels, ordering and delivery handled through our upcoming inventory system.</p>
                <p class="mx-auto mb-4">In the meantime, our software products are ready to deploy today, and we&rsquo;re happy to advise on the hardware to run them on.</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="{{ route('products.software') }}" class="btn btn-primary btn-lg">Browse Software Products <i class="bi bi-arrow-right"></i></a>
                    <button type="button" class="btn btn-outline-primary btn-lg" data-bs-toggle="modal" data-bs-target="#consultationModal">Talk to Us</button>
                </div>
            </div>
        </div>
    </section>
@endsection
