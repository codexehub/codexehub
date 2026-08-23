@extends('admin.layouts.app', ['title' => 'Add Team Member'])

@section('content')
    <div class="admin-page-heading">
        <h1>Add Team Member</h1>
        <p>Add a new team member to the About Us "Meet the Experts Behind CodexeHub" section.</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.team.store') }}" enctype="multipart/form-data">
            @include('admin.pages.team._form')
        </form>
    </div>
@endsection
