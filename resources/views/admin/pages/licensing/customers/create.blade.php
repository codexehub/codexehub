@extends('admin.layouts.app', ['title' => 'Add Customer'])

@section('content')
    <div class="admin-page-heading">
        <h1>Add Customer</h1>
        <p>Register a new license customer.</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.licensing.customers.store') }}">
            @include('admin.pages.licensing.customers._form')
        </form>
    </div>
@endsection
