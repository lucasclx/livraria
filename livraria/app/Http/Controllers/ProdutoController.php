<?php
// app/Http/Controllers/ProdutoController.php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Http\Requests\ProdutoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $query = Produto::query();
        
        // Busca por termo
        if ($request->filled('busca')) {
            $query->buscar($request->busca);
        }
        
        // Filtro por categoria
        if ($request->filled('categoria')) {
            $query->porCategoria($request->categoria);
        }
        
        // Filtro por marca
        if ($request->filled('marca')) {
            $query->porMarca($request->marca);
        }
        
        // Filtro por faixa de preço
        if ($request->filled('preco_min') || $request->filled('preco_max')) {
            $query->faixaPreco($request->preco_min, $request->preco_max);
        }
        
        // Filtro por status do estoque
        if ($request->filled('estoque')) {
            switch ($request->estoque) {
                case 'disponivel':
                    $query->emEstoque();
                    break;
                case 'baixo':
                    $query->where('estoque', '>', 0)->where('estoque', '<=', 5);
                    break;
                case 'sem_estoque':
                    $query->where('estoque', 0);
                    break;
            }
        }
        
        // Filtro produtos em destaque
        if ($request->filled('destaque')) {
            $query->destaque();
        }
        
        // Filtro produtos com desconto
        if ($request->filled('promocao')) {
            $query->comDesconto();
        }
        
        // Apenas produtos ativos para usuários normais
        $query->ativo();
        
        // Ordenação
        $orderBy = $request->get('ordem', 'nome');
        $direction = $request->get('direcao', 'asc');
        
        switch ($orderBy) {
            case 'preco':
                $query->orderBy('preco', $direction);
                break;
            case 'popularidade':
                $query->orderBy('total_vendas', 'desc')->orderBy('visualizacoes', 'desc');
                break;
            case 'lancamento':
                $query->orderBy('created_at', 'desc');
                break;
            case 'avaliacao':
                $query->orderBy('avaliacao_media', 'desc');
                break;
            default:
                $query->orderBy('nome', $direction);
        }
        
        $produtos = $query->paginate(12)->withQueryString();
        
        // Dados para filtros
        $categorias = Produto::ativo()
                            ->select('categoria')
                            ->whereNotNull('categoria')
                            ->distinct()
                            ->orderBy('categoria')
                            ->pluck('categoria');
        
        $marcas = Produto::ativo()
                        ->select('marca')
                        ->whereNotNull('marca')
                        ->distinct()
                        ->orderBy('marca')
                        ->pluck('marca');
        
        return view('produtos.index', compact('produtos', 'categorias', 'marcas'));
    }

    public function show(Produto $produto)
    {
        // Incrementar visualizações
        $produto->incrementarVisualizacoes();
        
        // Produtos relacionados (mesma categoria, excluindo o atual)
        $relacionados = Produto::ativo()
                             ->emEstoque()
                             ->where('categoria', $produto->categoria)
                             ->where('id', '!=', $produto->id)
                             ->limit(4)
                             ->get();
        
        return view('produtos.show', compact('produto', 'relacionados'));
    }

    public function create()
    {
        return view('produtos.create');
    }

    public function store(ProdutoRequest $request)
    {
        $data = $request->validated();

        // Upload da imagem principal
        if ($request->hasFile('imagem')) {
            $data['imagem'] = $this->uploadImagem($request->file('imagem'));
        }

        // Upload da galeria de imagens
        if ($request->hasFile('galeria_imagens')) {
            $data['galeria_imagens'] = $this->uploadGaleriaImagens($request->file('galeria_imagens'));
        }

        // Processar características como JSON
        if ($request->filled('caracteristicas_json')) {
            $data['caracteristicas'] = json_decode($request->caracteristicas_json, true);
        }

        try {
            Produto::create($data);
            return redirect()->route('produtos.index')
                ->with('success', 'Produto cadastrado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar produto: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar produto. Tente novamente.');
        }
    }

    public function edit(Produto $produto)
    {
        return view('produtos.edit', compact('produto'));
    }

    public function update(ProdutoRequest $request, Produto $produto)
    {
        $data = $request->validated();

        // Upload da nova imagem principal
        if ($request->hasFile('imagem')) {
            // Remover imagem antiga
            if ($produto->imagem && Storage::disk('public')->exists('produtos/' . $produto->imagem)) {
                Storage::disk('public')->delete('produtos/' . $produto->imagem);
            }
            $data['imagem'] = $this->uploadImagem($request->file('imagem'));
        }

        // Upload de novas imagens da galeria
        if ($request->hasFile('galeria_imagens')) {
            // Remover imagens antigas da galeria
            if ($produto->galeria_imagens) {
                foreach ($produto->galeria_imagens as $imagem) {
                    if (Storage::disk('public')->exists('produtos/galeria/' . $imagem)) {
                        Storage::disk('public')->delete('produtos/galeria/' . $imagem);
                    }
                }
            }
            $data['galeria_imagens'] = $this->uploadGaleriaImagens($request->file('galeria_imagens'));
        }

        // Processar características
        if ($request->filled('caracteristicas_json')) {
            $data['caracteristicas'] = json_decode($request->caracteristicas_json, true);
        }

        try {
            $produto->update($data);
            return redirect()->route('produtos.index')
                ->with('success', 'Produto atualizado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar produto: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar produto. Tente novamente.');
        }
    }

    public function destroy(Produto $produto)
    {
        try {
            $produto->delete();
            return redirect()->route('produtos.index')
                ->with('success', 'Produto removido com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir produto: ' . $e->getMessage());
            return redirect()->route('produtos.index')
                ->with('error', 'Erro ao excluir produto: ' . $e->getMessage());
        }
    }

    // Métodos especiais
    public function destaques()
    {
        $produtos = Produto::ativo()
                          ->destaque()
                          ->emEstoque()
                          ->orderBy('created_at', 'desc')
                          ->paginate(8);
        
        return view('produtos.destaques', compact('produtos'));
    }

    public function promocoes()
    {
        $produtos = Produto::ativo()
                          ->comDesconto()
                          ->emEstoque()
                          ->orderBy('desconto_percentual', 'desc')
                          ->paginate(12);
        
        return view('produtos.promocoes', compact('produtos'));
    }

    public function categoria($categoria)
    {
        $produtos = Produto::ativo()
                          ->porCategoria($categoria)
                          ->orderBy('nome')
                          ->paginate(12);
        
        return view('produtos.categoria', compact('produtos', 'categoria'));
    }

    // Métodos auxiliares privados
    private function uploadImagem($arquivo)
    {
        $nomeImagem = Str::random(20) . '.' . $arquivo->getClientOriginalExtension();
        
        $pastaDestino = storage_path('app/public/produtos');
        if (!file_exists($pastaDestino)) {
            mkdir($pastaDestino, 0755, true);
        }
        
        $arquivo->storeAs('produtos', $nomeImagem, 'public');
        
        Log::info('Imagem do produto salva', [
            'arquivo' => $nomeImagem,
            'caminho' => $pastaDestino . '/' . $nomeImagem
        ]);
        
        return $nomeImagem;
    }

    private function uploadGaleriaImagens($arquivos)
    {
        $imagens = [];
        
        $pastaDestino = storage_path('app/public/produtos/galeria');
        if (!file_exists($pastaDestino)) {
            mkdir($pastaDestino, 0755, true);
        }
        
        foreach ($arquivos as $arquivo) {
            $nomeImagem = Str::random(20) . '.' . $arquivo->getClientOriginalExtension();
            $arquivo->storeAs('produtos/galeria', $nomeImagem, 'public');
            $imagens[] = $nomeImagem;
        }
        
        return $imagens;
    }
}