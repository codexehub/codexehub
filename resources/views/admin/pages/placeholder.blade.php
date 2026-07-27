@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <div class="admin-page-heading">
        <h1>{{ $title }}</h1>
        <p>{{ $description }}</p>
    </div>

    <div class="admin-panel admin-empty-state">
        <i class="bi bi-cone-striped"></i>
        <h2>Module coming soon</h2>
        <p>This section is scaffolded and ready for its data model and CRUD screens in a future iteration.</p>
    </div>
@endsection
