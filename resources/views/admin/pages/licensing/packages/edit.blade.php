@extends('admin.layouts.app', ['title' => 'Edit Package'])

@section('content')
    <div class="admin-page-heading">
        <h1>Edit Package</h1>
        <p>Update "{{ $package->name }}".</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.licensing.packages.update', $package) }}">
            @include('admin.pages.licensing.packages._form')
        </form>
    </div>
@endsection
