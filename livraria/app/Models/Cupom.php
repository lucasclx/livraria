<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cupom extends Model
{
    protected $fillable = [
        'codigo',
        'descricao',
        'tipo',
        'valor',
        'valor_minimo_pedido',
        'limite_uso',
        'vezes_usado',
        'primeiro_pedido_apenas',
        'valido_de',
        'valido_ate',
        'ativo'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_minimo_pedido' => 'decimal:2',
        'limite_uso' => 'integer',
        'vezes_usado' => 'integer',
        'primeiro_pedido_apenas' => 'boolean',
        'valido_de' => 'datetime',
        'valido_ate' => 'datetime',
        'ativo' => 'boolean'
    ];

    // Relacionamentos
    public function utilizacoes()
    {
        return $this->hasMany(CupomUtilizado::class);
    }

    // Métodos de validação
    public function isValido($valorPedido = 0, $isPrimeiroPedido = false)
    {
        if (!$this->ativo) return false;
        if ($this->valido_de > now()) return false;
        if ($this->valido_ate < now()) return false;
        if ($this->limite_uso && $this->vezes_usado >= $this->limite_uso) return false;
        if ($this->valor_minimo_pedido && $valorPedido < $this->valor_minimo_pedido) return false;
        if ($this->primeiro_pedido_apenas && !$isPrimeiroPedido) return false;

        return true;
    }

    public function calcularDesconto($valorPedido)
    {
        if (!$this->isValido($valorPedido)) return 0;

        if ($this->tipo === 'percentual') {
            return ($valorPedido * $this->valor) / 100;
        }

        return min($this->valor, $valorPedido);
    }

    public function usar()
    {
        $this->increment('vezes_usado');
    }

    // Scopes
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeValidos($query)
    {
        return $query->where('valido_de', '<=', now())
                    ->where('valido_ate', '>=', now());
    }

    public function scopeDisponiveis($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('limite_uso')
              ->orWhereColumn('vezes_usado', '<', 'limite_uso');
        });
    }
}