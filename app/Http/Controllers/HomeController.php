<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Página inicial da loja.
     *
     * Os produtos em destaque ainda são estáticos; passam a vir do catálogo
     * (Category/Product) quando o módulo de catálogo for entregue.
     */
    public function __invoke(): View
    {
        $produtos = [
            ['nome' => 'Celular 1', 'descricao' => 'Smartphone 1', 'valor_com_desconto' => 300, 'valor_sem_desconto' => 350, 'imagem' => 'img/main/product/product-1.svg'],
            ['nome' => 'Celular 2', 'descricao' => 'Smartphone 2', 'valor_com_desconto' => 300, 'valor_sem_desconto' => 350, 'imagem' => 'img/main/product/product-1.svg'],
        ];

        return view('site.principal', [
            'produtos' => $produtos,
            'mostrarProdutos' => $produtos !== [],
        ]);
    }
}
