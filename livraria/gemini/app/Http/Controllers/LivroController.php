<?php

namespace App\Http\Controllers;

use App\Http\Requests\LivroRequest;
use App\Models\Livro;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        
        // Filtros
        if ($request->filled('busca')) {
            $termo = $request->busca;
            $query->where(function($q) use ($termo) {
                $q->where('titulo', 'like', "%{$termo}%")
                  ->orWhere('autor', 'like', "%{$termo}%")
                  ->orWhere('isbn', 'like', "%{$termo}%");
            });
        }
        
        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }
        
        if ($request->filled('estoque')) {
            if ($request->estoque === 'baixo') {
                $query->estoqueBaixo();
            } elseif ($request->estoque === 'sem_estoque') {
                $query->where('estoque', 0);
            }
        }
        
        $livros = $query->orderBy('titulo')->paginate(15);
        $categorias = Categoria::orderBy('nome')->get();
        
        return view('livros.index', compact('livros', 'categorias'));
    }

    public function create()
    {
        $this->checkAdmin();
        
        $categorias = Categoria::where('ativo', true)->orderBy('nome')->get();
        // Correção: Buscar editoras distintas para o datalist
        $editoras = Livro::select('editora')->distinct()->whereNotNull('editora')->orderBy('editora')->pluck('editora');
        return view('livros.create', compact('categorias', 'editoras'));
    }

    public function store(Request $request)
    {
        $this->checkAdmin();
        
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'autor' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20|unique:livros,isbn',
            'editora' => 'nullable|string|max:255',
            'ano_publicacao' => 'nullable|integer|min:1000|max:' . date('Y'),
            'preco' => 'required|numeric|min:0.01',
            'preco_promocional' => 'nullable|numeric|min:0.01|lt:preco',
            'paginas' => 'nullable|integer|min:1',
            'sinopse' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'estoque' => 'required|integer|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
            'peso' => 'nullable|numeric|min:0.01',
            'idioma' => 'nullable|string|max:50',
            'edicao' => 'nullable|string|max:50',
            'encadernacao' => 'nullable|string|max:50',
            'ativo' => 'boolean',
            'destaque' => 'boolean',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('imagem')) {
            $imagem = $request->file('imagem');
            $nomeImagem = Str::random(20) . '.' . $imagem->getClientOriginalExtension();
            $imagem->storeAs('public/livros', $nomeImagem);
            $data['imagem'] = $nomeImagem;
        }

        $data['ativo'] = $request->boolean('ativo', true);
        $data['destaque'] = $request->boolean('destaque');

        Livro::create($data);

        return redirect()->route('livros.index')
            ->with('success', 'Livro criado com sucesso!');
    }

    public function show(Livro $livro)
    {
        $this->checkAdmin();
        
        return view('livros.show', compact('livro'));
    }

    public function edit(Livro $livro)
    {
        $this->checkAdmin();
        
        $categorias = Categoria::where('ativo', true)->orderBy('nome')->get();
        // Correção: Buscar editoras distintas para o datalist no método edit
        $editoras = Livro::select('editora')->distinct()->whereNotNull('editora')->orderBy('editora')->pluck('editora');
        return view('livros.edit', compact('livro', 'categorias', 'editoras'));
    }

    public function update(Request $request, Livro $livro)
    {
        $this->checkAdmin();
        
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'autor' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20|unique:livros,isbn,' . $livro->id,
            'editora' => 'nullable|string|max:255',
            'ano_publicacao' => 'nullable|integer|min:1000|max:' . date('Y'),
            'preco' => 'required|numeric|min:0.01',
            'preco_promocional' => 'nullable|numeric|min:0.01|lt:preco',
            'paginas' => 'nullable|integer|min:1',
            'sinopse' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'estoque' => 'required|integer|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
            'peso' => 'nullable|numeric|min:0.01',
            'idioma' => 'nullable|string|max:50',
            'edicao' => 'nullable|string|max:50',
            'encadernacao' => 'nullable|string|max:50',
            'ativo' => 'boolean',
            'destaque' => 'boolean',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('imagem')) {
            // Remove imagem antiga
            if ($livro->imagem) {
                Storage::delete('public/livros/' . $livro->imagem);
            }

            $imagem = $request->file('imagem');
            $nomeImagem = Str::random(20) . '.' . $imagem->getClientOriginalExtension();
            $imagem->storeAs('public/livros', $nomeImagem);
            $data['imagem'] = $nomeImagem;
        }

        $data['ativo'] = $request->boolean('ativo');
        $data['destaque'] = $request->boolean('destaque');

        $livro->update($data);

        return redirect()->route('livros.index')
            ->with('success', 'Livro atualizado com sucesso!');
    }

    public function destroy(Livro $livro)
    {
        $this->checkAdmin();
        
        try {
            $livro->delete();
            return redirect()->route('livros.index')
                ->with('success', 'Livro excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('livros.index')
                ->with('error', 'Erro ao excluir livro: ' . $e->getMessage());
        }
    }

    public function confirmDelete(Livro $livro)
    {
        $this->checkAdmin();
        
        return view('livros.delete', compact('livro'));
    }
}