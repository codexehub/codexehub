@extends('admin.layouts.app', ['title' => 'User Management'])

@section('content')
    <div class="admin-page-heading">
        <h1>User Management</h1>
        <p>Search, sort and manage admin accounts.</p>
    </div>

    @error('user')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <div class="admin-panel">
        <div class="admin-panel-header admin-panel-header--toolbar">
            <form method="GET" class="admin-table-search">
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name or email...">
            </form>
        </div>

        <x-admin::data-table>
            <x-slot:head>
                <tr>
                    @foreach (['name' => 'Name', 'email' => 'Email', 'role' => 'Role', 'created_at' => 'Joined'] as $key => $label)
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => $key, 'direction' => $sort === $key && $direction === 'asc' ? 'desc' : 'asc']) }}" class="admin-sort-link">
                                {{ $label }}
                                @if ($sort === $key)
                                    <i class="bi {{ $direction === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }}"></i>
                                @endif
                            </a>
                        </th>
                    @endforeach
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:head>

            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="admin-badge admin-badge--info">{{ ucfirst($user->role) }}</span></td>
                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                    <td><span class="admin-badge admin-badge--success">Active</span></td>
                    <td class="text-end">
                        <div class="admin-table-actions">
                            <a href="{{ route('admin.users.edit', $user) }}" class="admin-table-action" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-table-action admin-table-action--danger" title="Delete"><i class="bi bi-trash-fill"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="admin-table-empty">No users found.</td>
                </tr>
            @endforelse
        </x-admin::data-table>

        <div class="admin-pagination">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
