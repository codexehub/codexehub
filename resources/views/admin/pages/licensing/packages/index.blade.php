@extends('admin.layouts.app', ['title' => 'Licensing Packages'])

@section('content')
    <div class="admin-page-heading d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>Licensing Packages</h1>
            <p>Pricing tiers (Basic/Pro/...) for each licensed app.</p>
        </div>
        <a href="{{ route('admin.licensing.packages.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Package</a>
    </div>

    @include('admin.pages.licensing._nav')

    <div class="admin-panel">
        <x-admin::data-table>
            <x-slot:head>
                <tr>
                    <th>App</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Max Activations</th>
                    <th>Active</th>
                    <th>Action</th>
                </tr>
            </x-slot:head>

            @forelse ($packages as $package)
                <tr>
                    <td>{{ $package->licensedApp->name }}</td>
                    <td>{{ $package->name }}</td>
                    <td>{{ number_format($package->price, 2) }}</td>
                    <td>{{ $package->duration_days }} days</td>
                    <td>{{ $package->max_activations }}</td>
                    <td><span class="admin-badge admin-badge--{{ $package->is_active ? 'success' : 'danger' }}">{{ $package->is_active ? 'Yes' : 'No' }}</span></td>
                    <td>
                        <div class="admin-table-actions">
                            <a href="{{ route('admin.licensing.packages.edit', $package) }}" class="admin-table-action" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.licensing.packages.destroy', $package) }}" onsubmit="return confirm('Delete this package? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-table-action admin-table-action--danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="admin-table-empty">No packages yet.</td>
                </tr>
            @endforelse
        </x-admin::data-table>

        <div class="admin-pagination">{{ $packages->links() }}</div>
    </div>
@endsection
