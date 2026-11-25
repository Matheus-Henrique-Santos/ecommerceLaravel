@extends('site.layouts.app')

@section('title', '| Página não encontrada')

@section('conteudo')
    <div class="not-found">
        <h1>Página não encontrada</h1>
        <p>A página que você procura não existe ou foi removida.</p>
        <a href="{{ route('site.index') }}">Voltar para a página inicial</a>
    </div>
@endsection
