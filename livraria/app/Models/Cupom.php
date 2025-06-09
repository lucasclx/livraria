<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cupom extends Model
{
    // Definir o nome correto da tabela
    protected $table = 'cupons';

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

    public function orders()
    {
        return $this->hasMany(Order::class);
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

    // Accessors
    public function getValorFormatadoAttribute()
    {
        if ($this->tipo === 'percentual') {
            return $this->valor . '%';
        }
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    public function getStatusAttribute()
    {
        if (!$this->ativo) {
            return ['status' => 'inativo', 'texto' => 'Inativo', 'cor' => 'secondary'];
        }

        if ($this->valido_de > now()) {
            return ['status' => 'futuro', 'texto' => 'Não Iniciado', 'cor' => 'info'];
        }

        if ($this->valido_ate < now()) {
            return ['status' => 'expirado', 'texto' => 'Expirado', 'cor' => 'danger'];
        }

        if ($this->limite_uso && $this->vezes_usado >= $this->limite_uso) {
            return ['status' => 'esgotado', 'texto' => 'Esgotado', 'cor' => 'warning'];
        }

        return ['status' => 'ativo', 'texto' => 'Ativo', 'cor' => 'success'];
    }

    public function getDiasRestantesAttribute()
    {
        if ($this->valido_ate < now()) {
            return 0;
        }

        return now()->diffInDays($this->valido_ate);
    }

    public function getUsosRestantesAttribute()
    {
        if (!$this->limite_uso) {
            return null; // Ilimitado
        }

        return max(0, $this->limite_uso - $this->vezes_usado);
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

    public function scopeExpirados($query)
    {
        return $query->where('valido_ate', '<', now());
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeBuscar($query, $termo)
    {
        return $query->where(function($q) use ($termo) {
            $q->where('codigo', 'like', "%{$termo}%")
              ->orWhere('descricao', 'like', "%{$termo}%");
        });
    }

    // Métodos estáticos
    public static function buscarPorCodigo($codigo)
    {
        return static::where('codigo', strtoupper($codigo))->first();
    }

    public static function gerarCodigoUnico($length = 8)
    {
        do {
            $codigo = strtoupper(\Illuminate\Support\Str::random($length));
        } while (static::where('codigo', $codigo)->exists());

        return $codigo;
    }
}