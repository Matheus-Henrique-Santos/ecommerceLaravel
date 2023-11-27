<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title>Furniture @yield('title')</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
        <link rel="shortcut icon" href="{{asset ('img/favicon.ico')}}" type="image/x-icon">
    </head>

    <body>
       @include('site/layouts/navbar')
       @yield('conteudo')
       <hr>
       @include('site/layouts/footer')
    </body>
</html>
