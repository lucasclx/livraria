<?php
// app/Http/Controllers/LivroController.php - VERSÃO MELHORADA

namespace App\Http\Controllers;

use App\Models\Livro;
use App\Http\Requests\LivroRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class LivroController extends Controller
{
    public function index(Request $request)
    {
        $query = Livro::query();
        
        // Busca por termo
        if ($request->filled('busca')) {
            $query->buscar($request->busca);
        }
        
        // Filtro por categoria
        if ($request->filled('categoria')) {
            $query->porCategoria($request->categoria);
        }
        
        // Filtro por gênero
        if ($request->filled('genero')) {
            $query->porGenero($request->genero);
        }
        
        // Filtro por autor
        if ($request->filled('autor')) {
            $query->porAutor($request->autor);
        }
        
        // Filtro por editora
        if ($request->filled('editora')) {
            $query->porEditora($request->editora);
        }
        
        // Filtro por idioma
        if ($request->filled('idioma')) {
            $query->where('idioma', $request->idioma);
        }
        
        // Filtro por faixa de preço
        if ($request->filled('preco_min') || $request->filled('preco_max')) {
            $query->faixaPreco($request->preco_min, $request->preco_max);
        }
        
        // Filtro por ano de publicação
        if ($request->filled('ano_inicio') || $request->filled('ano_fim')) {
            $query->porAno($request->ano_inicio, $request->ano_fim);
        }
        
        // Filtro por status do estoque
        if ($request->filled('estoque')) {
            switch ($request->estoque) {
                case 'disponivel':
                    $query->emEstoque();
                    break;
                case 'baixo':
                    $query->estoqueBaixo();
                    break;
                case 'sem_estoque':
                    $query->where('estoque', 0);
                    break;
            }
        }
        
        // Filtro livros em destaque
        if ($request->filled('destaque')) {
            $query->destaque();
        }
        
        // Filtro livros com desconto
        if ($request->filled('promocao')) {
            $query->comDesconto();
        }
        
        // Apenas livros ativos
        $query->ativo();
        
        // Ordenação
        $orderBy = $request->get('ordem', 'titulo');
        $direction = $request->get('direcao', 'asc');
        
        switch ($orderBy) {
            case 'preco':
                $query->orderBy('preco', $direction);
                break;
            case 'lancamento':
                $query->orderBy('created_at', 'desc');
                break;
            case 'popularidade':
                $query->orderBy('total_vendas', 'desc')
                      ->orderBy('visualizacoes', 'desc');
                break;
            case 'avaliacao':
                $query->orderBy('avaliacao_media', 'desc')
                      ->orderBy('total_avaliacoes', 'desc');
                break;
            case 'ano':
                $query->orderBy('ano_publicacao', $direction);
                break;
            case 'autor':
                $query->orderBy('autor', $direction);
                break;
            default:
                $query->orderBy('titulo', $direction);
        }
        
        $livros = $query->paginate(12)->withQueryString();
        
        // Dados para filtros
        $categorias = Livro::ativo()
                          ->select('categoria')
                          ->whereNotNull('categoria')
                          ->distinct()
                          ->orderBy('categoria')
                          ->pluck('categoria');
        
        $generos = Livro::ativo()
                       ->select('genero')
                       ->whereNotNull('genero')
                       ->distinct()
                       ->orderBy('genero')
                       ->pluck('genero');
        
        $autores = Livro::ativo()
                       ->select('autor')
                       ->groupBy('autor')
                       ->havingRaw('COUNT(*) > 1') // Apenas autores com mais de 1 livro
                       ->orderBy('autor')
                       ->pluck('autor');
        
        $editoras = Livro::ativo()
                        ->select('editora')
                        ->whereNotNull('editora')
                        ->groupBy('editora')
                        ->havingRaw('COUNT(*) > 1')
                        ->orderBy('editora')
                        ->pluck('editora');
        
        return view('livros.index', compact(
            'livros', 'categorias', 'generos', 'autores', 'editoras'
        ));
    }

    public function show(Livro $livro)
    {
        // Incrementar visualizações
        $livro->incrementarVisualizacoes();
        
        // Livros similares
        $similares = $livro->livrosSimilares();
        
        // Livros do mesmo autor
        $mesmoAutor = Livro::ativo()
                          ->emEstoque()
                          ->where('autor', $livro->autor)
                          ->where('id', '!=', $livro->id)
                          ->limit(3)
                          ->get();
        
        return view('livros.show', compact('livro', 'similares', 'mesmoAutor'));
    }

    public function create()
    {
        // Dados para formulário
        $categorias = Livro::select('categoria')
                          ->whereNotNull('categoria')
                          ->distinct()
                          ->orderBy('categoria')
                          ->pluck('categoria');
        
        $editoras = Livro::select('editora')
                        ->whereNotNull('editora')
                        ->distinct()
                        ->orderBy('editora')
                        ->pluck('editora');

        $generos = [
            'ficcao' => 'Ficção',
            'nao_ficcao' => 'Não-ficção',
            'romance' => 'Romance',
            'fantasia' => 'Fantasia',
            'misterio' => 'Mistério',
            'biografia' => 'Biografia',
            'historia' => 'História',
            'ciencia' => 'Ciência',
            'tecnologia' => 'Tecnologia',
            'autoajuda' => 'Autoajuda',
            'infantil' => 'Infantil',
            'jovem_adulto' => 'Jovem Adulto',
            'academico' => 'Acadêmico'
        ];

        return view('livros.create', compact('categorias', 'editoras', 'generos'));
    }

    public function update(LivroRequest $request, Livro $livro)
    {
        $data = $request->validated();

        // Upload da nova imagem
        if ($request->hasFile('imagem')) {
            try {
                // Remover imagem antiga
                if ($livro->imagem && Storage::disk('public')->exists('livros/' . $livro->imagem)) {
                    Storage::disk('public')->delete('livros/' . $livro->imagem);
                    Log::info('Imagem antiga removida: ' . $livro->imagem);
                }

                $imagem = $request->file('imagem');
                $nomeImagem = Str::random(20) . '.' . $imagem->getClientOriginalExtension();
                
                $pastaDestino = storage_path('app/public/livros');
                if (!file_exists($pastaDestino)) {
                    mkdir($pastaDestino, 0755, true);
                }
                
                $path = $imagem->storeAs('livros', $nomeImagem, 'public');
                $data['imagem'] = $nomeImagem;
                
                Log::info('Imagem atualizada com sucesso', [
                    'arquivo' => $nomeImagem,
                    'caminho' => storage_path('app/public/livros/' . $nomeImagem)
                ]);
                
            } catch (\Exception $e) {
                Log::error('Erro ao atualizar imagem: ' . $e->getMessage());
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Erro ao atualizar a imagem. Tente novamente.');
            }
        }

        // Processar tags
        if ($request->filled('tags_input')) {
            $tags = array_map('trim', explode(',', $request->tags_input));
            $data['tags'] = array_filter($tags);
        }

        try {
            $livro->update($data);
            return redirect()->route('livros.index')
                ->with('success', 'Livro atualizado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar livro: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar o livro. Tente novamente.');
        }
    }

    public function destroy(Livro $livro)
    {
        try {
            // Verificar se o livro está em algum carrinho ou pedido
            if ($livro->cartItems()->count() > 0) {
                return redirect()->route('livros.index')
                    ->with('error', 'Não é possível excluir este livro pois ele está em carrinhos de compras.');
            }

            $livro->delete();
            
            return redirect()->route('livros.index')
                ->with('success', 'Livro removido com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir livro: ' . $e->getMessage());
            return redirect()->route('livros.index')
                ->with('error', 'Erro ao excluir livro: ' . $e->getMessage());
        }
    }

    // Métodos especiais para livraria
    public function destaques()
    {
        $livros = Livro::ativo()
                      ->destaque()
                      ->emEstoque()
                      ->orderBy('avaliacao_media', 'desc')
                      ->orderBy('total_vendas', 'desc')
                      ->paginate(12);
        
        return view('livros.destaques', compact('livros'));
    }

    public function promocoes()
    {
        $livros = Livro::ativo()
                      ->comDesconto()
                      ->emEstoque()
                      ->orderBy('desconto_percentual', 'desc')
                      ->paginate(12);
        
        return view('livros.promocoes', compact('livros'));
    }

    public function lancamentos()
    {
        $livros = Livro::ativo()
                      ->lancamentos(60) // Últimos 60 dias
                      ->emEstoque()
                      ->orderBy('created_at', 'desc')
                      ->paginate(12);
        
        return view('livros.lancamentos', compact('livros'));
    }

    public function maisVendidos()
    {
        $livros = Livro::ativo()
                      ->emEstoque()
                      ->orderBy('total_vendas', 'desc')
                      ->orderBy('avaliacao_media', 'desc')
                      ->paginate(12);
        
        return view('livros.mais-vendidos', compact('livros'));
    }

    public function categoria($categoria)
    {
        $livros = Livro::ativo()
                      ->porCategoria($categoria)
                      ->orderBy('titulo')
                      ->paginate(12);
        
        return view('livros.categoria', compact('livros', 'categoria'));
    }

    public function autor($autor)
    {
        $livros = Livro::ativo()
                      ->porAutor($autor)
                      ->orderBy('ano_publicacao', 'desc')
                      ->paginate(12);
        
        // Informações do autor
        $infoAutor = [
            'nome' => $autor,
            'total_livros' => $livros->total(),
            'preco_medio' => Livro::ativo()->porAutor($autor)->avg('preco'),
            'livros_em_estoque' => Livro::ativo()->porAutor($autor)->emEstoque()->count(),
        ];
        
        return view('livros.autor', compact('livros', 'autor', 'infoAutor'));
    }

    public function editora($editora)
    {
        $livros = Livro::ativo()
                      ->porEditora($editora)
                      ->orderBy('ano_publicacao', 'desc')
                      ->paginate(12);
        
        return view('livros.editora', compact('livros', 'editora'));
    }

    // Dashboard e estatísticas
    public function dashboard()
    {
        $stats = [
            'total_livros' => Livro::count(),
            'livros_ativos' => Livro::ativo()->count(),
            'total_estoque' => Livro::sum('estoque'),
            'estoque_baixo' => Livro::estoqueBaixo()->count(),
            'sem_estoque' => Livro::where('estoque', 0)->count(),
            'valor_total_estoque' => Livro::selectRaw('SUM(preco * estoque) as total')->value('total'),
            'livros_destaque' => Livro::destaque()->count(),
            'livros_promocao' => Livro::comDesconto()->count(),
            'media_preco' => Livro::ativo()->avg('preco'),
            'livro_mais_caro' => Livro::ativo()->orderBy('preco', 'desc')->first(),
            'livro_mais_barato' => Livro::ativo()->orderBy('preco', 'asc')->first(),
        ];

        // Top categorias
        $topCategorias = Livro::topCategorias(5);
        
        // Top autores
        $topAutores = Livro::topAutores(5);
        
        // Top editoras
        $topEditoras = Livro::topEditoras(5);
        
        // Livros mais vendidos
        $maisVendidos = Livro::ativo()->orderBy('total_vendas', 'desc')->limit(5)->get();
        
        // Livros com estoque baixo
        $estoqueBaixo = Livro::ativo()->estoqueBaixo()->orderBy('estoque')->limit(10)->get();
        
        // Últimos livros adicionados
        $ultimosLivros = Livro::latest()->limit(5)->get();

        return view('livros.dashboard', compact(
            'stats', 'topCategorias', 'topAutores', 'topEditoras', 
            'maisVendidos', 'estoqueBaixo', 'ultimosLivros'
        ));
    }

    // AJAX para busca
    public function buscarAjax(Request $request)
    {
        $termo = $request->get('q');
        
        if (strlen($termo) < 2) {
            return response()->json([]);
        }
        
        $livros = Livro::ativo()
                      ->buscar($termo)
                      ->limit(10)
                      ->get(['id', 'titulo', 'autor', 'preco', 'imagem'])
                      ->map(function ($livro) {
                          return [
                              'id' => $livro->id,
                              'titulo' => $livro->titulo,
                              'autor' => $livro->autor,
                              'preco' => $livro->preco_formatado,
                              'imagem' => $livro->imagem_url,
                              'url' => route('livros.show', $livro)
                          ];
                      });
        
        return response()->json($livros);
    }

    // Gerar relatório
    public function relatorio(Request $request)
    {
        $tipo = $request->get('tipo', 'vendas');
        $periodo = $request->get('periodo', '30');
        
        $data = [];
        
        switch ($tipo) {
            case 'vendas':
                $data['titulo'] = 'Relatório de Vendas';
                $data['livros'] = Livro::ativo()
                                      ->orderBy('total_vendas', 'desc')
                                      ->limit(20)
                                      ->get();
                break;
                
            case 'estoque':
                $data['titulo'] = 'Relatório de Estoque';
                $data['livros'] = Livro::ativo()
                                      ->orderBy('estoque', 'asc')
                                      ->get();
                break;
                
            case 'categoria':
                $data['titulo'] = 'Relatório por Categoria';
                $data['categorias'] = Livro::topCategorias();
                break;
        }
        
        return view('livros.relatorio', compact('data', 'tipo'));
    }

    // Importar livros via CSV
    public function importar(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|mimes:csv,txt|max:2048'
        ]);

        try {
            $arquivo = $request->file('arquivo');
            $conteudo = file_get_contents($arquivo->path());
            $linhas = array_map('str_getcsv', explode("\n", $conteudo));
            
            $header = array_shift($linhas); // Remove cabeçalho
            $importados = 0;
            $erros = [];
            
            foreach ($linhas as $index => $linha) {
                if (empty(array_filter($linha))) continue; // Pula linhas vazias
                
                try {
                    $dadosLivro = array_combine($header, $linha);
                    
                    // Validações básicas
                    if (empty($dadosLivro['titulo']) || empty($dadosLivro['autor'])) {
                        $erros[] = "Linha " . ($index + 2) . ": título e autor são obrigatórios";
                        continue;
                    }
                    
                    // Criar livro
                    Livro::create([
                        'titulo' => $dadosLivro['titulo'],
                        'autor' => $dadosLivro['autor'],
                        'isbn' => $dadosLivro['isbn'] ?? null,
                        'editora' => $dadosLivro['editora'] ?? null,
                        'ano_publicacao' => $dadosLivro['ano_publicacao'] ?? null,
                        'preco' => floatval($dadosLivro['preco'] ?? 0),
                        'paginas' => intval($dadosLivro['paginas'] ?? 0),
                        'categoria' => $dadosLivro['categoria'] ?? null,
                        'estoque' => intval($dadosLivro['estoque'] ?? 0),
                        'sinopse' => $dadosLivro['sinopse'] ?? null,
                        'ativo' => true
                    ]);
                    
                    $importados++;
                    
                } catch (\Exception $e) {
                    $erros[] = "Linha " . ($index + 2) . ": " . $e->getMessage();
                }
            }
            
            $mensagem = "Importação concluída! {$importados} livros importados.";
            if (count($erros) > 0) {
                $mensagem .= " " . count($erros) . " erros encontrados.";
            }
            
            return redirect()->route('livros.index')
                ->with('success', $mensagem)
                ->with('import_errors', $erros);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao importar arquivo: ' . $e->getMessage());
        }
    }
}',
            'misterio' => 'Mistério',
            'biografia' => 'Biografia',
            'historia' => 'História',
            'ciencia' => 'Ciência',
            'tecnologia' => 'Tecnologia',
            'autoajuda' => 'Autoajuda',
            'infantil' => 'Infantil',
            'jovem_adulto' => 'Jovem Adulto',
            'academico' => 'Acadêmico'
        ];
        
        return view('livros.create', compact('categorias', 'editoras', 'generos'));
    }

    public function store(LivroRequest $request)
    {
        $data = $request->validated();

        // Upload da imagem
        if ($request->hasFile('imagem')) {
            try {
                $imagem = $request->file('imagem');
                $nomeImagem = Str::random(20) . '.' . $imagem->getClientOriginalExtension();
                
                $pastaDestino = storage_path('app/public/livros');
                if (!file_exists($pastaDestino)) {
                    mkdir($pastaDestino, 0755, true);
                }
                
                $path = $imagem->storeAs('livros', $nomeImagem, 'public');
                $data['imagem'] = $nomeImagem;
                
                Log::info('Imagem do livro salva', [
                    'arquivo' => $nomeImagem,
                    'caminho' => storage_path('app/public/livros/' . $nomeImagem)
                ]);
                
            } catch (\Exception $e) {
                Log::error('Erro ao salvar imagem: ' . $e->getMessage());
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Erro ao salvar a imagem. Tente novamente.');
            }
        }

        // Processar tags
        if ($request->filled('tags_input')) {
            $tags = array_map('trim', explode(',', $request->tags_input));
            $data['tags'] = array_filter($tags);
        }

        try {
            Livro::create($data);
            return redirect()->route('livros.index')
                ->with('success', 'Livro cadastrado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar livro: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar o livro. Tente novamente.');
        }
    }

    public function edit(Livro $livro)
    {
        // Dados para formulário
        $categorias = Livro::select('categoria')
                          ->whereNotNull('categoria')
                          ->distinct()
                          ->orderBy('categoria')
                          ->pluck('categoria');
        
        $editoras = Livro::select('editora')
                        ->whereNotNull('editora')
                        ->distinct()
                        ->orderBy('editora')
                        ->pluck('editora');
        
        $generos = [
            'ficcao' => 'Ficção',
            'nao_ficcao' => 'Não-ficção',
            'romance' => 'Romance',
            'fantasia' => 'Fantasia