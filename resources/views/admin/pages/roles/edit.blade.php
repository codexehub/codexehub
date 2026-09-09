@extends('admin.layouts.app', ['title' => 'Edit ' . $role->label])

@section('content')
    <div class="admin-page-heading">
        <h1>{{ $role->label }} &mdash; Permissions</h1>
        <p>{{ $role->description }}</p>
    </div>

    @error('permissions')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PUT')

        <div class="admin-panel">
            <div class="p-3">
                @foreach ($groups as $group => $permissions)
                    <fieldset class="mb-4">
                        <legend class="form-label text-uppercase text-secondary small mb-2">{{ str_replace('-', ' ', $group) }}</legend>
                        <div class="row g-2">
                            @foreach ($permissions as $name => $label)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="permissions[]" value="{{ $name }}"
                                               id="perm-{{ $name }}"
                                               @checked(in_array($name, old('permissions', $assigned), true))>
                                        <label class="form-check-label" for="perm-{{ $name }}">
                                            {{ $label }} <code class="small text-secondary">{{ $name }}</code>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">Save Permissions</button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
