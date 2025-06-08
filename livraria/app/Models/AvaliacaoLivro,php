<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AvaliacaoLivro extends Model
{
    use HasFactory;

    protected $table = 'avaliacoes_livros';

    protected $fillable = [
        'livro_id',
        'user_id',
        'nota',
        'titulo',
        'comentario',
        'recomenda',
        'util_positivo',
        'util_negativo',
        'verificada'
    ];

    protected $casts = [
        'nota' => 'integer',
        'recomenda' => 'boolean',
        'util_positivo' => 'integer',
        'util_negativo' => 'integer',
        'verificada' => 'boolean'
    ];

    // Relacionamentos
    public function livro()
    {
        return $this->belongsTo(Livro::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getEstrelasAttribute()
    {
        $estrelas = '';
        for ($i = 1; $i <= 5; $i++) {
            $estrelas .= $i <= $this->nota ? '★' : '☆';
        }
        return $estrelas;
    }

    public function getUtilidadeAttribute()
    {
        $total = $this->util_positivo + $this->util_negativo;
        if ($total == 0) return 0;
        return round(($this->util_positivo / $total) * 100);
    }

    // Métodos
    public function marcarUtil($util = true)
    {
        if ($util) {
            $this->increment('util_positivo');
        } else {
            $this->increment('util_negativo');
        }
    }

    // Scopes
    public function scopeVerificadas($query)
    {
        return $query->where('verificada', true);
    }

    public function scopePorNota($query, $nota)
    {
        return $query->where('nota', $nota);
    }

    public function scopeRecentes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeUteis($query)
    {
        return $query->orderByDesc('util_positivo');
    }

    // Eventos
    protected static function boot()
    {
        parent::boot();

        static::created(function ($avaliacao) {
            $avaliacao->livro->atualizarAvaliacao();
        });

        static::updated(function ($avaliacao) {
            $avaliacao->livro->atualizarAvaliacao();
        });

        static::deleted(function ($avaliacao) {
            $avaliacao->livro->atualizarAvaliacao();
        });
    }
}