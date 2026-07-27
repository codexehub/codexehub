@extends('admin.layouts.guest', ['title' => 'Admin Login — CodexeHub'])

@section('content')
    <h1 class="admin-auth-title">Welcome back</h1>
    <p class="admin-auth-subtitle">Sign in to manage your CodexeHub dashboard.</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login') }}" class="admin-auth-form">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                <label for="remember" class="form-check-label">Remember me</label>
            </div>
            <a href="{{ route('admin.password.request') }}" class="admin-auth-link">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100">Sign In <i class="bi bi-arrow-right"></i></button>
    </form>
@endsection
