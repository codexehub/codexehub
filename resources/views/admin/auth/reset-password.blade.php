@extends('admin.layouts.guest', ['title' => 'Reset Password — CodexeHub'])

@section('content')
    <h1 class="admin-auth-title">Reset your password</h1>
    <p class="admin-auth-subtitle">Choose a new password for your admin account.</p>

    <form method="POST" action="{{ route('admin.password.update') }}" class="admin-auth-form">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" name="email" id="email" value="{{ old('email', $email) }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New password</label>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirm new password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Reset Password <i class="bi bi-arrow-right"></i></button>
    </form>
@endsection
