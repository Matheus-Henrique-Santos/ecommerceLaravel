@foreach ($produtos as $produto)
<div class="product-box">
    <div class="product-description">
        <img src="{{ asset($produto['imagem']) }}" alt="{{ $produto['nome'] }}">
        <h1>{{ $produto['nome'] }}</h1>
        <h3>{{ $produto['descricao'] }}</h3>
        <span>Com Desconto: R$ {{ $produto['valor_com_desconto'] }}</span>
        <br>
        <span>Sem Desconto: R$ {{ $produto['valor_sem_desconto'] }}</span>
    </div>
    <div class="product-hidden">
        <div class="product-hidden-content">
            <div>
                <button>ADICIONAR AO CARRINHO</button>
            </div>
            <div class="product-hidden-links">
                <ul>
                    <li>
                        <a href="#"><img src="img/main/product/share.svg" alt="share">Postar</a>
                    </li>
                    <li>
                        <a href="#"><img src="img/main/product/compare.svg" alt="compare">Compare</a>
                    </li>
                    <li>
                        <a href="#"><img src="img/main/product/heart.svg" alt="heart">Gostei</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endforeach

