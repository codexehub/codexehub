@extends('admin.layouts.app', ['title' => 'Edit Team Member'])

@section('content')
    <div class="admin-page-heading">
        <h1>Edit Team Member</h1>
        <p>Update "{{ $member->name }}".</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.team.update', $member) }}" enctype="multipart/form-data">
            @include('admin.pages.team._form')
        </form>
    </div>

    {{-- Photo deletes live outside the main form (forms can't nest); the
         remove buttons in the form target these by id via their `form` attribute. --}}
    @foreach ($member->images as $image)
        <form id="delete-team-image-{{ $image->id }}" method="POST" action="{{ route('admin.team.images.destroy', $image) }}" onsubmit="return confirm('Remove this photo?')" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endsection
