<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Requests\CategoriaRequest; // Certifique-se que esta linha está presente
use Illuminate\Http\Request;
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

    public function index(Request $request)
    {
        $this->checkAdmin();

        $query = Categoria::query();

        if ($request->filled('busca')) {
            $query->where('nome', 'like', '%' . $request->busca . '%');
        }

        if ($request->filled('status')) {
            if ($request->status == 'ativo') {
                $query->where('ativo', true);
            } elseif ($request->status == 'inativo') {
                $query->where('ativo', false);
            }
        }

        $categorias = $query->orderBy('nome')->paginate(10);
        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        $this->checkAdmin();
        return view('categorias.create');
    }

    public function store(CategoriaRequest $request)
    {
        $this->checkAdmin();
        $data = $request->validated();
        
        $data['slug'] = Str::slug($data['nome']);
        // O campo 'ativo' já é tratado pela validação da CategoriaRequest e pelo helper request->boolean()
        $data['ativo'] = $request->boolean('ativo', true); // Adicionado default true se não presente

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

    public function update(CategoriaRequest $request, Categoria $categoria)
    {
        $this->checkAdmin();
        $data = $request->validated();
        
        $data['slug'] = Str::slug($data['nome']);
        // O campo 'ativo' já é tratado pela validação da CategoriaRequest e pelo helper request->boolean()
        $data['ativo'] = $request->boolean('ativo'); // Sem default para update, pois o valor atual deve ser respeitado

        $categoria->update($data);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(Categoria $categoria)
    {
        $this->checkAdmin();
        try {
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