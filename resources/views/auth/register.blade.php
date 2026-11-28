@extends('site.layouts.app')

@section('title', '| Cadastro')

@section('conteudo')
<main class="auth-page">
    <h1>Criar conta</h1>

    <form method="POST" action="{{ route('register.store') }}" class="auth-form" novalidate>
        @csrf

        <label>
            Nome
            <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
        </label>
        @error('name') <p class="form-error">{{ $message }}</p> @enderror

        <label>
            E-mail
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
        </label>
        @error('email') <p class="form-error">{{ $message }}</p> @enderror

        <label>
            Senha
            <input type="password" name="password" required autocomplete="new-password">
        </label>
        @error('password') <p class="form-error">{{ $message }}</p> @enderror

        <label>
            Confirme a senha
            <input type="password" name="password_confirmation" required autocomplete="new-password">
        </label>

        <button type="submit">Cadastrar</button>
    </form>

    <p>Já tem conta? <a href="{{ route('login') }}">Entrar</a></p>
</main>
@endsection
