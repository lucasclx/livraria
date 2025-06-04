<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
        'imagem',
        'ativo'
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function livros()
    {
        return $this->hasMany(Livro::class);
    }

    public function getImagemUrlAttribute()
    {
        if ($this->imagem) {
            return Storage::url('categorias/' . $this->imagem);
        }
        return asset('vendor/adminlte/dist/img/default-150x150.png');
    }

    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($categoria) {
            if ($categoria->imagem) {
                Storage::delete('public/categorias/' . $categoria->imagem);
            }
        });
    }
}