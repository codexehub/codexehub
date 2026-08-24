@extends('admin.layouts.app', ['title' => 'New License'])

@section('content')
    <div class="admin-page-heading">
        <h1>New License</h1>
        <p>Issue a new license key for a customer.</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.licensing.licenses.store') }}">
            @include('admin.pages.licensing.licenses._form')
        </form>
    </div>
@endsection
