<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Requests\CategoriaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoriaController extends Controller
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

    public function index()
    {
        $this->checkAdmin();
        
        $categorias = Categoria::orderBy('nome')->paginate(10);
        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        $this->checkAdmin();
        
        return view('categorias.create');
    }

    public function store($request)
    {
        $this->checkAdmin();
        
        // Se não é uma instância de CategoriaRequest, validar manualmente
        if (!$request instanceof \App\Http\Requests\CategoriaRequest) {
            $data = $request->validate([
                'nome' => 'required|string|min:3|max:100',
                'descricao' => 'nullable|string|max:500',
                'ativo' => 'boolean',
                'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } else {
            $data = $request->validated();
        }

        if ($request->hasFile('imagem')) {
            $imagem = $request->file('imagem');
            $nomeImagem = Str::random(20) . '.' . $imagem->getClientOriginalExtension();
            $imagem->storeAs('public/categorias', $nomeImagem);
            $data['imagem'] = $nomeImagem;
        }

        // Converter checkbox
        $data['ativo'] = $request->has('ativo') ? true : false;

        Categoria::create($data);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    public function show(Categoria $categoria)
    {
        $this->checkAdmin();
        
        return view('categorias.show', compact('categoria'));
    }

    public function edit(Categoria $categoria)
    {
        $this->checkAdmin();
        
        return view('categorias.edit', compact('categoria'));
    }

    public function update($request, Categoria $categoria)
    {
        $this->checkAdmin();
        
        // Se não é uma instância de CategoriaRequest, validar manualmente
        if (!$request instanceof \App\Http\Requests\CategoriaRequest) {
            $data = $request->validate([
                'nome' => 'required|string|min:3|max:100',
                'descricao' => 'nullable|string|max:500',
                'ativo' => 'boolean',
                'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } else {
            $data = $request->validated();
        }

        if ($request->hasFile('imagem')) {
            // Remove imagem antiga
            if ($categoria->imagem) {
                Storage::delete('public/categorias/' . $categoria->imagem);
            }

            $imagem = $request->file('imagem');
            $nomeImagem = Str::random(20) . '.' . $imagem->getClientOriginalExtension();
            $imagem->storeAs('public/categorias', $nomeImagem);
            $data['imagem'] = $nomeImagem;
        }

        // Converter checkbox
        $data['ativo'] = $request->has('ativo') ? true : false;

        $categoria->update($data);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(Categoria $categoria)
    {
        $this->checkAdmin();
        
        try {
            // Verifica se há livros vinculados
            if ($categoria->livros()->count() > 0) {
                return redirect()->route('categorias.index')
                    ->with('error', 'Não é possível excluir a categoria pois existem livros vinculados a ela.');
            }

            $categoria->delete();

            return redirect()->route('categorias.index')
                ->with('success', 'Categoria excluída com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('categorias.index')
                ->with('error', 'Erro ao excluir categoria: ' . $e->getMessage());
        }
    }

    public function confirmDelete(Categoria $categoria)
    {
        $this->checkAdmin();
        
        return view('categorias.delete', compact('categoria'));
    }
}