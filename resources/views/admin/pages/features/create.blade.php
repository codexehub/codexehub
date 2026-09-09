@extends('admin.layouts.app', ['title' => 'Add Feature'])

@section('content')
    <div class="admin-page-heading">
        <h1>Add Feature</h1>
        <p>Add a reusable entitlement to the catalog.</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.features.store') }}">
            @include('admin.pages.features._form')
        </form>
    </div>
@endsection
