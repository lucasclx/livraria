<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'telefone',
        'data_nascimento',
        'genero',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'data_nascimento' => 'date',
        ];
    }

    // Relacionamentos
    public function favorites()
    {
        return $this->belongsToMany(Livro::class, 'favorites')->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function avaliacoes()
    {
        return $this->hasMany(AvaliacaoLivro::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    // Métodos auxiliares
    public function isAdmin()
    {
        return $this->is_admin === true;
    }

    public function getInitialsAttribute()
    {
        $names = explode(' ', $this->name);
        $initials = '';
        
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
            if (strlen($initials) >= 2) break;
        }
        
        return $initials;
    }

    public function getFirstNameAttribute()
    {
        return explode(' ', $this->name)[0];
    }

    // Scopes
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    public function scopeCustomers($query)
    {
        return $query->where('is_admin', false);
    }

    // Métodos de negócio
    public function hasOrderedBook($livroId)
    {
        return $this->orders()
            ->whereHas('cart.items', function($query) use ($livroId) {
                $query->where('livro_id', $livroId);
            })
            ->where('status', '!=', 'cancelled')
            ->exists();
    }

    public function hasFavoriteBook($livroId)
    {
        return $this->favorites()->where('livro_id', $livroId)->exists();
    }

    public function toggleFavorite($livroId)
    {
        if ($this->hasFavoriteBook($livroId)) {
            $this->favorites()->detach($livroId);
            return false; // Removido dos favoritos
        } else {
            $this->favorites()->attach($livroId);
            return true; // Adicionado aos favoritos
        }
    }

    public function getTotalSpentAttribute()
    {
        return $this->orders()
            ->where('status', '!=', 'cancelled')
            ->sum('total');
    }

    public function getOrdersCountAttribute()
    {
        return $this->orders()->count();
    }
}