<?php

namespace App\Http\Controllers;

use App\Models\Livro;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LojaController extends Controller
{
    public function index()
    {
        // Livros em destaque (mais recentes)
        $livrosDestaque = Livro::where('ativo', true)
            ->where('estoque', '>', 0)
            ->latest()
            ->limit(8)
            ->get();

        // Livros mais vendidos (simulado por mais visualizados)
        $livrosMaisVendidos = Livro::where('ativo', true)
            ->where('estoque', '>', 0)
            ->inRandomOrder() // Em produção, seria orderBy('vendas', 'desc')
            ->limit(6)
            ->get();

        // Categorias populares
        $categoriasPopulares = Livro::select('categoria', DB::raw('count(*) as total'))
            ->where('ativo', true)
            ->whereNotNull('categoria')
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // Ofertas especiais (livros com preço menor)
        $ofertas = Livro::where('ativo', true)
            ->where('estoque', '>', 0)
            ->where('preco', '<', 50)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // Estatísticas da loja
        $estatisticas = [
            'total_livros' => Livro::where('ativo', true)->count(),
            'total_categorias' => Livro::whereNotNull('categoria')->distinct('categoria')->count(),
            'total_autores' => Livro::distinct('autor')->count(),
            'livros_estoque' => Livro::where('estoque', '>', 0)->sum('estoque')
        ];

        return view('loja.index', compact(
            'livrosDestaque',
            'livrosMaisVendidos', 
            'categoriasPopulares',
            'ofertas',
            'estatisticas'
        ));
    }

    public function categoria($categoria)
    {
        $livros = Livro::where('categoria', $categoria)
            ->where('ativo', true)
            ->paginate(12);

        $totalLivros = $livros->total();

        return view('loja.categoria', compact('livros', 'categoria', 'totalLivros'));
    }

    public function buscar(Request $request)
    {
        $termo = $request->get('q');
        
        if (empty($termo)) {
            return redirect()->route('loja.index');
        }

        $livros = Livro::where('ativo', true)
            ->where(function($query) use ($termo) {
                $query->where('titulo', 'like', "%{$termo}%")
                      ->orWhere('autor', 'like', "%{$termo}%")
                      ->orWhere('categoria', 'like', "%{$termo}%")
                      ->orWhere('sinopse', 'like', "%{$termo}%");
            })
            ->paginate(12);

        return view('loja.busca', compact('livros', 'termo'));
    }

    public function detalhes(Livro $livro)
    {
        // Verificar se o livro está ativo
        if (!$livro->ativo) {
            abort(404);
        }

        // Livros relacionados da mesma categoria
        $livrosRelacionados = Livro::where('categoria', $livro->categoria)
            ->where('id', '!=', $livro->id)
            ->where('ativo', true)
            ->where('estoque', '>', 0)
            ->limit(4)
            ->get();

        // Verificar se está nos favoritos do usuário
        $isFavorito = false;
        if (auth()->check()) {
            $isFavorito = auth()->user()->favorites()->where('livro_id', $livro->id)->exists();
        }

        return view('loja.detalhes', compact('livro', 'livrosRelacionados', 'isFavorito'));
    }

    public function favoritos()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $favoritos = auth()->user()->favorites()->where('ativo', true)->paginate(12);

        return view('loja.favoritos', compact('favoritos'));
    }
}