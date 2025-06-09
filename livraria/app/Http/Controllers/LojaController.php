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
        /* 1. Livros em destaque (mais recentes) */
        $livrosDestaque = Livro::ativo()
            ->emEstoque()
            ->latest()
            ->limit(8)
            ->get();

        /* 2. Livros mais vendidos (aqui simulamos com aleatório; 
              troque pela métrica real — ex.: visualizações — se existir) */
        $livrosMaisVendidos = Livro::ativo()
            ->emEstoque()
            ->inRandomOrder()
            ->limit(6)
            ->get();

        /* 3. Livros por categoria (usando categoria_id) */
        $livrosPorCategoria = Categoria::select('categorias.id', 'categorias.nome', 'categorias.slug', DB::raw('COUNT(livros.id) AS total'))
            ->join('livros', 'livros.categoria_id', '=', 'categorias.id')
            ->where('livros.ativo', true)
            ->where('livros.estoque', '>', 0)
            ->groupBy('categorias.id', 'categorias.nome', 'categorias.slug')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        /* 4. Ofertas especiais (preço < 50) */
        $ofertas = Livro::ativo()
            ->emEstoque()
            ->where('preco', '<', 50)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        /* 5. Estatísticas da loja */
        $estatisticas = [
            'total_livros'     => Livro::ativo()->count(),
            'total_categorias' => Categoria::has('livros')->count(), // apenas categorias com livros
            'total_autores'    => Livro::distinct('autor')->count(),
            'livros_estoque'   => Livro::ativo()->sum('estoque'),
        ];

        /* 6. Catálogo completo (pagina 12) */
        $livros = Livro::ativo()
            ->emEstoque()
            ->paginate(12);

        return view('loja.index', compact(
            'livrosDestaque',
            'livrosMaisVendidos',
            'livrosPorCategoria',
            'ofertas',
            'estatisticas',
            'livros'
        ));
    }

    public function catalogo(Request $request)
    {
        $query = Livro::where('ativo', true)->with('categoria');
        
        // Busca por termo
        if ($request->filled('busca')) {
            $termo = $request->busca;
            $query->where(function($q) use ($termo) {
                $q->where('titulo', 'like', "%{$termo}%")
                  ->orWhere('autor', 'like', "%{$termo}%")
                  ->orWhere('sinopse', 'like', "%{$termo}%")
                  ->orWhereHas('categoria', function($query) use ($termo) {
                      $query->where('nome', 'like', "%{$termo}%");
                  });
            });
        }
        
        // Filtro por categoria
        if ($request->filled('categoria')) {
            // Se categoria é um ID numérico
            if (is_numeric($request->categoria)) {
                $query->where('categoria_id', $request->categoria);
            } else {
                // Se categoria é um nome/slug
                $query->whereHas('categoria', function($q) use ($request) {
                    $q->where('nome', $request->categoria)
                      ->orWhere('slug', $request->categoria);
                });
            }
        }
        
        // Filtro por faixa de preço
        if ($request->filled('preco_min')) {
            $query->where('preco', '>=', $request->preco_min);
        }
        if ($request->filled('preco_max')) {
            $query->where('preco', '<=', $request->preco_max);
        }
        
        // Filtro por disponibilidade
        if ($request->filled('disponivel')) {
            $query->where('estoque', '>', 0);
        }
        
        // Ordenação
        $orderBy = $request->get('ordem', 'titulo');
        $direction = $request->get('direcao', 'asc');
        
        if ($orderBy === 'popularidade') {
            $query->inRandomOrder(); // Simular popularidade
        } else {
            $query->orderBy($orderBy, $direction);
        }
        
        $livros = $query->paginate(12)->withQueryString();
        
        // Dados para filtros - corrigido para usar relacionamento
        $categorias = Categoria::whereHas('livros', function($query) {
            $query->where('ativo', true);
        })
        ->orderBy('nome')
        ->get();
        
        return view('loja.catalogo', compact('livros', 'categorias'));
    }

    public function categoria($categoriaSlug)
    {
        // Buscar categoria pelo slug ou nome
        $categoria = Categoria::where('slug', $categoriaSlug)
                             ->orWhere('nome', $categoriaSlug)
                             ->firstOrFail();
        
        $livros = Livro::where('categoria_id', $categoria->id)
            ->where('ativo', true)
            ->with('categoria')
            ->paginate(12);

        $totalLivros = $livros->total();

        return view('loja.categoria', compact('livros', 'categoria', 'totalLivros'));
    }

    public function detalhes(Livro $livro)
    {
        // Verificar se o livro está ativo
        if (!$livro->ativo) {
            abort(404);
        }

        // Carregar o relacionamento com categoria
        $livro->load('categoria');

        // CORREÇÃO DO BUG: Livros relacionados usando categoria_id
        $livrosRelacionados = collect();
        
        if ($livro->categoria_id) {
            $livrosRelacionados = Livro::where('categoria_id', $livro->categoria_id)
                ->where('id', '!=', $livro->id)
                ->where('ativo', true)
                ->where('estoque', '>', 0)
                ->with('categoria')
                ->limit(4)
                ->get();
        }

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

        $favoritos = auth()->user()->favorites()
            ->where('ativo', true)
            ->with('categoria')
            ->paginate(12);

        return view('loja.favoritos', compact('favoritos'));
    }

    public function buscar(Request $request)
    {
        $termo = $request->get('q');
        
        if (empty($termo)) {
            return redirect()->route('loja.catalogo');
        }

        $livros = Livro::where('ativo', true)
            ->with('categoria')
            ->where(function($query) use ($termo) {
                $query->where('titulo', 'like', "%{$termo}%")
                      ->orWhere('autor', 'like', "%{$termo}%")
                      ->orWhere('sinopse', 'like', "%{$termo}%")
                      ->orWhereHas('categoria', function($q) use ($termo) {
                          $q->where('nome', 'like', "%{$termo}%");
                      });
            })
            ->paginate(12);

        return view('loja.busca', compact('livros', 'termo'));
    }
}