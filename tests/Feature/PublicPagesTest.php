<?php

it('renders public page', function (string $routeName) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertViewIs($routeName === 'site.index' ? 'site.principal' : $routeName);
})->with([
    'home' => 'site.index',
    'shop' => 'site.shop',
    'about' => 'site.sobre-nos',
    'contact' => 'site.contato',
    'cart' => 'site.cart',
]);

it('shows featured products on the home page', function () {
    $this->get(route('site.index'))
        ->assertOk()
        ->assertViewHas('mostrarProdutos', true)
        ->assertSee('Celular 1');
});

it('renders the store 404 page for unknown routes', function () {
    $this->get('/rota-que-nao-existe')
        ->assertNotFound()
        ->assertSee('Página não encontrada')
        ->assertSee(route('site.index'), escape: false);
});

it('does not expose the legacy debug routes', function (string $uri) {
    $this->get($uri)->assertNotFound();
})->with([
    '/teste/1/2',
    '/clientes/fornecedores',
    '/product',
]);
