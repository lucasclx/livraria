<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
        'imagem',
        'ativo',
        'slug'
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

    // Gerar slug automaticamente ao criar/atualizar
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($categoria) {
            if (empty($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->nome);
            }
        });

        static::updating(function ($categoria) {
            if ($categoria->isDirty('nome')) {
                $categoria->slug = Str::slug($categoria->nome);
            }
        });

        static::deleting(function ($categoria) {
            if ($categoria->imagem) {
                Storage::delete('public/categorias/' . $categoria->imagem);
            }
        });
    }

    // Garantir que o slug seja único
    public function setSlugAttribute($value)
    {
        $slug = Str::slug($value);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $this->attributes['slug'] = $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}