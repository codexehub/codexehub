@extends('admin.layouts.app', ['title' => 'Add Service'])

@section('content')
    <div class="admin-page-heading">
        <h1>Add Service</h1>
        <p>Create a new service card for the homepage and Services page.</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data">
            @include('admin.pages.services._form')
        </form>
    </div>
@endsection
