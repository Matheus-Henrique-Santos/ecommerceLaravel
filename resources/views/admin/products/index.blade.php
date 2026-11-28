@extends('site.layouts.app')

@section('title', '| Produtos')

@section('conteudo')
<main class="admin-page">
    <nav class="breadcrumb" aria-label="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Painel</a> &rsaquo; <span>Produtos</span>
    </nav>

    <h1>Produtos</h1>

    @if (session('status'))
        <p class="admin-notice" role="status">{{ session('status') }}</p>
    @endif

    <livewire:admin.products.product-table />
</main>
@endsection
