@extends('admin.layouts.app', ['title' => 'Licensed Apps'])

@section('content')
    <div class="admin-page-heading d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>Licensed Apps</h1>
            <p>The software titles you license out via codexehub/license-guard -- separate from your public Products catalog.</p>
        </div>
        <a href="{{ route('admin.licensing.apps.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add App</a>
    </div>

    @include('admin.pages.licensing._nav')

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
        <x-admin::data-table>
            <x-slot:head>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Packages</th>
                    <th>Licenses</th>
                    <th>Active</th>
                    <th>Action</th>
                </tr>
            </x-slot:head>

            @forelse ($licensedApps as $app)
                <tr>
                    <td>{{ $app->name }}</td>
                    <td><code>{{ $app->slug }}</code></td>
                    <td>{{ $app->packages_count }}</td>
                    <td>{{ $app->licenses_count }}</td>
                    <td><span class="admin-badge admin-badge--{{ $app->is_active ? 'success' : 'danger' }}">{{ $app->is_active ? 'Yes' : 'No' }}</span></td>
                    <td>
                        <div class="admin-table-actions">
                            <a href="{{ route('admin.licensing.apps.edit', $app) }}" class="admin-table-action" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.licensing.apps.destroy', $app) }}" onsubmit="return confirm('Delete this app? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-table-action admin-table-action--danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="admin-table-empty">No licensed apps yet.</td>
                </tr>
            @endforelse
        </x-admin::data-table>

        <div class="admin-pagination">{{ $licensedApps->links() }}</div>
    </div>
@endsection
