@extends('admin.layouts.app', ['title' => 'Add Licensed App'])

@section('content')
    <div class="admin-page-heading">
        <h1>Add Licensed App</h1>
        <p>Register a new product to be licensed via codexehub/license-guard.</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.licensing.apps.store') }}">
            @include('admin.pages.licensing.apps._form')
        </form>
    </div>
@endsection
