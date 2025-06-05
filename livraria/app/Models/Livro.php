<?php
// app/Models/Livro.php - VERSÃO MELHORADA

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class Livro extends Model
{
    protected $fillable = [
        'titulo',
        'autor', 
        'isbn',
        'editora',
        'ano_publicacao',
        'preco',
        'paginas',
        'sinopse',
        'categoria',
        'estoque',
        'imagem',
        'ativo',
        'destaque',
        'idioma',
        'edicao',
        'genero',
        'tags',
        'desconto_percentual',
        'peso'
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'ano_publicacao' => 'integer',
        'paginas' => 'integer',
        'estoque' => 'integer',
        'ativo' => 'boolean',
        'destaque' => 'boolean',
        'desconto_percentual' => 'decimal:2',
        'peso' => 'decimal:3',
        'tags' => 'array'
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

    public function getImagemUrlAttribute()
    {
        if ($this->imagem) {
            // Verificar se existe no storage público
            if (Storage::disk('public')->exists('livros/' . $this->imagem)) {
                return url('storage/livros/' . $this->imagem);
            }
            
            // Fallback: verificar se existe na pasta public/images
            $publicPath = public_path('images/livros/' . $this->imagem);
            if (file_exists($publicPath)) {
                return asset('images/livros/' . $this->imagem);
            }
        }
        
        // Imagem padrão específica para livros
        return 'data:image/svg+xml;base64,' . base64_encode('
            <svg width="200" height="300" xmlns="http://www.w3.org/2000/svg">
                <rect width="200" height="300" fill="#f8f9fa" stroke="#dee2e6" stroke-width="2"/>
                <rect x="20" y="40" width="160" height="4" fill="#6c757d" opacity="0.3"/>
                <rect x="20" y="55" width="120" height="3" fill="#6c757d" opacity="0.3"/>
                <rect x="20" y="68" width="140" height="3" fill="#6c757d" opacity="0.3"/>
                <text x="100" y="140" text-anchor="middle" fill="#6c757d" font-size="14">Sem Capa</text>
                <text x="100" y="160" text-anchor="middle" fill="#6c757d" font-size="32">📚</text>
                <text x="100" y="180" text-anchor="middle" fill="#6c757d" font-size="12">Livro</text>
            </svg>
        ');
    }

    public function getStatusEstoqueAttribute()
    {
        if ($this->estoque == 0) {
            return ['status' => 'sem_estoque', 'cor' => 'danger', 'texto' => 'Esgotado'];
        } elseif ($this->estoque <= 5) {
            return ['status' => 'estoque_baixo', 'cor' => 'warning', 'texto' => 'Últimas Unidades'];
        } else {
            return ['status' => 'disponivel', 'cor' => 'success', 'texto' => 'Disponível'];
        }
    }

    public function getGeneroFormatadoAttribute()
    {
        $generos = [
            'ficcao' => 'Ficção',
            'nao_ficcao' => 'Não-ficção',
            'romance' => 'Romance',
            'fantasia' => 'Fantasia',
            'misterio' => 'Mistério',
            'biografia' => 'Biografia',
            'historia' => 'História',
            'ciencia' => 'Ciência',
            'tecnologia' => 'Tecnologia',
            'autoajuda' => 'Autoajuda',
            'infantil' => 'Infantil',
            'jovem_adulto' => 'Jovem Adulto',
            'academico' => 'Acadêmico',
            'arte' => 'Arte',
            'culinaria' => 'Culinária',
            'viagem' => 'Viagem',
            'religiao' => 'Religião',
            'filosofia' => 'Filosofia'
        ];
        
        return $generos[$this->genero] ?? ucfirst($this->genero);
    }

    public function getIdiomaFormatadoAttribute()
    {
        $idiomas = [
            'pt' => 'Português',
            'en' => 'Inglês',
            'es' => 'Espanhol',
            'fr' => 'Francês',
            'de' => 'Alemão',
            'it' => 'Italiano',
            'ja' => 'Japonês',
            'zh' => 'Chinês'
        ];
        
        return $idiomas[$this->idioma] ?? ucfirst($this->idioma);
    }

    public function getAvaliacaoEstrelinhasAttribute()
    {
        $rating = $this->avaliacao_media ?? 0;
        $fullStars = floor($rating);
        $hasHalfStar = ($rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
        
        $stars = str_repeat('★', $fullStars);
        if ($hasHalfStar) {
            $stars .= '☆';
        }
        $stars .= str_repeat('☆', $emptyStars);
        
        return $stars;
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
        return $query->where('estoque', '>', 0)->where('estoque', '<=', 5);
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

    public function scopePorGenero(Builder $query, $genero)
    {
        return $query->where('genero', $genero);
    }

    public function scopePorAutor(Builder $query, $autor)
    {
        return $query->where('autor', 'like', "%{$autor}%");
    }

    public function scopePorEditora(Builder $query, $editora)
    {
        return $query->where('editora', 'like', "%{$editora}%");
    }

    public function scopeBuscar(Builder $query, $termo)
    {
        return $query->where(function ($q) use ($termo) {
            $q->where('titulo', 'like', "%{$termo}%")
              ->orWhere('autor', 'like', "%{$termo}%")
              ->orWhere('isbn', 'like', "%{$termo}%")
              ->orWhere('editora', 'like', "%{$termo}%")
              ->orWhere('sinopse', 'like', "%{$termo}%")
              ->orWhere('categoria', 'like', "%{$termo}%");
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

    public function scopePorAno(Builder $query, $ano_inicio = null, $ano_fim = null)
    {
        if ($ano_inicio !== null) {
            $query->where('ano_publicacao', '>=', $ano_inicio);
        }
        if ($ano_fim !== null) {
            $query->where('ano_publicacao', '<=', $ano_fim);
        }
        return $query;
    }

    public function scopeMaisVendidos(Builder $query, $limit = 10)
    {
        return $query->orderBy('total_vendas', 'desc')->limit($limit);
    }

    public function scopeLancamentos(Builder $query, $dias = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($dias));
    }

    // Relacionamentos
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function avaliacoes()
    {
        return $this->hasMany(AvaliacaoLivro::class);
    }

    public function favoritos()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function categoria_relacionada()
    {
        return $this->belongsTo(Categoria::class, 'categoria', 'nome');
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

    public function calcularAvaliacaoMedia()
    {
        $this->avaliacao_media = $this->avaliacoes()->avg('nota') ?? 0;
        $this->save();
    }

    public function livrosSimilares($limit = 4)
    {
        return self::where('id', '!=', $this->id)
                   ->where(function($query) {
                       $query->where('categoria', $this->categoria)
                             ->orWhere('genero', $this->genero)
                             ->orWhere('autor', $this->autor);
                   })
                   ->ativo()
                   ->emEstoque()
                   ->limit($limit)
                   ->get();
    }

    // Eventos do modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($livro) {
            // Definir categoria baseada no gênero se não informada
            if (!$livro->categoria && $livro->genero) {
                $livro->categoria = $livro->genero_formatado;
            }
            
            // Adicionar tags automáticas
            if (!$livro->tags) {
                $tags = [];
                if ($livro->genero) $tags[] = $livro->genero;
                if ($livro->categoria) $tags[] = $livro->categoria;
                if ($livro->editora) $tags[] = $livro->editora;
                $livro->tags = $tags;
            }
        });

        static::deleting(function ($livro) {
            if ($livro->imagem && Storage::disk('public')->exists('livros/' . $livro->imagem)) {
                Storage::disk('public')->delete('livros/' . $livro->imagem);
            }
        });
    }

    // Métodos estáticos úteis
    public static function topCategorias($limit = 10)
    {
        return self::select('categoria')
                   ->selectRaw('COUNT(*) as total_livros')
                   ->selectRaw('SUM(estoque) as total_estoque')
                   ->whereNotNull('categoria')
                   ->groupBy('categoria')
                   ->orderByRaw('total_livros DESC')
                   ->limit($limit)
                   ->get();
    }

    public static function topAutores($limit = 10)
    {
        return self::select('autor')
                   ->selectRaw('COUNT(*) as total_livros')
                   ->selectRaw('AVG(preco) as preco_medio')
                   ->groupBy('autor')
                   ->orderByRaw('total_livros DESC')
                   ->limit($limit)
                   ->get();
    }

    public static function topEditoras($limit = 10)
    {
        return self::select('editora')
                   ->selectRaw('COUNT(*) as total_livros')
                   ->whereNotNull('editora')
                   ->groupBy('editora')
                   ->orderByRaw('total_livros DESC')
                   ->limit($limit)
                   ->get();
    }
}