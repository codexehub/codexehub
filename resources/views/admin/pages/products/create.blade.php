@extends('admin.layouts.app', ['title' => 'Add Product'])

@section('content')
    <div class="admin-page-heading">
        <h1>Add Product</h1>
        <p>Create a new software product for the homepage and Products pages.</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @include('admin.pages.products._form')
        </form>
    </div>
@endsection
