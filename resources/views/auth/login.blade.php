@extends('site.layouts.app')

@section('title', '| Entrar')

@section('conteudo')
<main class="auth-page">
    <h1>Entrar</h1>

    <form method="POST" action="{{ route('login.store') }}" class="auth-form" novalidate>
        @csrf

        <label>
            E-mail
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </label>
        @error('email') <p class="form-error">{{ $message }}</p> @enderror

        <label>
            Senha
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        @error('password') <p class="form-error">{{ $message }}</p> @enderror

        <label class="checkbox">
            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
            Manter conectado
        </label>

        <button type="submit">Entrar</button>
    </form>

    <p>Ainda não tem conta? <a href="{{ route('register') }}">Cadastre-se</a></p>
</main>
@endsection
