<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\StockMovement;

class OrderService
{
    public function create(array $data)
    {
        $order = Order::create(array_merge($data, [
            'order_number' => $this->generateOrderNumber(),
            'status' => 'pending_payment'
        ]));

        $this->addStatusHistory($order, 'pending_payment', 'Pedido criado');
        
        return $order;
    }

    public function confirmPayment(Order $order)
    {
        $order->update(['status' => 'paid']);
        $this->addStatusHistory($order, 'paid', 'Pagamento confirmado');
        
        // Confirmar venda no estoque
        foreach ($order->cart->items as $item) {
            $this->recordStockMovement($item->livro_id, 'sale', $item->quantity, 'Order', $order->id);
            $item->livro->increment('vendas_total', $item->quantity);
        }
    }

    public function cancel(Order $order, string $reason = null)
    {
        $order->update(['status' => 'cancelled']);
        $this->addStatusHistory($order, 'cancelled', $reason ?? 'Pedido cancelado');
    }

    public function ship(Order $order, string $trackingCode = null)
    {
        $order->update([
            'status' => 'shipped',
            'tracking_code' => $trackingCode,
            'shipped_at' => now()
        ]);
        
        $this->addStatusHistory($order, 'shipped', 'Pedido enviado' . ($trackingCode ? " - Código: {$trackingCode}" : ''));
    }

    public function deliver(Order $order)
    {
        $order->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);
        
        $this->addStatusHistory($order, 'delivered', 'Pedido entregue');
    }

    private function generateOrderNumber()
    {
        return date('Y') . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT);
    }

    private function addStatusHistory(Order $order, string $status, string $notes = null)
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $status,
            'notes' => $notes,
            'changed_at' => now()
        ]);
    }

    private function recordStockMovement($livroId, $type, $quantity, $refType = null, $refId = null)
    {
        $livro = \App\Models\Livro::find($livroId);
        
        StockMovement::create([
            'livro_id' => $livroId,
            'type' => $type,
            'quantity_before' => $livro->estoque + $quantity,
            'quantity_change' => -$quantity,
            'quantity_after' => $livro->estoque,
            'reference_type' => $refType,
            'reference_id' => $refId
        ]);
    }
}
