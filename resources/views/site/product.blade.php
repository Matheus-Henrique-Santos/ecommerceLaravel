@extends('site.layouts.app')

@section('title', '| '.$product->name)

@section('conteudo')
<main class="product-page">
    <nav class="breadcrumb" aria-label="breadcrumb">
        <a href="{{ route('site.shop') }}">Shop</a>
        &rsaquo;
        <a href="{{ route('site.shop', ['categoria' => $product->category->slug]) }}">{{ $product->category->name }}</a>
        &rsaquo;
        <span>{{ $product->name }}</span>
    </nav>

    <article class="product-detail">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}">

        <div>
            <h1>{{ $product->name }}</h1>
            <p class="product-sku">SKU: {{ $product->sku }}</p>
            <strong class="product-price">{{ $product->formatted_price }}</strong>

            @if ($product->isInStock())
                <p class="product-stock">{{ $product->stock }} em estoque</p>
            @else
                <p class="product-stock is-out">Esgotado</p>
            @endif

            <livewire:cart.add-to-cart :product="$product" />

            @if ($product->description)
                <p class="product-description">{{ $product->description }}</p>
            @endif
        </div>
    </article>
</main>
@endsection
