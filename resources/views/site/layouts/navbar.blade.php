
<header class="container">
        <div class="page-header">
            <div class="logo">
                <a href="img/navbar/logo.svg">Furniro.</a>
            </div>
            <input type="checkbox" id="click">

            <label for="click" class="mainicon">
                <div class="menu">
                    <i class='bx bx-menu'></i>
                </div>
            </label>
            <ul>

                <li><a href="{{ route('site.index') }}" class="active" style="--navAni:1">Home</a></li>

                <li><a href="{{ route('site.shop') }}" 
                style="--navAni:2">Shop</a></li>

                <li><a href="{{ route('site.sobre-nos') }} " style="--navAni:3">Sobre</a></li>

                <li><a href="{{ route('site.contato') }}" style="--navAni:4">Contato</a></li>

                <li><a href="#" style="--navAni:5">Dev's</a></li>

            </ul>

            <div class="icons-nav">

        <span>
            <img src="img/navbar/person.svg" alt="account">
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
            <label class="mode">
                <input type="checkbox" id="darkModeToggle">
                <i class='bx bxs-moon'></i>
            </label>

        </div>
    </header>


<!-- <nav>

    <div class="logo">
        <img src="img/navbar/logo.svg">
        <h1>Furniro</h1>
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

        <span>
            <img src="img/navbar/account-alert.svg" alt="account">
        </span>

        <span>
            <img src="img/navbar/heart.svg" alt="heart">
        </span>

        <span>
            <img src="img/navbar/search.svg" alt="search">
        </span>

        <span>
            <img src="img/navbar/shopping-cart.svg" alt="shopping">
        </span>
        
    </div>

            <label class="mode">
            <input type="checkbox" id="darkModeToggle">
            <i class='bx bxs-moon'></i>
            </label>

</nav>
 -->
