@extends('layouts.app')

@section('content')
    @include('partials.home.hero')
    @include('partials.home.core-values')
    @include('partials.home.trusted-by')
    @include('partials.home.services')
    @include('partials.home.featured-projects')
    @include('partials.home.process')
    @include('partials.home.testimonials')
    @include('partials.home.freelance-profiles')
    @include('partials.home.cta')
@endsection
