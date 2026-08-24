@extends('admin.layouts.app', ['title' => 'License Customers'])

@section('content')
    <div class="admin-page-heading d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>License Customers</h1>
            <p>Buyers of your licensed apps.</p>
        </div>
        <a href="{{ route('admin.licensing.customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Customer</a>
    </div>

    @include('admin.pages.licensing._nav')

    <div class="admin-panel">
        <x-admin::data-table>
            <x-slot:head>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Licenses</th>
                    <th>Action</th>
                </tr>
            </x-slot:head>

            @forelse ($customers as $customer)
                <tr>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->licenses_count }}</td>
                    <td>
                        <div class="admin-table-actions">
                            <a href="{{ route('admin.licensing.customers.edit', $customer) }}" class="admin-table-action" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.licensing.customers.destroy', $customer) }}" onsubmit="return confirm('Delete this customer? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-table-action admin-table-action--danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="admin-table-empty">No customers yet.</td>
                </tr>
            @endforelse
        </x-admin::data-table>

        <div class="admin-pagination">{{ $customers->links() }}</div>
    </div>
@endsection
