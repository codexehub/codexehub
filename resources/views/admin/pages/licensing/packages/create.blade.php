@extends('admin.layouts.app', ['title' => 'Add Package'])

@section('content')
    <div class="admin-page-heading">
        <h1>Add Package</h1>
        <p>Create a new pricing tier for a licensed app.</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.licensing.packages.store') }}">
            @include('admin.pages.licensing.packages._form')
        </form>
    </div>
@endsection
