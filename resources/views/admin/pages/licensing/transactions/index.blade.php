@extends('admin.layouts.app', ['title' => 'License Transactions'])

@section('content')
    <div class="admin-page-heading">
        <h1>License Transactions</h1>
        <p>bKash payments recorded against licenses.</p>
    </div>

    @include('admin.pages.licensing._nav')

    <div class="admin-panel">
        <x-admin::data-table>
            <x-slot:head>
                <tr>
                    <th>Customer</th>
                    <th>App</th>
                    <th>Method</th>
                    <th>Txn ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Approved By</th>
                    <th>Action</th>
                </tr>
            </x-slot:head>

            @forelse ($transactions as $txn)
                <tr>
                    <td>{{ $txn->license->customer->name }}</td>
                    <td>{{ $txn->license->licensedApp->name }}</td>
                    <td>{{ strtoupper($txn->method) }}</td>
                    <td>{{ $txn->txn_id }}</td>
                    <td>{{ number_format($txn->amount, 2) }}</td>
                    <td><span class="admin-badge admin-badge--{{ $txn->status === 'approved' ? 'success' : ($txn->status === 'rejected' ? 'danger' : 'warning') }}">{{ $txn->status }}</span></td>
                    <td>{{ $txn->approver->name ?? '-' }}</td>
                    <td>
                        <div class="admin-table-actions">
                            <a href="{{ route('admin.licensing.licenses.show', $txn->license) }}" class="admin-table-action" title="Open license"><i class="bi bi-box-arrow-up-right"></i></a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="admin-table-empty">No transactions yet.</td>
                </tr>
            @endforelse
        </x-admin::data-table>

        <div class="admin-pagination">{{ $transactions->links() }}</div>
    </div>
@endsection
