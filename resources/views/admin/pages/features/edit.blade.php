@extends('admin.layouts.app', ['title' => 'Edit Feature'])

@section('content')
    <div class="admin-page-heading">
        <h1>Edit Feature</h1>
        <p>Update "{{ $feature->name }}".</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.features.update', $feature) }}">
            @include('admin.pages.features._form')
        </form>
    </div>
@endsection
