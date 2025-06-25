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
        'total_avaliacoes',
        'promocao_inicio',
        'promocao_fim',
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
        'galeria_imagens' => 'array',
        'promocao_inicio' => 'datetime',
        'promocao_fim' => 'datetime',
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

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // Accessors
    public function getPrecoFinalAttribute()
    {
        if ($this->tem_promocao) {
            return $this->preco_promocional;
        }
        return $this->preco;
    }

    public function getPrecoFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->preco_final, 2, ',', '.');
    }

    public function getTemPromocaoAttribute()
    {
        if (!$this->preco_promocional) return false;
        if ($this->preco_promocional >= $this->preco) return false;
        
        $now = now();
        if ($this->promocao_inicio && $now < $this->promocao_inicio) return false;
        if ($this->promocao_fim && $now > $this->promocao_fim) return false;
        
        return true;
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

    // Scopes
    public function scopeAtivo(Builder $query)
    {
        return $query->where('ativo', true);
    }

    public function scopeEmEstoque(Builder $query)
    {
        return $query->where('estoque', '>', 0);
    }

    public function scopeEstoqueBaixo(Builder $query)
    {
        return $query->whereColumn('estoque', '<=', 'estoque_minimo')
                    ->where('estoque', '>', 0);
    }

    public function scopeDestaque(Builder $query)
    {
        return $query->where('destaque', true);
    }

    public function scopePromocao(Builder $query)
    {
        $now = now();
        return $query->whereNotNull('preco_promocional')
                    ->whereColumn('preco_promocional', '<', 'preco')
                    ->where(function($q) use ($now) {
                        $q->whereNull('promocao_inicio')
                          ->orWhere('promocao_inicio', '<=', $now);
                    })
                    ->where(function($q) use ($now) {
                        $q->whereNull('promocao_fim')
                          ->orWhere('promocao_fim', '>=', $now);
                    });
    }

    public function scopeBuscar(Builder $query, $termo)
    {
        return $query->where(function ($q) use ($termo) {
            $q->where('titulo', 'like', "%{$termo}%")
              ->orWhere('autor', 'like', "%{$termo}%")
              ->orWhere('isbn', 'like', "%{$termo}%")
              ->orWhere('editora', 'like', "%{$termo}%")
              ->orWhere('sinopse', 'like', "%{$termo}%")
              ->orWhereHas('categoria', function($query) use ($termo) {
                  $query->where('nome', 'like', "%{$termo}%");
              });
        });
    }

    public function scopePorCategoria(Builder $query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    // Métodos de negócio
    public function diminuirEstoque($quantidade = 1, $motivo = 'venda', $referencia = null)
    {
        if ($this->estoque >= $quantidade) {
            $estoqueAnterior = $this->estoque;
            $this->decrement('estoque', $quantidade);
            
            // Registrar movimento de estoque
            $this->stockMovements()->create([
                'type' => $motivo,
                'quantity_before' => $estoqueAnterior,
                'quantity_change' => -$quantidade,
                'quantity_after' => $this->estoque,
                'reference_type' => $referencia ? get_class($referencia) : null,
                'reference_id' => $referencia?->id,
            ]);
            
            return true;
        }
        return false;
    }

    public function aumentarEstoque($quantidade = 1, $motivo = 'reposicao', $referencia = null)
    {
        $estoqueAnterior = $this->estoque;
        $this->increment('estoque', $quantidade);
        
        // Registrar movimento de estoque
        $this->stockMovements()->create([
            'type' => $motivo,
            'quantity_before' => $estoqueAnterior,
            'quantity_change' => $quantidade,
            'quantity_after' => $this->estoque,
            'reference_type' => $referencia ? get_class($referencia) : null,
            'reference_id' => $referencia?->id,
        ]);
        
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

    // Método getPlaceholderImage com um SVG genérico (você pode personalizar)
    private function getPlaceholderImage()
    {
        return 'data:image/svg+xml;base64,' . base64_encode('
            <svg width="200" height="300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 300">
                <rect width="200" height="300" fill="#e0e0e0"/>
                <text x="100" y="150" font-family="Arial" font-size="20" fill="#666" text-anchor="middle" dominant-baseline="middle">Capa</text>
                <text x="100" y="175" font-family="Arial" font-size="14" fill="#666" text-anchor="middle" dominant-baseline="middle">Indisponível</text>
                <path d="M70 100 L130 100 L130 200 L70 200 Z" fill="#999"/>
                <path d="M70 100 L100 80 L130 100" fill="#777"/>
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