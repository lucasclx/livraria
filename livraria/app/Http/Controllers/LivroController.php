<?php

namespace App\Http\Controllers;

use App\Models\Livro;
use App\Models\Categoria;
use App\Http\Requests\LivroRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class LivroController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function checkAdmin()
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Acesso negado. Apenas administradores podem acessar esta área.');
        }
    }

    public function index(Request $request)
    {
        $this->checkAdmin();
        
        $query = Livro::with('categoria');
        
        // Busca por termo
        if ($request->filled('busca')) {
            $query->buscar($request->busca);
        }
        
        // Filtro por categoria
        if ($request->filled('categoria')) {
            $query->porCategoria($request->categoria);
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
        
        // Ordenação
        $orderBy = $request->get('ordem', 'titulo');
        $direction = $request->get('direcao', 'asc');
        
        $query->orderBy($orderBy, $direction);
        
        $livros = $query->paginate(12)->withQueryString();
        
        // Dados para filtros
        $categorias = Categoria::where('ativo', true)
                              ->orderBy('nome')
                              ->get();
        
        return view('livros.index', compact('livros', 'categorias'));
    }

    public function create()
    {
        $this->checkAdmin();
        
        // Dados para formulário
        $categorias = Categoria::where('ativo', true)
                              ->orderBy('nome')
                              ->get();
        
        $editoras = Livro::whereNotNull('editora')
                        ->distinct()
                        ->orderBy('editora')
                        ->pluck('editora');

        return view('livros.create', compact('categorias', 'editoras'));
    }

    public function store(Request $request)
    {
        $this->checkAdmin();
        
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'autor' => 'required|string|max:100',
            'isbn' => 'nullable|string|max:20|unique:livros',
            'preco' => 'required|numeric|min:0.01|max:99999.99',
            'preco_promocional' => 'nullable|numeric|min:0.01|max:99999.99|lt:preco',
            'editora' => 'nullable|string|max:100',
            'ano_publicacao' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'paginas' => 'nullable|integer|min:1|max:99999',
            'categoria_id' => 'nullable|exists:categorias,id',
            'estoque' => 'required|integer|min:0|max:99999',
            'estoque_minimo' => 'nullable|integer|min:0|max:9999',
            'peso' => 'nullable|numeric|min:0.001|max:99.999',
            'dimensoes' => 'nullable|string|max:50',
            'idioma' => 'nullable|string|max:50',
            'edicao' => 'nullable|string|max:50',
            'encadernacao' => 'nullable|string|max:50',
            'sinopse' => 'nullable|string|max:2000',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'ativo' => 'boolean',
            'destaque' => 'boolean',
            'promocao_inicio' => 'nullable|date|before_or_equal:promocao_fim',
            'promocao_fim' => 'nullable|date|after_or_equal:promocao_inicio',
        ]);

        // Handle image upload
        if ($request->hasFile('imagem')) {
            try {
                $imagem = $request->file('imagem');
                $nomeImagem = Str::random(20) . '.' . $imagem->getClientOriginalExtension();
                
                // Garantir que a pasta existe
                $pastaDestino = storage_path('app/public/livros');
                if (!file_exists($pastaDestino)) {
                    mkdir($pastaDestino, 0755, true);
                }
                
                $imagem->storeAs('livros', $nomeImagem, 'public');
                $data['imagem'] = $nomeImagem;
                
            } catch (\Exception $e) {
                Log::error('Erro ao salvar imagem: ' . $e->getMessage());
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Erro ao salvar a imagem. Tente novamente.');
            }
        }

        // Converter checkbox values
        $data['ativo'] = $request->has('ativo') ? true : false;
        $data['destaque'] = $request->has('destaque') ? true : false;

        // Definir valores padrão se não informados
        $data['estoque_minimo'] = $data['estoque_minimo'] ?? 5;
        $data['peso'] = $data['peso'] ?? 0.5;
        $data['idioma'] = $data['idioma'] ?? 'Português';

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

    public function show(Livro $livro)
    {
        $this->checkAdmin();
        
        $livro->load('categoria', 'avaliacoes.user', 'stockMovements');
        return view('livros.show', compact('livro'));
    }

    public function edit(Livro $livro)
    {
        $this->checkAdmin();
        
        // Dados para formulário
        $categorias = Categoria::where('ativo', true)
                              ->orderBy('nome')
                              ->get();
        
        $editoras = Livro::whereNotNull('editora')
                        ->distinct()
                        ->orderBy('editora')
                        ->pluck('editora');

        return view('livros.edit', compact('livro', 'categorias', 'editoras'));
    }

    public function update(Request $request, Livro $livro)
    {
        $this->checkAdmin();
        
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'autor' => 'required|string|max:100',
            'isbn' => 'nullable|string|max:20|unique:livros,isbn,' . $livro->id,
            'preco' => 'required|numeric|min:0.01|max:99999.99',
            'preco_promocional' => 'nullable|numeric|min:0.01|max:99999.99|lt:preco',
            'editora' => 'nullable|string|max:100',
            'ano_publicacao' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'paginas' => 'nullable|integer|min:1|max:99999',
            'categoria_id' => 'nullable|exists:categorias,id',
            'estoque' => 'required|integer|min:0|max:99999',
            'estoque_minimo' => 'nullable|integer|min:0|max:9999',
            'peso' => 'nullable|numeric|min:0.001|max:99.999',
            'dimensoes' => 'nullable|string|max:50',
            'idioma' => 'nullable|string|max:50',
            'edicao' => 'nullable|string|max:50',
            'encadernacao' => 'nullable|string|max:50',
            'sinopse' => 'nullable|string|max:2000',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'ativo' => 'boolean',
            'destaque' => 'boolean',
            'promocao_inicio' => 'nullable|date|before_or_equal:promocao_fim',
            'promocao_fim' => 'nullable|date|after_or_equal:promocao_inicio',
        ]);

        // Handle image upload
        if ($request->hasFile('imagem')) {
            try {
                // Delete old image if exists
                if ($livro->imagem && Storage::disk('public')->exists('livros/' . $livro->imagem)) {
                    Storage::disk('public')->delete('livros/' . $livro->imagem);
                }

                $imagem = $request->file('imagem');
                $nomeImagem = Str::random(20) . '.' . $imagem->getClientOriginalExtension();
                
                $imagem->storeAs('livros', $nomeImagem, 'public');
                $data['imagem'] = $nomeImagem;
                
            } catch (\Exception $e) {
                Log::error('Erro ao atualizar imagem: ' . $e->getMessage());
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Erro ao atualizar a imagem. Tente novamente.');
            }
        }

        // Converter checkbox values
        $data['ativo'] = $request->has('ativo') ? true : false;
        $data['destaque'] = $request->has('destaque') ? true : false;

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
        $this->checkAdmin();
        
        try {
            // Verificar se há itens em carrinho ou pedidos
            if ($livro->cartItems()->count() > 0) {
                return redirect()->route('livros.index')
                    ->with('error', 'Não é possível excluir este livro pois há itens em carrinhos de compras.');
            }

            // Delete associated image if exists
            if ($livro->imagem && Storage::disk('public')->exists('livros/' . $livro->imagem)) {
                Storage::disk('public')->delete('livros/' . $livro->imagem);
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

    public function confirmDelete(Livro $livro)
    {
        $this->checkAdmin();
        
        return view('livros.delete', compact('livro'));
    }

    // API Methods
    public function searchApi(Request $request)
    {
        $this->checkAdmin();
        
        $termo = $request->get('q');
        
        if (strlen($termo) < 2) {
            return response()->json([]);
        }

        $livros = Livro::buscar($termo)
                      ->ativo()
                      ->with('categoria')
                      ->limit(10)
                      ->get(['id', 'titulo', 'autor', 'categoria_id', 'preco', 'imagem']);

        return response()->json($livros);
    }
}