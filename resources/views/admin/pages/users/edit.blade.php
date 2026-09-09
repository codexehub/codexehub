@extends('admin.layouts.app', ['title' => 'Edit User'])

@section('content')
    <div class="admin-page-heading">
        <h1>Edit User</h1>
        <p>Update account details for {{ $user->name }}.</p>
    </div>

    <div class="admin-panel admin-panel--narrow">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="role" class="form-label">Account type</label>
                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror">
                    <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                    <option value="editor" @selected(old('role', $user->role) === 'editor')>Editor</option>
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Roles &amp; permissions</label>
                @error('roles')
                    <div class="alert alert-danger py-2">{{ $message }}</div>
                @enderror
                @php($assignedRoles = old('roles', $user->roles->pluck('name')->all()))
                @foreach ($roles as $role)
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="roles[]" value="{{ $role->name }}"
                               id="role-{{ $role->name }}" @checked(in_array($role->name, $assignedRoles, true))>
                        <label class="form-check-label" for="role-{{ $role->name }}">
                            {{ $role->label }} <span class="text-secondary small">&mdash; {{ $role->description }}</span>
                        </label>
                    </div>
                @endforeach
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
