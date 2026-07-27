@extends('layouts.app')

@section('content')
    @include('partials.portfolio.hero')
    @include('partials.portfolio.grid')
    @include('partials.home.tech-stack')
    @include('partials.home.testimonials')
    @include('partials.home.process')
    @include('partials.home.faq')
    @include('partials.home.cta')
@endsection
