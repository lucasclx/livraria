<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Livro;
use App\Models\Categoria;
use App\Models\User;
use App\Models\Order;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Redirecionar usuários não-admin para a loja
        if (!auth()->user()->is_admin) {
            return redirect()->route('loja.index');
        }

        // Estatísticas para admin
        $stats = [
            'total_livros' => Livro::count(),
            'livros_estoque' => Livro::where('estoque', '>', 0)->count(),
            'estoque_baixo' => Livro::estoqueBaixo()->count(),
            'valor_estoque' => Livro::where('estoque', '>', 0)
                                  ->get()
                                  ->sum(fn($livro) => $livro->estoque * $livro->preco_final),
        ];

        // Livros com estoque baixo
        $estoqueBaixo = Livro::estoqueBaixo()
                            ->with('categoria')
                            ->orderBy('estoque')
                            ->limit(10)
                            ->get();

        // Livros sem estoque
        $semEstoque = Livro::where('estoque', 0)
                          ->where('ativo', true)
                          ->with('categoria')
                          ->orderBy('updated_at', 'desc')
                          ->limit(10)
                          ->get();

        return view('admin.dashboard', compact('stats', 'estoqueBaixo', 'semEstoque'));
    }
}