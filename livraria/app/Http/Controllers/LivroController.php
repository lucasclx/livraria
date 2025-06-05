<?php

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
        $categorias = Livro::select('categoria')
                          ->whereNotNull('categoria')
                          ->distinct()
                          ->pluck('categoria');
        
        return view('livros.index', compact('livros', 'categorias'));
    }

    public function create()
    {
        return view('livros.create');
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
                
                // Salvar sempre no disk 'public'
                $path = $imagem->storeAs('livros', $nomeImagem, 'public');
                $data['imagem'] = $nomeImagem;
                
                // Log de sucesso
                Log::info('Imagem salva com sucesso', [
                    'arquivo' => $nomeImagem,
                    'caminho' => storage_path('app/public/livros/' . $nomeImagem),
                    'url' => url('storage/livros/' . $nomeImagem)
                ]);
                
            } catch (\Exception $e) {
                Log::error('Erro ao salvar imagem: ' . $e->getMessage());
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Erro ao salvar a imagem. Tente novamente.');
            }
        }

        // Definir status ativo como true por padrão
        $data['ativo'] = $data['ativo'] ?? true;

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
        return view('livros.show', compact('livro'));
    }

    public function edit(Livro $livro)
    {
        return view('livros.edit', compact('livro'));
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
                    Log::info('Imagem antiga removida: ' . $livro->imagem);
                }

                $imagem = $request->file('imagem');
                $nomeImagem = Str::random(20) . '.' . $imagem->getClientOriginalExtension();
                
                // Garantir que a pasta existe
                $pastaDestino = storage_path('app/public/livros');
                if (!file_exists($pastaDestino)) {
                    mkdir($pastaDestino, 0755, true);
                }
                
                // Salvar sempre no disk 'public'
                $path = $imagem->storeAs('livros', $nomeImagem, 'public');
                $data['imagem'] = $nomeImagem;
                
                // Log de sucesso
                Log::info('Imagem atualizada com sucesso', [
                    'arquivo' => $nomeImagem,
                    'caminho' => storage_path('app/public/livros/' . $nomeImagem),
                    'url' => url('storage/livros/' . $nomeImagem)
                ]);
                
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
            // Delete associated image if exists
            if ($livro->imagem && Storage::disk('public')->exists('livros/' . $livro->imagem)) {
                Storage::disk('public')->delete('livros/' . $livro->imagem);
                Log::info('Imagem removida: ' . $livro->imagem);
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
}