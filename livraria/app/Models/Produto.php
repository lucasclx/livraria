<?php
// app/Models/Produto.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Produto extends Model
{
    protected $fillable = [
        'nome',
        'descricao', 
        'preco',
        'estoque',
        'categoria',
        'marca',
        'sku',
        'caracteristicas',
        'imagem',
        'galeria_imagens',
        'peso',
        'unidade_medida',
        'desconto_percentual',
        'ativo',
        'destaque',
        'data_lancamento'
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'estoque' => 'integer',
        'caracteristicas' => 'array',
        'galeria_imagens' => 'array',
        'peso' => 'decimal:3',
        'desconto_percentual' => 'decimal:2',
        'ativo' => 'boolean',
        'destaque' => 'boolean',
        'avaliacao_media' => 'decimal:2',
        'data_lancamento' => 'datetime'
    ];

    // Accessors
    public function getPrecoFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->preco, 2, ',', '.');
    }

    public function getPrecoComDescontoAttribute()
    {
        if ($this->desconto_percentual > 0) {
            $desconto = ($this->preco * $this->desconto_percentual) / 100;
            return $this->preco - $desconto;
        }
        return $this->preco;
    }

    public function getPrecoComDescontoFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->preco_com_desconto, 2, ',', '.');
    }

    public function getTemDescontoAttribute()
    {
        return $this->desconto_percentual > 0;
    }

    public function getImagemUrlAttribute()
    {
        if ($this->imagem) {
            if (Storage::disk('public')->exists('produtos/' . $this->imagem)) {
                return url('storage/produtos/' . $this->imagem);
            }
        }
        
        // Placeholder SVG para produtos sem imagem
        return 'data:image/svg+xml;base64,' . base64_encode('
            <svg width="300" height="300" xmlns="http://www.w3.org/2000/svg">
                <rect width="300" height="300" fill="#f8f9fa" stroke="#dee2e6"/>
                <text x="150" y="140" text-anchor="middle" fill="#6c757d" font-size="16">Sem Imagem</text>
                <text x="150" y="160" text-anchor="middle" fill="#6c757d" font-size="36">🛍️</text>
            </svg>
        ');
    }

    public function getGaleriaImagensUrlAttribute()
    {
        if (!$this->galeria_imagens) {
            return [];
        }

        return collect($this->galeria_imagens)->map(function ($imagem) {
            if (Storage::disk('public')->exists('produtos/galeria/' . $imagem)) {
                return url('storage/produtos/galeria/' . $imagem);
            }
            return null;
        })->filter()->values()->toArray();
    }

    public function getStatusEstoqueAttribute()
    {
        if ($this->estoque == 0) {
            return ['status' => 'sem_estoque', 'cor' => 'danger', 'texto' => 'Sem Estoque'];
        } elseif ($this->estoque <= 5) {
            return ['status' => 'estoque_baixo', 'cor' => 'warning', 'texto' => 'Estoque Baixo'];
        } else {
            return ['status' => 'disponivel', 'cor' => 'success', 'texto' => 'Disponível'];
        }
    }

    public function getEconomiaAttribute()
    {
        if ($this->tem_desconto) {
            return $this->preco - $this->preco_com_desconto;
        }
        return 0;
    }

    public function getEconomiaFormatadaAttribute()
    {
        return 'R$ ' . number_format($this->economia, 2, ',', '.');
    }

    // Scopes
    public function scopeAtivo(Builder $query)
    {
        return $query->where('ativo', true);
    }

    public function scopeEmEstoque(Builder $query)
    {
        return $query->where('estoque', '>', 0);
    }

    public function scopeDestaque(Builder $query)
    {
        return $query->where('destaque', true);
    }

    public function scopeComDesconto(Builder $query)
    {
        return $query->where('desconto_percentual', '>', 0);
    }

    public function scopePorCategoria(Builder $query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorMarca(Builder $query, $marca)
    {
        return $query->where('marca', $marca);
    }

    public function scopeBuscar(Builder $query, $termo)
    {
        return $query->where(function ($q) use ($termo) {
            $q->where('nome', 'like', "%{$termo}%")
              ->orWhere('descricao', 'like', "%{$termo}%")
              ->orWhere('categoria', 'like', "%{$termo}%")
              ->orWhere('marca', 'like', "%{$termo}%")
              ->orWhere('sku', 'like', "%{$termo}%");
        });
    }

    public function scopeFaixaPreco(Builder $query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('preco', '>=', $min);
        }
        if ($max !== null) {
            $query->where('preco', '<=', $max);
        }
        return $query;
    }

    // Métodos utilitários
    public function incrementarVisualizacoes()
    {
        $this->increment('visualizacoes');
    }

    public function diminuirEstoque($quantidade = 1)
    {
        if ($this->estoque >= $quantidade) {
            $this->decrement('estoque', $quantidade);
            $this->increment('total_vendas', $quantidade);
            return true;
        }
        return false;
    }

    public function aumentarEstoque($quantidade = 1)
    {
        $this->increment('estoque', $quantidade);
        return true;
    }

    // Relacionamentos
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function avaliacoes()
    {
        return $this->hasMany(Avaliacao::class);
    }

    public function favoritos()
    {
        return $this->belongsToMany(User::class, 'favoritos')->withTimestamps();
    }

    // Eventos do modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($produto) {
            // Gerar SKU automático se não fornecido
            if (!$produto->sku) {
                $produto->sku = 'PRD-' . strtoupper(uniqid());
            }
        });

        static::deleting(function ($produto) {
            // Remover imagens ao excluir produto
            if ($produto->imagem && Storage::disk('public')->exists('produtos/' . $produto->imagem)) {
                Storage::disk('public')->delete('produtos/' . $produto->imagem);
            }
            
            if ($produto->galeria_imagens) {
                foreach ($produto->galeria_imagens as $imagem) {
                    if (Storage::disk('public')->exists('produtos/galeria/' . $imagem)) {
                        Storage::disk('public')->delete('produtos/galeria/' . $imagem);
                    }
                }
            }
        });
    }
}