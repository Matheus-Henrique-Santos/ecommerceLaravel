@foreach ($produtos as $produto)
<div class="product-box">
    <img src="{{ asset($produto['imagem']) }}" alt="{{ $produto['nome'] }}">
    <div class="product-description">
        <h1>{{ $produto['nome'] }}</h1>
        <h3>{{ $produto['descricao'] }}</h3>
        <span>Com Desconto: R$ {{ $produto['valor_com_desconto'] }}</span>
        <br>
        <span>Sem Desconto: R$ {{ $produto['valor_sem_desconto'] }}</span>
    </div>
</div>
@endforeach
