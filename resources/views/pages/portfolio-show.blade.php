@extends('layouts.app')

@section('content')
    @include('partials.portfolio.show-hero')
    @include('partials.portfolio.overview')
    @include('partials.portfolio.challenge-solution')
    @include('partials.portfolio.gallery')
    @include('partials.portfolio.features')
    @include('partials.portfolio.tech-results')
    @include('partials.portfolio.testimonial')
    @include('partials.portfolio.related')
    @include('partials.portfolio.cta')
@endsection
