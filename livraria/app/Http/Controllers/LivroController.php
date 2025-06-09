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

    // ==========================================
    // RELACIONAMENTOS
    // ==========================================

    /**
     * Relacionamento com Categoria
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class); 
    }

    /**
     * Relacionamento com Avaliações
     */
    public function avaliacoes()
    {
        return $this->hasMany(AvaliacaoLivro::class);
    }

    /**
     * Relacionamento com Usuários que favoritaram (Many-to-Many)
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /**
     * Relacionamento com Itens do Carrinho
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Relacionamento com Movimentações de Estoque
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // ==========================================
    // ACCESSORS (ATRIBUTOS CALCULADOS)
    // ==========================================

    /**
     * Retorna o preço final (com ou sem promoção)
     */
    public function getPrecoFinalAttribute()
    {
        if ($this->tem_promocao) {
            return $this->preco_promocional;
        }
        return $this->preco;
    }

    /**
     * Retorna o preço formatado em Real brasileiro
     */
    public function getPrecoFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->preco_final, 2, ',', '.');
    }

    /**
     * Verifica se o livro está em promoção
     */
    public function getTemPromocaoAttribute()
    {
        if (!$this->preco_promocional) return false;
        if ($this->preco_promocional >= $this->preco) return false;
        
        $now = now();
        if ($this->promocao_inicio && $now < $this->promocao_inicio) return false;
        if ($this->promocao_fim && $now > $this->promocao_fim) return false;
        
        return true;
    }

    /**
     * Calcula a porcentagem de desconto
     */
    public function getDesconto()
    {
        if (!$this->tem_promocao) return 0;
        return round((($this->preco - $this->preco_promocional) / $this->preco) * 100);
    }

    /**
     * Retorna a URL da imagem do livro ou placeholder
     */
    public function getImagemUrlAttribute()
    {
        if ($this->imagem) {
            if (Storage::disk('public')->exists('livros/' . $this->imagem)) {
                return url('storage/livros/' . $this->imagem);
            }
        }
        return $this->getPlaceholderImage();
    }

    /**
     * Retorna o status do estoque com informações de cor e texto
     */
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

    /**
     * Retorna array com URLs da galeria de imagens
     */
    public function getGaleriaImagensUrlAttribute()
    {
        if (!$this->galeria_imagens || !is_array($this->galeria_imagens)) {
            return [];
        }

        $urls = [];
        foreach ($this->galeria_imagens as $imagem) {
            if (Storage::disk('public')->exists('livros/galeria/' . $imagem)) {
                $urls[] = url('storage/livros/galeria/' . $imagem);
            }
        }

        return $urls;
    }

    /**
     * Retorna as estrelas da avaliação média
     */
    public function getEstrelasAvaliacaoAttribute()
    {
        $nota = round($this->avaliacao_media);
        $estrelas = '';
        for ($i = 1; $i <= 5; $i++) {
            $estrelas .= $i <= $nota ? '★' : '☆';
        }
        return $estrelas;
    }

    // ==========================================
    // SCOPES (CONSULTAS PERSONALIZADAS)
    // ==========================================

    /**
     * Scope para livros ativos
     */
    public function scopeAtivo(Builder $query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para livros em estoque
     */
    public function scopeEmEstoque(Builder $query)
    {
        return $query->where('estoque', '>', 0);
    }

    /**
     * Scope para livros com estoque baixo
     */
    public function scopeEstoqueBaixo(Builder $query)
    {
        return $query->whereColumn('estoque', '<=', 'estoque_minimo')
                    ->where('estoque', '>', 0);
    }

    /**
     * Scope para livros em destaque
     */
    public function scopeDestaque(Builder $query)
    {
        return $query->where('destaque', true);
    }

    /**
     * Scope para livros em promoção
     */
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

    /**
     * Scope para buscar livros por termo
     */
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

    /**
     * Scope para filtrar por categoria
     */
    public function scopePorCategoria(Builder $query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    /**
     * Scope para livros mais vendidos
     */
    public function scopeMaisVendidos(Builder $query, $limit = 10)
    {
        return $query->orderByDesc('vendas_total')->limit($limit);
    }

    /**
     * Scope para livros recentes
     */
    public function scopeRecentes(Builder $query, $limit = 10)
    {
        return $query->latest()->limit($limit);
    }

    /**
     * Scope para livros bem avaliados
     */
    public function scopeBemAvaliados(Builder $query, $minAvaliacoes = 3)
    {
        return $query->where('total_avaliacoes', '>=', $minAvaliacoes)
                    ->orderByDesc('avaliacao_media');
    }

    // ==========================================
    // MÉTODOS DE NEGÓCIO
    // ==========================================

    /**
     * Diminui o estoque do livro
     */
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

    /**
     * Aumenta o estoque do livro
     */
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

    /**
     * Adiciona uma venda ao contador
     */
    public function adicionarVenda($quantidade = 1)
    {
        $this->increment('vendas_total', $quantidade);
    }

    /**
     * Atualiza a avaliação média do livro
     */
    public function atualizarAvaliacao()
    {
        $avaliacoes = $this->avaliacoes;
        $this->total_avaliacoes = $avaliacoes->count();
        $this->avaliacao_media = $avaliacoes->count() > 0 
            ? $avaliacoes->avg('nota') 
            : 0;
        $this->save();
    }

    /**
     * Busca livros relacionados (mesma categoria)
     */
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

    /**
     * Verifica se o livro pode ser comprado
     */
    public function podeSerComprado($quantidade = 1)
    {
        return $this->ativo && $this->estoque >= $quantidade;
    }

    /**
     * Verifica se o usuário pode avaliar este livro
     */
    public function podeSerAvaliadoPor(User $user)
    {
        // Verificar se o usuário já avaliou
        $jaAvaliou = $this->avaliacoes()->where('user_id', $user->id)->exists();
        
        // Verificar se o usuário comprou o livro
        $jaComprou = $user->orders()
            ->whereHas('cart.items', function($query) {
                $query->where('livro_id', $this->id);
            })
            ->where('status', '!=', 'cancelled')
            ->exists();

        return !$jaAvaliou && $jaComprou;
    }

    /**
     * Calcula o valor total em estoque
     */
    public function getValorTotalEstoque()
    {
        return $this->estoque * $this->preco_final;
    }

    // ==========================================
    // MÉTODOS DE PLACEHOLDER DE IMAGEM
    // ==========================================

    /**
     * Retorna a imagem placeholder baseada na disponibilidade
     */
    private function getPlaceholderImage()
    {
        // Lista de extensões suportadas
        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        // Opção 1: Imagem na pasta public/images
        foreach ($extensions as $ext) {
            $defaultImagePath = "images/capa-padrao.{$ext}";
            if (file_exists(public_path($defaultImagePath))) {
                return asset($defaultImagePath);
            }
        }

        // Opção 2: Imagem na pasta storage/app/public/defaults
        foreach ($extensions as $ext) {
            $storageDefaultPath = "defaults/capa-padrao.{$ext}";
            if (Storage::disk('public')->exists($storageDefaultPath)) {
                return url('storage/' . $storageDefaultPath);
            }
        }

        // Opção 3: Imagem na pasta storage/app/public/livros (como padrão)
        foreach ($extensions as $ext) {
            $booksDefaultPath = "livros/placeholder.{$ext}";
            if (Storage::disk('public')->exists($booksDefaultPath)) {
                return url('storage/' . $booksDefaultPath);
            }
        }

        // Fallback: SVG caso não encontre nenhuma imagem
        return $this->getFallbackSvg();
    }

    /**
     * SVG de fallback melhorado caso não encontre imagem alguma
     */
    private function getFallbackSvg()
    {
        $categoryName = $this->categoria?->nome ?? 'Livro';
        $bookIcon = $this->getCategoryIcon();
        $colors = $this->getCategoryColors();

        $svg = '
        <svg width="200" height="300" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:' . $colors['primary'] . ';stop-opacity:0.1" />
                    <stop offset="100%" style="stop-color:' . $colors['secondary'] . ';stop-opacity:0.3" />
                </linearGradient>
                <pattern id="bookPattern" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                    <rect width="40" height="40" fill="none"/>
                    <circle cx="20" cy="20" r="1" fill="' . $colors['accent'] . '" opacity="0.1"/>
                </pattern>
            </defs>
            
            <!-- Fundo principal -->
            <rect width="200" height="300" fill="url(#grad1)" stroke="' . $colors['border'] . '" stroke-width="2" rx="12"/>
            <rect width="200" height="300" fill="url(#bookPattern)"/>
            
            <!-- Header decorativo -->
            <rect x="0" y="0" width="200" height="60" fill="' . $colors['primary'] . '" opacity="0.05" rx="12"/>
            
            <!-- Linhas decorativas (simulando texto) -->
            <rect x="30" y="80" width="140" height="3" fill="' . $colors['text'] . '" opacity="0.2" rx="1.5"/>
            <rect x="30" y="95" width="100" height="3" fill="' . $colors['text'] . '" opacity="0.15" rx="1.5"/>
            <rect x="30" y="110" width="120" height="3" fill="' . $colors['text'] . '" opacity="0.1" rx="1.5"/>
            
            <!-- Área do ícone -->
            <circle cx="100" cy="170" r="35" fill="' . $colors['iconBg'] . '" opacity="0.1"/>
            <circle cx="100" cy="170" r="25" fill="' . $colors['iconBg'] . '" opacity="0.2"/>
            
            <!-- Ícone principal -->
            <text x="100" y="180" text-anchor="middle" fill="' . $colors['icon'] . '" font-size="28" opacity="0.7">' . $bookIcon . '</text>
            
            <!-- Texto categoria -->
            <text x="100" y="220" text-anchor="middle" fill="' . $colors['text'] . '" font-size="11" font-family="Arial, sans-serif" opacity="0.6">' . htmlspecialchars($categoryName) . '</text>
            
            <!-- Texto "Capa Indisponível" -->
            <text x="100" y="240" text-anchor="middle" fill="' . $colors['text'] . '" font-size="10" font-family="Arial, sans-serif" opacity="0.5">Capa</text>
            <text x="100" y="255" text-anchor="middle" fill="' . $colors['text'] . '" font-size="10" font-family="Arial, sans-serif" opacity="0.5">Indisponível</text>
            
            <!-- Bordas decorativas -->
            <rect x="20" y="20" width="160" height="260" fill="none" stroke="' . $colors['border'] . '" stroke-width="1" opacity="0.2" rx="8"/>
        </svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Retorna ícone baseado na categoria
     */
    private function getCategoryIcon()
    {
        if (!$this->categoria) return '📚';

        $categoryIcons = [
            'ficção' => '📖',
            'ficcion' => '📖',
            'romance' => '💕',
            'técnico' => '🔧',
            'tecnico' => '🔧',
            'tecnologia' => '💻',
            'ciência' => '🔬',
            'ciencia' => '🔬',
            'história' => '📜',
            'historia' => '📜',
            'biografia' => '👤',
            'não-ficção' => '📊',
            'nao-ficcao' => '📊',
            'infantil' => '🎨',
            'terror' => '👻',
            'suspense' => '🔍',
            'fantasia' => '🧙‍♂️',
            'aventura' => '🗺️',
            'culinária' => '🍳',
            'culinaria' => '🍳',
            'religião' => '🙏',
            'religiao' => '🙏',
            'autoajuda' => '🌟',
            'negócios' => '💼',
            'negocios' => '💼',
            'educação' => '🎓',
            'educacao' => '🎓',
        ];

        $categoryName = strtolower($this->categoria->nome);
        $categorySlug = $this->categoria->slug ?? \Illuminate\Support\Str::slug($this->categoria->nome);

        return $categoryIcons[$categoryName] 
            ?? $categoryIcons[$categorySlug] 
            ?? '📚';
    }

    /**
     * Retorna cores baseadas na categoria
     */
    private function getCategoryColors()
    {
        if (!$this->categoria) {
            return [
                'primary' => '#6c757d',
                'secondary' => '#adb5bd',
                'accent' => '#495057',
                'border' => '#dee2e6',
                'text' => '#495057',
                'icon' => '#6c757d',
                'iconBg' => '#f8f9fa',
            ];
        }

        $categoryColors = [
            'ficção' => ['primary' => '#6f42c1', 'secondary' => '#9c7ae3', 'accent' => '#563d7c'],
            'romance' => ['primary' => '#e83e8c', 'secondary' => '#f093c1', 'accent' => '#d63384'],
            'técnico' => ['primary' => '#0d6efd', 'secondary' => '#6ea8fe', 'accent' => '#0a58ca'],
            'tecnologia' => ['primary' => '#198754', 'secondary' => '#75b798', 'accent' => '#146c43'],
            'história' => ['primary' => '#fd7e14', 'secondary' => '#feb379', 'accent' => '#e55a0e'],
            'biografia' => ['primary' => '#20c997', 'secondary' => '#7edcc7', 'accent' => '#198754'],
            'infantil' => ['primary' => '#ffc107', 'secondary' => '#ffda6a', 'accent' => '#f59e0b'],
            'terror' => ['primary' => '#dc3545', 'secondary' => '#ea868f', 'accent' => '#bb2d3b'],
            'fantasia' => ['primary' => '#9c27b0', 'secondary' => '#ce93d8', 'accent' => '#7b1fa2'],
        ];

        $categoryName = strtolower($this->categoria->nome);
        $categorySlug = $this->categoria->slug ?? \Illuminate\Support\Str::slug($this->categoria->nome);

        $colors = $categoryColors[$categoryName] 
               ?? $categoryColors[$categorySlug] 
               ?? ['primary' => '#6c757d', 'secondary' => '#adb5bd', 'accent' => '#495057'];

        return array_merge($colors, [
            'border' => '#dee2e6',
            'text' => '#495057',
            'icon' => $colors['accent'],
            'iconBg' => '#f8f9fa',
        ]);
    }

    // ==========================================
    // MÉTODOS AUXILIARES
    // ==========================================

    /**
     * Método auxiliar para verificar se tem imagem personalizada
     */
    public function hasCustomImage()
    {
        return $this->imagem && Storage::disk('public')->exists('livros/' . $this->imagem);
    }

    /**
     * Método auxiliar para obter tipo de placeholder sendo usado
     */
    public function getPlaceholderType()
    {
        if ($this->hasCustomImage()) {
            return 'custom_image';
        }

        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        foreach ($extensions as $ext) {
            if (file_exists(public_path("images/capa-padrao.{$ext}"))) {
                return 'custom_default_public';
            }
            if (Storage::disk('public')->exists("defaults/capa-padrao.{$ext}")) {
                return 'custom_default_storage';
            }
        }

        return 'generated_svg';
    }

    /**
     * Converte o livro para array para APIs
     */
    public function toApiArray()
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'autor' => $this->autor,
            'isbn' => $this->isbn,
            'editora' => $this->editora,
            'ano_publicacao' => $this->ano_publicacao,
            'preco' => $this->preco,
            'preco_promocional' => $this->preco_promocional,
            'preco_final' => $this->preco_final,
            'preco_formatado' => $this->preco_formatado,
            'tem_promocao' => $this->tem_promocao,
            'desconto_percentual' => $this->getDesconto(),
            'categoria' => $this->categoria?->nome,
            'categoria_slug' => $this->categoria?->slug,
            'estoque' => $this->estoque,
            'status_estoque' => $this->status_estoque,
            'avaliacao_media' => $this->avaliacao_media,
            'total_avaliacoes' => $this->total_avaliacoes,
            'estrelas' => $this->estrelas_avaliacao,
            'imagem_url' => $this->imagem_url,
            'ativo' => $this->ativo,
            'destaque' => $this->destaque,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    // ==========================================
    // EVENTOS DO MODEL
    // ==========================================

    protected static function boot()
    {
        parent::boot();

        // Evento ao deletar livro
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

        // Evento ao criar livro
        static::creating(function ($livro) {
            // Definir valores padrão se não informados
            if (is_null($livro->estoque_minimo)) {
                $livro->estoque_minimo = 5;
            }
            if (is_null($livro->peso)) {
                $livro->peso = 0.5;
            }
            if (is_null($livro->idioma)) {
                $livro->idioma = 'Português';
            }
            if (is_null($livro->vendas_total)) {
                $livro->vendas_total = 0;
            }
            if (is_null($livro->avaliacao_media)) {
                $livro->avaliacao_media = 0;
            }
            if (is_null($livro->total_avaliacoes)) {
                $livro->total_avaliacoes = 0;
            }
        });

        // Evento ao atualizar livro
        static::updating(function ($livro) {
            // Se o estoque mudou, registrar movimento
            if ($livro->isDirty('estoque')) {
                $original = $livro->getOriginal('estoque');
                $novo = $livro->estoque;
                $diferenca = $novo - $original;
                
                if ($diferenca != 0) {
                    $livro->stockMovements()->create([
                        'type' => $diferenca > 0 ? 'ajuste_positivo' : 'ajuste_negativo',
                        'quantity_before' => $original,
                        'quantity_change' => $diferenca,
                        'quantity_after' => $novo,
                        'reference_type' => 'manual_update',
                        'reference_id' => auth()->id(),
                    ]);
                }
            }
        });
    }
}