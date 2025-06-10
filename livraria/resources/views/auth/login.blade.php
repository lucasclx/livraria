{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.auth')
@section('title', 'Login - Livraria Mil Páginas')
@section('subtitle', 'Faça login em sua conta')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                   name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
        </div>
        @error('email')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Senha</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                   name="password" required autocomplete="current-password">
        </div>
        @error('password')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 form-check">
        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
        <label class="form-check-label" for="remember">
            Lembrar-me
        </label>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-sign-in-alt me-2"></i>Entrar
        </button>
    </div>
</form>

<div class="text-center mt-4">
    @if (Route::has('password.request'))
        <a class="text-muted text-decoration-none" href="{{ route('password.request') }}">
            Esqueceu sua senha?
        </a>
    @endif
</div>

<hr class="my-4">

<div class="text-center">
    <p class="mb-0">Não tem uma conta?</p>
    <a href="{{ route('register') }}" class="btn btn-outline-secondary">
        <i class="fas fa-user-plus me-1"></i>Criar conta
    </a>
</div>

<div class="mt-4 p-3 bg-light rounded">
    <h6><i class="fas fa-info-circle me-1"></i>Conta de Teste:</h6>
    <small>
        <strong>Admin:</strong> admin@livraria.com / admin123<br>
        <strong>Cliente:</strong> Registre-se ou use uma conta existente
    </small>
</div>
@endsection