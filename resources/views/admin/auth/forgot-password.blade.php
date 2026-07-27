@extends('admin.layouts.guest', ['title' => 'Forgot Password — CodexeHub'])

@section('content')
    <h1 class="admin-auth-title">Forgot your password?</h1>
    <p class="admin-auth-subtitle">Enter your email and we'll send you a reset link.</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.password.email') }}" class="admin-auth-form">
        @csrf

        <div class="mb-4">
            <label for="email" class="form-label">Email address</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Send Reset Link <i class="bi bi-arrow-right"></i></button>
    </form>

    <a href="{{ route('admin.login') }}" class="admin-auth-link admin-auth-back"><i class="bi bi-arrow-left"></i> Back to login</a>
@endsection
