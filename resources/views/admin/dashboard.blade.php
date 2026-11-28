@extends('site.layouts.app')

@section('title', '| Painel')

@section('conteudo')
<main class="admin-page">
    <h1>Painel administrativo</h1>
    <p>Olá, {{ auth()->user()->name }}.</p>

    <dl class="admin-stats">
        <div><dt>Produtos</dt><dd>{{ $products }}</dd></div>
        <div><dt>Ativos</dt><dd>{{ $activeProducts }}</dd></div>
        <div><dt>Sem estoque</dt><dd>{{ $outOfStock }}</dd></div>
        <div><dt>Categorias</dt><dd>{{ $categories }}</dd></div>
    </dl>

    <p><a class="admin-button" href="{{ route('admin.products.index') }}">Gerenciar produtos</a></p>
</main>
@endsection
