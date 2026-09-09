@extends('admin.layouts.app', ['title' => 'Audit Log'])

@php
    $actionBadge = [
        'created' => 'success',
        'updated' => 'info',
        'deleted' => 'danger',
        'restored' => 'warning',
    ];

    $formatValue = function ($value) {
        if (is_null($value)) {
            return '—';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES);
        }
        return \Illuminate\Support\Str::limit((string) $value, 120);
    };
@endphp

@section('content')
    <div class="admin-page-heading">
        <h1>Audit Log</h1>
        <p>Every create, update and delete across the control plane, plus role and permission changes. Append-only (SRS &sect;75).</p>
    </div>

    <div class="admin-panel">
        <form method="GET" action="{{ route('admin.audit.index') }}" class="admin-panel-header admin-panel-header--toolbar flex-wrap gap-2">
            <select name="action" class="form-select" onchange="this.form.submit()">
                <option value="">All actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                @endforeach
            </select>

            <select name="entity_type" class="form-select" onchange="this.form.submit()">
                <option value="">All entities</option>
                @foreach ($entityTypes as $type)
                    <option value="{{ $type }}" @selected(request('entity_type') === $type)>{{ $type }}</option>
                @endforeach
            </select>

            <select name="actor" class="form-select" onchange="this.form.submit()">
                <option value="">All actors</option>
                <option value="system" @selected(request('actor') === 'system')>System / scheduled</option>
                @foreach ($actors as $actor)
                    <option value="{{ $actor->id }}" @selected((string) request('actor') === (string) $actor->id)>{{ $actor->name }}</option>
                @endforeach
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" aria-label="From date">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" aria-label="To date">

            <button type="submit" class="btn btn-primary">Filter</button>
            @if (request()->hasAny(['action', 'entity_type', 'actor', 'date_from', 'date_to']))
                <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-secondary">Reset</a>
            @endif
        </form>

        <x-admin::data-table>
            <x-slot:head>
                <tr>
                    <th>When</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Changes</th>
                </tr>
            </x-slot:head>

            @forelse ($logs as $log)
                <tr>
                    <td class="text-nowrap" title="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</td>
                    <td>{{ $log->actor_label ?? 'System' }}</td>
                    <td><span class="admin-badge admin-badge--{{ $actionBadge[$log->action] ?? 'info' }}">{{ $log->action }}</span></td>
                    <td class="text-secondary">{{ $log->entity_name ?? '—' }}</td>
                    <td>
                        @if (empty($log->change_set))
                            <span class="text-secondary">—</span>
                        @else
                            <ul class="mb-0 ps-3 small">
                                @foreach ($log->change_set as $field => $pair)
                                    <li>
                                        <strong>{{ $field }}</strong>:
                                        <span class="text-secondary">{{ $formatValue($pair['from']) }}</span>
                                        &rarr;
                                        {{ $formatValue($pair['to']) }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="admin-table-empty">No audit entries match these filters.</td>
                </tr>
            @endforelse
        </x-admin::data-table>

        <div class="admin-pagination">{{ $logs->links() }}</div>
    </div>
@endsection
