@extends('admin.layouts.app', ['title' => 'Edit Customer'])

@section('content')
    <div class="admin-page-heading">
        <h1>Edit Customer</h1>
        <p>Update "{{ $customer->name }}".</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
            @include('admin.pages.customers._form')
        </form>
    </div>
@endsection
