@extends('site.layouts.app')

@section('title', '| Editar '.$product->name)

@section('conteudo')
<main class="admin-page">
    <nav class="breadcrumb" aria-label="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Painel</a> &rsaquo;
        <a href="{{ route('admin.products.index') }}">Produtos</a> &rsaquo; <span>{{ $product->name }}</span>
    </nav>

    <h1>Editar produto</h1>

    <livewire:admin.products.product-editor :product="$product" />
</main>
@endsection
