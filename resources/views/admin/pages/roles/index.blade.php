@extends('admin.layouts.app', ['title' => 'Roles & Permissions'])

@section('content')
    <div class="admin-page-heading">
        <h1>Role &amp; Permission Management</h1>
        <p>The SRS &sect;105 admin roles. Assign roles to users from <a href="{{ route('admin.users.index') }}">User Management</a>.</p>
    </div>

    <div class="admin-panel">
        <x-admin::data-table>
            <x-slot:head>
                <tr>
                    <th>Role</th>
                    <th>Description</th>
                    <th class="text-center">Permissions</th>
                    <th class="text-center">Users</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:head>

            @foreach ($roles as $role)
                <tr>
                    <td><span class="admin-badge admin-badge--info">{{ $role->label }}</span></td>
                    <td class="text-secondary">{{ $role->description }}</td>
                    <td class="text-center">
                        @if ($role->name === \App\Models\Role::SUPER_ADMIN)
                            <span class="admin-badge admin-badge--success">All</span>
                        @else
                            {{ $role->permissions_count }}
                        @endif
                    </td>
                    <td class="text-center">{{ $role->users_count }}</td>
                    <td class="text-end">
                        @if ($role->name === \App\Models\Role::SUPER_ADMIN)
                            <span class="text-secondary small">Locked</span>
                        @else
                            <a href="{{ route('admin.roles.edit', $role) }}" class="admin-table-action" title="Edit permissions"><i class="bi bi-pencil-fill"></i></a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-admin::data-table>
    </div>
@endsection
