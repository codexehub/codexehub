@extends('admin.layouts.app', ['title' => 'Add Customer'])

@section('content')
    <div class="admin-page-heading">
        <h1>Add Customer</h1>
        <p>Register a new control-plane customer.</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.customers.store') }}">
            @include('admin.pages.customers._form')
        </form>
    </div>
@endsection
