@extends('admin.layouts.app', ['title' => 'Add Project'])

@section('content')
    <div class="admin-page-heading">
        <h1>Add Project</h1>
        <p>Create a new portfolio project.</p>
    </div>

    <div class="admin-panel">
        <form method="POST" action="{{ route('admin.portfolio.store') }}" enctype="multipart/form-data">
            @include('admin.pages.portfolio._form')
        </form>
    </div>
@endsection
