@extends('site.layouts.app')

@section('title', '| Novo produto')

@section('conteudo')
<main class="admin-page">
    <nav class="breadcrumb" aria-label="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Painel</a> &rsaquo;
        <a href="{{ route('admin.products.index') }}">Produtos</a> &rsaquo; <span>Novo</span>
    </nav>

    <h1>Novo produto</h1>

    <livewire:admin.products.product-editor />
</main>
@endsection
