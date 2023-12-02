@extends('site.layouts.app')

@section('conteudo')
    <div class="header-home">
        <div class="square-home">

            <div class="square-home-content">
                <div>
                    <h3>Nova Chegada</h3>
                </div>
                <div class="title-home">
                    <div>
                        <h1>
                            Descubra nossa
                        </h1>
                    </div>
                    <div>
                        <h1>
                            nova coleção
                        </h1>
                    </div>
                </div>
                <div>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper
                        mattis.
                    </p>
                </div>
            </div>

            <div class="square-home-btn">
                <button class="btn">Comprar Agora</button>
            </div>
        </div>
    </div>

    <div class="main-home">
        <div class="section-variety-home">
            <div class="section-title-home">
                <h2>
                    Navegue pelas Variedades
                </h2>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                </p>
            </div>
            <div class="section-img-home">
                <div class="section-img-bloco">
                    <img src="img/main/home/img106.svg" alt="img1">
                    <h3>Sala de jantar</h3>
                </div>
                <div class="section-img-bloco">
                    <img src="img/main/home/img100.svg" alt="img2">
                    <h3>Sala de estar</h3>
                </div>
                <div class="section-img-bloco">
                    <img src="img/main/home/img101.svg" alt="img3">
                    <h3>Quarto</h3>
                </div>

            </div>
        </div>

        <div class="section-product-main">
            <h1>Outros Produtos</h1>

            @if ($mostrarProdutos)
                <div class="section-product">
                    @if (!empty($produtos))
                        @include('site.components.product')
                    @else
                        <p>Nenhum produto disponível.</p>
                    @endif
                </div>
            @else
                <p>A seção de produtos não está sendo exibida. Nenhum produto disponível.</p>
            @endif
        </div>
    </div>
@endsection
