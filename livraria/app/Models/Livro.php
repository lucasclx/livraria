<?php

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
        'ativo'
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'ano_publicacao' => 'integer',
        'paginas' => 'integer',
        'estoque' => 'integer',
        'ativo' => 'boolean'
    ];

    // Accessors
    public function getPrecoFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->preco, 2, ',', '.');
    }

    public function getImagemUrlAttribute()
    {
        if ($this->imagem) {
            // Verificar se existe no storage público
            if (Storage::disk('public')->exists('livros/' . $this->imagem)) {
                // Usar url() para garantir que funcione com o link simbólico
                return url('storage/livros/' . $this->imagem);
            }
            
            // Fallback: verificar se existe na pasta public/images
            $publicPath = public_path('images/livros/' . $this->imagem);
            if (file_exists($publicPath)) {
                return asset('images/livros/' . $this->imagem);
            }
        }
        
        // Imagem padrão - criar um placeholder se não existir
        return data_url('image/svg+xml;base64,' . base64_encode('
            <svg width="200" height="300" xmlns="http://www.w3.org/2000/svg">
                <rect width="200" height="300" fill="#f8f9fa" stroke="#dee2e6"/>
                <text x="100" y="140" text-anchor="middle" fill="#6c757d" font-size="14">Sem Imagem</text>
                <text x="100" y="160" text-anchor="middle" fill="#6c757d" font-size="24">📚</text>
            </svg>
        '));
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

    public function scopePorCategoria(Builder $query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopeBuscar(Builder $query, $termo)
    {
        return $query->where(function ($q) use ($termo) {
            $q->where('titulo', 'like', "%{$termo}%")
              ->orWhere('autor', 'like', "%{$termo}%")
              ->orWhere('isbn', 'like', "%{$termo}%")
              ->orWhere('editora', 'like', "%{$termo}%");
        });
    }

    // Eventos do modelo
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($livro) {
            if ($livro->imagem && Storage::disk('public')->exists('livros/' . $livro->imagem)) {
                Storage::disk('public')->delete('livros/' . $livro->imagem);
            }
        });
    }

    // Métodos utilitários
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

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
}