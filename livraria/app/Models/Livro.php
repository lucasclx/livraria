<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Livro extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'titulo',
        'autor', 
        'isbn',
        'editora',
        'ano_publicacao',
        'preco',
        'preco_promocional',
        'paginas',
        'sinopse',
        'sumario',
        'categoria_id',
        'estoque',
        'estoque_minimo',
        'peso',
        'dimensoes',
        'idioma',
        'edicao',
        'encadernacao',
        'imagem',
        'galeria_imagens',
        'ativo',
        'destaque',
        'vendas_total',
        'avaliacao_media',
        'total_avaliacoes'
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'preco_promocional' => 'decimal:2',
        'ano_publicacao' => 'integer',
        'paginas' => 'integer',
        'estoque' => 'integer',
        'estoque_minimo' => 'integer',
        'peso' => 'decimal:3',
        'ativo' => 'boolean',
        'destaque' => 'boolean',
        'vendas_total' => 'integer',
        'avaliacao_media' => 'decimal:2',
        'total_avaliacoes' => 'integer',
        'galeria_imagens' => 'array'
    ];

    // Relacionamentos
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function avaliacoes()
    {
        return $this->hasMany(AvaliacaoLivro::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function vendas()
    {
        return $this->hasMany(ItemPedido::class);
    }

    // Accessors
    public function getPrecoFinalAttribute()
    {
        return $this->preco_promocional ?? $this->preco;
    }

    public function getPrecoFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->preco_final, 2, ',', '.');
    }

    public function getTemPromocaoAttribute()
    {
        return !is_null($this->preco_promocional) && $this->preco_promocional < $this->preco;
    }

    public function getDesconto()
    {
        if (!$this->tem_promocao) return 0;
        return round((($this->preco - $this->preco_promocional) / $this->preco) * 100);
    }

    public function getImagemUrlAttribute()
    {
        if ($this->imagem) {
            if (Storage::disk('public')->exists('livros/' . $this->imagem)) {
                return url('storage/livros/' . $this->imagem);
            }
        }
        return $this->getPlaceholderImage();
    }

    public function getGaleriaUrlsAttribute()
    {
        if (!$this->galeria_imagens) return [];
        
        return collect($this->galeria_imagens)->map(function ($imagem) {
            if (Storage::disk('public')->exists('livros/galeria/' . $imagem)) {
                return url('storage/livros/galeria/' . $imagem);
            }
            return null;
        })->filter()->values()->toArray();
    }

    public function getStatusEstoqueAttribute()
    {
        if ($this->estoque == 0) {
            return ['status' => 'sem_estoque', 'cor' => 'danger', 'texto' => 'Esgotado'];
        } elseif ($this->estoque <= $this->estoque_minimo) {
            return ['status' => 'estoque_baixo', 'cor' => 'warning', 'texto' => 'Últimas unidades'];
        } else {
            return ['status' => 'disponivel', 'cor' => 'success', 'texto' => 'Disponível'];
        }
    }

    public function getClassificacaoAttribute()
    {
        if ($this->avaliacao_media >= 4.5) return 'Excelente';
        if ($this->avaliacao_media >= 4.0) return 'Muito Bom';
        if ($this->avaliacao_media >= 3.0) return 'Bom';
        if ($this->avaliacao_media >= 2.0) return 'Regular';
        return 'Baixo';
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

    public function scopePromocao(Builder $query)
    {
        return $query->whereNotNull('preco_promocional')
                    ->whereColumn('preco_promocional', '<', 'preco');
    }

    public function scopeMaisVendidos(Builder $query, $limit = 10)
    {
        return $query->orderByDesc('vendas_total')->limit($limit);
    }

    public function scopeMelhoresAvaliados(Builder $query, $minAvaliacoes = 5)
    {
        return $query->where('total_avaliacoes', '>=', $minAvaliacoes)
                    ->orderByDesc('avaliacao_media');
    }

    public function scopeBuscar(Builder $query, $termo)
    {
        return $query->where(function ($q) use ($termo) {
            $q->where('titulo', 'like', "%{$termo}%")
              ->orWhere('autor', 'like', "%{$termo}%")
              ->orWhere('isbn', 'like', "%{$termo}%")
              ->orWhere('editora', 'like', "%{$termo}%")
              ->orWhere('sinopse', 'like', "%{$termo}%");
        });
    }

    public function scopePorPreco(Builder $query, $min = null, $max = null)
    {
        if ($min) {
            $query->where(function ($q) use ($min) {
                $q->where('preco_promocional', '>=', $min)
                  ->orWhere(function ($q2) use ($min) {
                      $q2->whereNull('preco_promocional')
                         ->where('preco', '>=', $min);
                  });
            });
        }

        if ($max) {
            $query->where(function ($q) use ($max) {
                $q->where('preco_promocional', '<=', $max)
                  ->orWhere(function ($q2) use ($max) {
                      $q2->whereNull('preco_promocional')
                         ->where('preco', '<=', $max);
                  });
            });
        }

        return $query;
    }

    // Métodos de negócio
    public function diminuirEstoque($quantidade = 1)
    {
        if ($this->estoque >= $quantidade) {
            $this->decrement('estoque', $quantidade);
            return true;
        }
        return false;
    }

    public function aumentarEstoque($quantidade = 1)
    {
        $this->increment('estoque', $quantidade);
        return true;
    }

    public function adicionarVenda($quantidade = 1)
    {
        $this->increment('vendas_total', $quantidade);
    }

    public function atualizarAvaliacao()
    {
        $avaliacoes = $this->avaliacoes;
        $this->total_avaliacoes = $avaliacoes->count();
        $this->avaliacao_media = $avaliacoes->count() > 0 
            ? $avaliacoes->avg('nota') 
            : 0;
        $this->save();
    }

    public function livrosRelacionados($limit = 4)
    {
        return self::where('categoria_id', $this->categoria_id)
                   ->where('id', '!=', $this->id)
                   ->ativo()
                   ->emEstoque()
                   ->orderByDesc('vendas_total')
                   ->limit($limit)
                   ->get();
    }

    private function getPlaceholderImage()
    {
        return 'data:image/svg+xml;base64,' . base64_encode('
            <svg width="200" height="300" xmlns="http://www.w3.org/2000/svg">
                <rect width="200" height="300" fill="#f8f9fa" stroke="#dee2e6"/>
                <text x="100" y="140" text-anchor="middle" fill="#6c757d" font-size="14">Capa Indisponível</text>
                <text x="100" y="160" text-anchor="middle" fill="#6c757d" font-size="24">📚</text>
            </svg>
        ');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($livro) {
            // Deletar imagem principal
            if ($livro->imagem && Storage::disk('public')->exists('livros/' . $livro->imagem)) {
                Storage::disk('public')->delete('livros/' . $livro->imagem);
            }
            
            // Deletar galeria
            if ($livro->galeria_imagens) {
                foreach ($livro->galeria_imagens as $imagem) {
                    if (Storage::disk('public')->exists('livros/galeria/' . $imagem)) {
                        Storage::disk('public')->delete('livros/galeria/' . $imagem);
                    }
                }
            }
        });
    }
}