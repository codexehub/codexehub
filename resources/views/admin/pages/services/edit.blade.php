@extends('admin.layouts.app', ['title' => 'Edit Service'])

@section('content')
    <div class="admin-page-heading">
        <h1>Edit Service</h1>
        <p>Update "{{ $service->title }}".</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.services.update', $service) }}" enctype="multipart/form-data">
            @include('admin.pages.services._form')
        </form>
    </div>
@endsection
