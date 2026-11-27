@extends('site.layouts.app')

@section('title', '| Shop')

@section('conteudo')
<main class="shop">
    <h1>Shop</h1>
    <livewire:shop.product-catalog />
</main>
@endsection
