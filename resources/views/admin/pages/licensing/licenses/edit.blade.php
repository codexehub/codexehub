@extends('admin.layouts.app', ['title' => 'Edit License'])

@section('content')
    <div class="admin-page-heading">
        <h1>Edit License</h1>
        <p>Update <code>{{ $license->license_key }}</code>.</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.licensing.licenses.update', $license) }}">
            @include('admin.pages.licensing.licenses._form')
        </form>
    </div>
@endsection
