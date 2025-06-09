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
    public function index(Request $request)
    {
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

    public function store(LivroRequest $request)
    {
        $data = $request->validated();

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
        $livro->load('categoria', 'avaliacoes.user', 'stockMovements');
        return view('livros.show', compact('livro'));
    }

    public function edit(Livro $livro)
    {
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

    public function update(LivroRequest $request, Livro $livro)
    {
        $data = $request->validated();

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

    // API Methods
    public function searchApi(Request $request)
    {
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