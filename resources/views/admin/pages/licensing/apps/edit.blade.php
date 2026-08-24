@extends('admin.layouts.app', ['title' => 'Edit Licensed App'])

@section('content')
    <div class="admin-page-heading">
        <h1>Edit Licensed App</h1>
        <p>Update "{{ $licensedApp->name }}".</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.licensing.apps.update', $licensedApp) }}">
            @include('admin.pages.licensing.apps._form')
        </form>
    </div>
@endsection
