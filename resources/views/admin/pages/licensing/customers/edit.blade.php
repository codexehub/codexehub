@extends('admin.layouts.app', ['title' => 'Edit Customer'])

@section('content')
    <div class="admin-page-heading">
        <h1>Edit Customer</h1>
        <p>Update "{{ $customer->name }}".</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.licensing.customers.update', $customer) }}">
            @include('admin.pages.licensing.customers._form')
        </form>
    </div>
@endsection
