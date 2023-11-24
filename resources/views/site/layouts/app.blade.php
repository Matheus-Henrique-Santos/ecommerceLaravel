<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title>Furniro @yield('title')</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="{{ asset('css/style_basic.css') }}">
    </head>

    <body>
       @include('site/layouts/navbar')
       @yield('conteudo')
       @include('site/layouts/footer')
    </body>
</html>
