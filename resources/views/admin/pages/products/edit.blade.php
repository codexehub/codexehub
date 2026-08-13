@extends('admin.layouts.app', ['title' => 'Edit Product'])

@section('content')
    <div class="admin-page-heading">
        <h1>Edit Product</h1>
        <p>Update "{{ $product->title }}".</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @include('admin.pages.products._form')
        </form>
    </div>

    {{-- Screenshot deletes live outside the main form (forms can't nest); the
         remove buttons in the form target these by id via their `form` attribute. --}}
    @foreach ($product->images as $image)
        <form id="delete-product-image-{{ $image->id }}" method="POST" action="{{ route('admin.products.images.destroy', $image) }}" onsubmit="return confirm('Remove this screenshot?')" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endsection
