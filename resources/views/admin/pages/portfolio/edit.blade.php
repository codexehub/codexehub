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

    {{-- Gallery image deletes live outside the main form (forms can't nest); the
         remove buttons in the form target these by id via their `form` attribute. --}}
    @foreach ($portfolio->images->where('type', 'gallery') as $image)
        <form id="delete-portfolio-image-{{ $image->id }}" method="POST" action="{{ route('admin.portfolio.images.destroy', $image) }}" onsubmit="return confirm('Remove this image?')" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endsection
