@extends('layouts.app')

@section('content')
    @include('partials.product-details.hero')
    @include('partials.product-details.overview')
    @include('partials.product-details.screenshots')
    @include('partials.product-details.related')
    @include('partials.product-details.cta')
@endsection
