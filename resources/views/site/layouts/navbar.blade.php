<nav>

    <div class="logo">
        <a href="{{ route('site.index') }}">
            <img src="img/navbar/logo.svg">
            <h1>Furniture</h1>
        </a>
    </div>

    <div class="links">
        <ul>
            <li>
                <a href="{{ route('site.index') }}">Home</a>
            </li>
            <li>
                <a href="{{ route('site.shop') }}">Shop</a>
            </li>
            <li>
                <a href="{{ route('site.sobre-nos') }}">Sobre </a>
            </li>
            <li>
                <a href="{{ route('site.contato') }}">Contato</a>
            </li>
        </ul>
    </div>


    <div class="icons-nav">

        <span class="account-menu">
            <img src="{{ asset('img/navbar/person.svg') }}" alt="Minha conta">
            @auth
                <span>{{ auth()->user()->name }}</span>
                @can('access-admin')
                    <a href="{{ route('admin.dashboard') }}">Painel</a>
                @endcan
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sair</button>
                </form>
            @else
                <a href="{{ route('login') }}">Entrar</a>
                <a href="{{ route('register') }}">Cadastrar</a>
            @endauth
        </span>

        <span>
            <img src="img/navbar/favorite.svg" alt="heart">
        </span>

        <span>
            <img src="img/navbar/search.svg" alt="search">
        </span>

        <span>
            <img src="img/navbar/shopping_cart.svg" alt="shopping">
        </span>

    </div>

</nav>
