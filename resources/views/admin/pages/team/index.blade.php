@extends('admin.layouts.app', ['title' => 'Content Management'])

@section('content')
    @php
        $sortUrl = fn (string $column) => request()->fullUrlWithQuery([
            'sort' => $column,
            'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc',
        ]);
    @endphp

    <div class="admin-page-heading d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>Content Management</h1>
            <p>Manage the team members shown in the "Meet the Experts Behind CodexeHub" section on the About Us page.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.team.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Team Member</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="admin-panel">
        <form method="GET" action="{{ route('admin.team.index') }}" class="admin-panel-header admin-panel-header--toolbar flex-wrap gap-3">
            <div class="admin-table-search">
                <i class="bi bi-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name...">
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.team.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th><a href="{{ $sortUrl('name') }}" class="admin-sort-link">Name <i class="bi bi-arrow-down-up"></i></a></th>
                        <th><a href="{{ $sortUrl('role') }}" class="admin-sort-link">Role <i class="bi bi-arrow-down-up"></i></a></th>
                        <th>Description</th>
                        <th>Gallery</th>
                        <th><a href="{{ $sortUrl('status') }}" class="admin-sort-link">Status <i class="bi bi-arrow-down-up"></i></a></th>
                        <th><a href="{{ $sortUrl('sort_order') }}" class="admin-sort-link">Sort Order <i class="bi bi-arrow-down-up"></i></a></th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td>
                                @if ($member->photo_url)
                                    <img src="{{ $member->photo_url }}" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:50%;">
                                @else
                                    <i class="bi bi-person-fill text-primary" style="font-size:1.4rem;"></i>
                                @endif
                            </td>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->role }}</td>
                            <td>{{ $member->description ? \Illuminate\Support\Str::limit($member->description, 40) : '—' }}</td>
                            <td>{{ $member->images->count() }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.team.status', $member) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="admin-badge admin-badge--{{ $member->status === 'active' ? 'success' : 'danger' }}" style="border:0;cursor:pointer;">
                                        {{ ucfirst($member->status) }}
                                    </button>
                                </form>
                            </td>
                            <td>{{ $member->sort_order }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.team.edit', $member) }}" class="admin-table-action" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('admin.team.destroy', $member) }}" onsubmit="return confirm('Delete this team member? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-table-action admin-table-action--danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="admin-table-empty">No team members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">{{ $members->links() }}</div>
    </div>
@endsection
