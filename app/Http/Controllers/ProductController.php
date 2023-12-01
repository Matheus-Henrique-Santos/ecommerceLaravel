<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function newProduct()
    {
        $produtos = [
            ['nome' => 'Celular 1', 'descricao' => 'Smartphone 1', 'valor_com_desconto' => 300, 'valor_sem_desconto' => 350, 'imagem' => 'img/main/product/product-1.svg'],

            ['nome' => 'Celular 2', 'descricao' => 'Smartphone 2', 'valor_com_desconto' => 300, 'valor_sem_desconto' => 350, 'imagem' => 'img/main/product/product-1.svg'],
        ];
        
        dd($produtos);

        return view('site.components.product', compact('produtos'));
    }
}