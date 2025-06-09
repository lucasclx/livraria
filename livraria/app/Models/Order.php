<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'cart_id',
        'user_id',
        'cupom_id',
        'total',
        'desconto',
        'shipping_cost',
        'shipping_address',
        'payment_method',
        'tracking_code',
        'shipped_at',
        'delivered_at',
        'status',
        'notes',
        'observacoes',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'desconto' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'shipping_address' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Status válidos
    const STATUS_PENDING_PAYMENT = 'pending_payment';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PAYMENT_FAILED = 'payment_failed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    public static function getStatusList()
    {
        return [
            self::STATUS_PENDING_PAYMENT => 'Aguardando Pagamento',
            self::STATUS_CONFIRMED => 'Confirmado',
            self::STATUS_PAYMENT_FAILED => 'Falha no Pagamento',
            self::STATUS_PROCESSING => 'Processando',
            self::STATUS_SHIPPED => 'Enviado',
            self::STATUS_DELIVERED => 'Entregue',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_REFUNDED => 'Estornado',
        ];
    }

    // Relacionamentos
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cupom(): BelongsTo
    {
        return $this->belongsTo(Cupom::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return self::getStatusList()[$this->status] ?? 'Status Desconhecido';
    }

    public function getSubtotalAttribute()
    {
        return $this->total - $this->shipping_cost + $this->desconto;
    }

    public function getFormattedTotalAttribute()
    {
        return 'R$ ' . number_format($this->total, 2, ',', '.');
    }

    public function getFormattedShippingAddressAttribute()
    {
        if (!$this->shipping_address) return '';
        
        $address = $this->shipping_address;
        return implode(', ', array_filter([
            $address['street'] ?? '',
            $address['city'] ?? '',
            $address['state'] ?? '',
            $address['zip'] ?? '',
        ]));
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING_PAYMENT);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeShipped($query)
    {
        return $query->where('status', self::STATUS_SHIPPED);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    // Métodos de negócio
    public function canBeCancelled()
    {
        return in_array($this->status, [
            self::STATUS_PENDING_PAYMENT,
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING
        ]);
    }

    public function canBeShipped()
    {
        return in_array($this->status, [
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING
        ]);
    }

    public function canBeDelivered()
    {
        return $this->status === self::STATUS_SHIPPED;
    }

    public function updateStatus($newStatus, $notes = null)
    {
        $oldStatus = $this->status;
        
        // Validar transição de status
        if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
            throw new \Exception("Transição de status inválida: {$oldStatus} -> {$newStatus}");
        }

        $this->update(['status' => $newStatus]);

        // Registrar histórico
        $this->statusHistory()->create([
            'status' => $newStatus,
            'notes' => $notes,
            'changed_at' => now(),
        ]);

        return $this;
    }

    private function isValidStatusTransition($from, $to)
    {
        $validTransitions = [
            self::STATUS_PENDING_PAYMENT => [
                self::STATUS_CONFIRMED,
                self::STATUS_PAYMENT_FAILED,
                self::STATUS_CANCELLED
            ],
            self::STATUS_CONFIRMED => [
                self::STATUS_PROCESSING,
                self::STATUS_CANCELLED
            ],
            self::STATUS_PROCESSING => [
                self::STATUS_SHIPPED,
                self::STATUS_CANCELLED
            ],
            self::STATUS_SHIPPED => [
                self::STATUS_DELIVERED
            ],
            self::STATUS_PAYMENT_FAILED => [
                self::STATUS_PENDING_PAYMENT,
                self::STATUS_CANCELLED
            ],
            self::STATUS_DELIVERED => [
                self::STATUS_REFUNDED
            ],
        ];

        return isset($validTransitions[$from]) && 
               in_array($to, $validTransitions[$from]);
    }

    // Eventos do modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = static::generateOrderNumber();
            }
        });

        static::created(function ($order) {
            // Criar primeiro registro de histórico
            $order->statusHistory()->create([
                'status' => $order->status,
                'notes' => 'Pedido criado',
                'changed_at' => $order->created_at,
            ]);
        });
    }

    private static function generateOrderNumber()
    {
        $year = date('Y');
        $month = date('m');
        $nextId = static::max('id') + 1;
        
        return sprintf('%s%s%06d', $year, $month, $nextId);
    }
}