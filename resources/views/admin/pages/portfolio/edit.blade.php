@extends('admin.layouts.app', ['title' => 'Edit Project'])

@section('content')
    <div class="admin-page-heading">
        <h1>Edit Project</h1>
        <p>Update "{{ $portfolio->title }}".</p>
    </div>

    <div class="admin-panel">
        <form method="POST" action="{{ route('admin.portfolio.update', $portfolio) }}" enctype="multipart/form-data">
            @include('admin.pages.portfolio._form')
        </form>
    </div>
@endsection
