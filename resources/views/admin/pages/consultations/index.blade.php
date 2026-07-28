@extends('admin.layouts.app', ['title' => 'Consultation Requests'])

@section('content')
    @php
        $sortUrl = fn (string $column) => request()->fullUrlWithQuery([
            'sort' => $column,
            'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc',
        ]);

        $statusVariant = fn (string $status) => match ($status) {
            'won' => 'success',
            'lost', 'archived' => 'danger',
            'new' => 'info',
            default => 'warning',
        };

        $priorityVariant = fn (string $priority) => match ($priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'low' => 'success',
            default => 'info',
        };
    @endphp

    <div class="admin-page-heading">
        <h1>Consultation Requests</h1>
        <p>Free consultation requests submitted from the website.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="admin-panel">
        <form method="GET" action="{{ route('admin.consultations.index') }}" class="admin-panel-header admin-panel-header--toolbar flex-wrap gap-3">
            <div class="admin-table-search">
                <i class="bi bi-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, reference...">
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    @foreach (\App\Models\Consultation::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="priority" class="form-select" onchange="this.form.submit()">
                    <option value="">All Priority</option>
                    @foreach (\App\Models\Consultation::PRIORITIES as $value => $label)
                        <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if (request()->hasAny(['search', 'status', 'priority']))
                    <a href="{{ route('admin.consultations.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th><a href="{{ $sortUrl('full_name') }}" class="admin-sort-link">Name <i class="bi bi-arrow-down-up"></i></a></th>
                        <th>Contact</th>
                        <th><a href="{{ $sortUrl('service') }}" class="admin-sort-link">Service <i class="bi bi-arrow-down-up"></i></a></th>
                        <th><a href="{{ $sortUrl('priority') }}" class="admin-sort-link">Priority <i class="bi bi-arrow-down-up"></i></a></th>
                        <th><a href="{{ $sortUrl('status') }}" class="admin-sort-link">Status <i class="bi bi-arrow-down-up"></i></a></th>
                        <th><a href="{{ $sortUrl('created_at') }}" class="admin-sort-link">Submitted <i class="bi bi-arrow-down-up"></i></a></th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($consultations as $consultation)
                        <tr>
                            <td><code>{{ $consultation->reference_no }}</code></td>
                            <td>{{ $consultation->full_name }}</td>
                            <td>
                                <div>{{ $consultation->email }}</div>
                                <small class="text-secondary">{{ $consultation->phone }}</small>
                            </td>
                            <td>{{ $consultation->service }}</td>
                            <td><span class="admin-badge admin-badge--{{ $priorityVariant($consultation->priority) }}">{{ \App\Models\Consultation::PRIORITIES[$consultation->priority] ?? $consultation->priority }}</span></td>
                            <td><span class="admin-badge admin-badge--{{ $statusVariant($consultation->status) }}">{{ \App\Models\Consultation::STATUSES[$consultation->status] ?? $consultation->status }}</span></td>
                            <td>{{ $consultation->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.consultations.edit', $consultation) }}" class="admin-table-action" title="View / Manage"><i class="bi bi-eye"></i></a>
                                    <form method="POST" action="{{ route('admin.consultations.destroy', $consultation) }}" onsubmit="return confirm('Delete this consultation request? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-table-action admin-table-action--danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="admin-table-empty">No consultation requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">{{ $consultations->links() }}</div>
    </div>
@endsection
