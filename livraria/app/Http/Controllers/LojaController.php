<?php

namespace App\Http\Controllers;

use App\Models\Livro;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LojaController extends Controller
{
    public function index()
    {
        try {
            // 1. Livros em destaque (mais recentes)
            $livrosDestaque = Livro::ativo()
                ->emEstoque()
                ->with('categoria')
                ->where('destaque', true)
                ->latest()
                ->limit(8)
                ->get();

            // Se não há livros em destaque, pegar os mais recentes
            if ($livrosDestaque->isEmpty()) {
                $livrosDestaque = Livro::ativo()
                    ->emEstoque()
                    ->with('categoria')
                    ->latest()
                    ->limit(8)
                    ->get();
            }

            // 2. Livros mais vendidos (usando vendas_total)
            $livrosMaisVendidos = Livro::ativo()
                ->emEstoque()
                ->with('categoria')
                ->orderByDesc('vendas_total')
                ->limit(6)
                ->get();

            // Se não há vendas registradas, usar aleatório
            if ($livrosMaisVendidos->isEmpty()) {
                $livrosMaisVendidos = Livro::ativo()
                    ->emEstoque()
                    ->with('categoria')
                    ->inRandomOrder()
                    ->limit(6)
                    ->get();
            }

            // 3. Livros por categoria
            $livrosPorCategoria = Categoria::select([
                    'categorias.id',
                    'categorias.nome',
                    'categorias.slug',
                    DB::raw('COUNT(livros.id) AS total')
                ])
                ->join('livros', 'livros.categoria_id', '=', 'categorias.id')
                ->where('categorias.ativo', true)
                ->where('livros.ativo', true)
                ->where('livros.estoque', '>', 0)
                ->groupBy('categorias.id', 'categorias.nome', 'categorias.slug')
                ->orderByDesc('total')
                ->limit(8)
                ->get();

            // 4. Ofertas especiais (livros em promoção)
            $ofertas = Livro::ativo()
                ->emEstoque()
                ->promocao()
                ->with('categoria')
                ->inRandomOrder()
                ->limit(4)
                ->get();

            // Se não há promoções, usar livros com preço baixo
            if ($ofertas->isEmpty()) {
                $ofertas = Livro::ativo()
                    ->emEstoque()
                    ->with('categoria')
                    ->where('preco', '<', 50)
                    ->inRandomOrder()
                    ->limit(4)
                    ->get();
            }

            // 5. Estatísticas da loja
            $estatisticas = [
                'total_livros' => Livro::ativo()->count(),
                'total_categorias' => Categoria::ativo()->has('livros')->count(),
                'total_autores' => Livro::ativo()->distinct('autor')->count(),
                'livros_estoque' => Livro::ativo()->sum('estoque'),
            ];

            // 6. Catálogo completo (primeira página)
            $livros = Livro::ativo()
                ->emEstoque()
                ->with('categoria')
                ->orderBy('titulo')
                ->paginate(12);

            return view('loja.index', compact(
                'livrosDestaque',
                'livrosMaisVendidos',
                'livrosPorCategoria',
                'ofertas',
                'estatisticas',
                'livros'
            ));

        } catch (\Exception $e) {
            Log::error('Erro na página inicial da loja: ' . $e->getMessage());
            
            // Em caso de erro, retornar dados vazios
            return view('loja.index', [
                'livrosDestaque' => collect(),
                'livrosMaisVendidos' => collect(),
                'livrosPorCategoria' => collect(),
                'ofertas' => collect(),
                'estatisticas' => [
                    'total_livros' => 0,
                    'total_categorias' => 0,
                    'total_autores' => 0,
                    'livros_estoque' => 0,
                ],
                'livros' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12),
            ]);
        }
    }

    public function catalogo(Request $request)
    {
        $query = Livro::ativo()->with('categoria');
        
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
            if (is_numeric($request->categoria)) {
                $query->where('categoria_id', $request->categoria);
            } else {
                $query->whereHas('categoria', function($q) use ($request) {
                    $q->where('nome', 'like', "%{$request->categoria}%")
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
        
        // Filtro por promoções
        if ($request->filled('promocao')) {
            $query->promocao();
        }
        
        // Ordenação
        $orderBy = $request->get('ordem', 'titulo');
        $direction = $request->get('direcao', 'asc');
        
        switch ($orderBy) {
            case 'popularidade':
                $query->orderByDesc('vendas_total');
                break;
            case 'preco':
                $query->orderBy('preco', $direction);
                break;
            case 'lancamento':
                $query->latest();
                break;
            case 'avaliacao':
                $query->orderByDesc('avaliacao_media');
                break;
            default:
                $query->orderBy($orderBy, $direction);
                break;
        }
        
        $livros = $query->paginate(12)->withQueryString();
        
        // Dados para filtros
        $categorias = Categoria::ativo()
            ->whereHas('livros', function($query) {
                $query->where('ativo', true);
            })
            ->withCount(['livros' => function($query) {
                $query->where('ativo', true);
            }])
            ->orderBy('nome')
            ->get();
        
        return view('loja.catalogo', compact('livros', 'categorias'));
    }

    public function categoria($categoriaSlug)
    {
        // Buscar categoria pelo slug primeiro, depois por nome
        $categoria = Categoria::where('slug', $categoriaSlug)
                             ->orWhere('nome', $categoriaSlug)
                             ->where('ativo', true)
                             ->firstOrFail();
        
        $livros = Livro::where('categoria_id', $categoria->id)
            ->ativo()
            ->with('categoria')
            ->orderBy('titulo')
            ->paginate(12);

        $totalLivros = $livros->total();

        return view('loja.categoria', compact('livros', 'categoria', 'totalLivros'));
    }

    public function detalhes(Livro $livro)
    {
        // Verificar se o livro está ativo
        if (!$livro->ativo) {
            abort(404, 'Livro não encontrado ou não está disponível.');
        }

        // Carregar relacionamentos
        $livro->load(['categoria', 'avaliacoes.user']);

        // Livros relacionados (mesma categoria)
        $livrosRelacionados = collect();
        
        if ($livro->categoria_id) {
            $livrosRelacionados = Livro::where('categoria_id', $livro->categoria_id)
                ->where('id', '!=', $livro->id)
                ->ativo()
                ->emEstoque()
                ->with('categoria')
                ->orderByDesc('vendas_total')
                ->limit(4)
                ->get();
        }

        // Verificar se está nos favoritos do usuário
        $isFavorito = false;
        if (auth()->check()) {
            $isFavorito = auth()->user()->favorites()
                ->where('livro_id', $livro->id)
                ->exists();
        }

        // Incrementar visualizações (opcional)
        // $livro->increment('visualizacoes');

        return view('loja.detalhes', compact('livro', 'livrosRelacionados', 'isFavorito'));
    }

    public function favoritos()
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('message', 'Você precisa estar logado para ver seus favoritos.');
        }

        $favoritos = auth()->user()->favorites()
            ->ativo()
            ->with('categoria')
            ->orderBy('favorites.created_at', 'desc')
            ->paginate(12);

        return view('loja.favoritos', compact('favoritos'));
    }

    public function buscar(Request $request)
    {
        $termo = trim($request->get('q'));
        
        if (empty($termo)) {
            return redirect()->route('loja.catalogo')
                ->with('info', 'Digite um termo para buscar.');
        }

        if (strlen($termo) < 2) {
            return redirect()->route('loja.catalogo')
                ->with('info', 'Digite pelo menos 2 caracteres para buscar.');
        }

        $livros = Livro::ativo()
            ->with('categoria')
            ->where(function($query) use ($termo) {
                $query->where('titulo', 'like', "%{$termo}%")
                      ->orWhere('autor', 'like', "%{$termo}%")
                      ->orWhere('sinopse', 'like', "%{$termo}%")
                      ->orWhere('isbn', 'like', "%{$termo}%")
                      ->orWhereHas('categoria', function($q) use ($termo) {
                          $q->where('nome', 'like', "%{$termo}%");
                      });
            })
            ->orderByRaw("
                CASE 
                    WHEN titulo LIKE ? THEN 1
                    WHEN autor LIKE ? THEN 2
                    WHEN sinopse LIKE ? THEN 3
                    ELSE 4
                END
            ", ["%{$termo}%", "%{$termo}%", "%{$termo}%"])
            ->paginate(12);

        // Adicionar termo à query string para paginação
        $livros->appends(['q' => $termo]);

        return view('loja.busca', compact('livros', 'termo'));
    }

    // Método para busca AJAX (opcional)
    public function buscaAjax(Request $request)
    {
        $termo = $request->get('q');
        
        if (strlen($termo) < 2) {
            return response()->json([]);
        }

        $livros = Livro::ativo()
            ->emEstoque()
            ->select('id', 'titulo', 'autor', 'preco', 'imagem')
            ->where(function($query) use ($termo) {
                $query->where('titulo', 'like', "%{$termo}%")
                      ->orWhere('autor', 'like', "%{$termo}%");
            })
            ->limit(8)
            ->get();

        return response()->json($livros);
    }
}