@extends('admin.layouts.app', ['title' => 'Dashboard'])

@section('content')
    <div class="admin-page-heading">
        <h1>Dashboard Overview</h1>
        <p>Welcome back, {{ auth()->user()->name }}. Here's what's happening today.</p>
    </div>

    <div class="admin-stat-grid">
        @foreach ($stats as $stat)
            <x-admin::stat-card :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon']" :change="$stat['change']" :trend="$stat['trend']" />
        @endforeach
    </div>

    <div class="admin-chart-grid">
        <div class="admin-panel admin-panel--wide">
            <div class="admin-panel-header">
                <h2>Monthly Sales Overview</h2>
            </div>
            <canvas data-chart="line" data-labels="{{ json_encode($charts['sales']['labels']) }}" data-values="{{ json_encode($charts['sales']['data']) }}" data-color="primary" height="110"></canvas>
        </div>

        <div class="admin-panel">
            <div class="admin-panel-header">
                <h2>Website Traffic</h2>
            </div>
            <canvas data-chart="pie" data-labels="{{ json_encode($charts['traffic']['labels']) }}" data-values="{{ json_encode($charts['traffic']['data']) }}" height="220"></canvas>
        </div>

        <div class="admin-panel">
            <div class="admin-panel-header">
                <h2>User Registrations</h2>
            </div>
            <canvas data-chart="bar" data-labels="{{ json_encode($charts['registrations']['labels']) }}" data-values="{{ json_encode($charts['registrations']['data']) }}" data-color="accent" height="220"></canvas>
        </div>

        <div class="admin-panel admin-panel--wide">
            <div class="admin-panel-header">
                <h2>Project Statistics</h2>
            </div>
            <canvas data-chart="area" data-labels="{{ json_encode($charts['projects']['labels']) }}" data-values="{{ json_encode($charts['projects']['data']) }}" data-color="accent" height="110"></canvas>
        </div>
    </div>

    <div class="admin-panel">
        <div class="admin-panel-header">
            <h2>Recent Users</h2>
            <a href="{{ route('admin.users.index') }}" class="admin-panel-link">View all <i class="bi bi-arrow-right"></i></a>
        </div>

        <x-admin::data-table>
            <x-slot:head>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Status</th>
                </tr>
            </x-slot:head>

            @foreach ($recentUsers as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="admin-badge admin-badge--info">{{ ucfirst($user->role) }}</span></td>
                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                    <td><span class="admin-badge admin-badge--success">Active</span></td>
                </tr>
            @endforeach
        </x-admin::data-table>
    </div>
@endsection
