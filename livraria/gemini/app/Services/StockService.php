<?php
// app/Services/StockService.php

namespace App\Services;

use App\Models\Livro;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Reservar estoque para venda
     */
    public function reserveStock(array $items): bool
    {
        return DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                $livro = Livro::lockForUpdate()->find($item['livro_id']);
                
                if (!$livro || $livro->estoque < $item['quantity']) {
                    throw new \Exception("Estoque insuficiente para {$livro->titulo ?? 'livro ID ' . $item['livro_id']}");
                }
                
                // Diminuir estoque temporariamente
                $livro->diminuirEstoque($item['quantity'], 'reserva', $item['reference'] ?? null);
            }
            
            return true;
        });
    }

    /**
     * Confirmar venda (estoque já foi reservado)
     */
    public function confirmSale(array $items): bool
    {
        return DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                $livro = Livro::find($item['livro_id']);
                
                if ($livro) {
                    // Apenas registrar movimento de confirmação
                    StockMovement::create([
                        'livro_id' => $livro->id,
                        'type' => 'venda_confirmada',
                        'quantity_before' => $livro->estoque,
                        'quantity_change' => 0, // Já foi diminuído na reserva
                        'quantity_after' => $livro->estoque,
                        'reference_type' => $item['reference_type'] ?? null,
                        'reference_id' => $item['reference_id'] ?? null,
                    ]);
                    
                    $livro->adicionarVenda($item['quantity']);
                }
            }
            
            return true;
        });
    }

    /**
     * Cancelar reserva (devolver estoque)
     */
    public function cancelReservation(array $items): bool
    {
        return DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                $livro = Livro::find($item['livro_id']);
                
                if ($livro) {
                    $livro->aumentarEstoque($item['quantity'], 'cancelamento', $item['reference'] ?? null);
                }
            }
            
            return true;
        });
    }

    /**
     * Repor estoque
     */
    public function replenishStock(int $livroId, int $quantity, string $reason = 'reposicao', $reference = null): bool
    {
        $livro = Livro::find($livroId);
        
        if (!$livro) {
            throw new \Exception('Livro não encontrado');
        }
        
        return $livro->aumentarEstoque($quantity, $reason, $reference);
    }

    /**
     * Verificar produtos com estoque baixo
     */
    public function getLowStockItems(): \Illuminate\Database\Eloquent\Collection
    {
        return Livro::estoqueBaixo()
                   ->with('categoria')
                   ->orderBy('estoque')
                   ->get();
    }

    /**
     * Verificar produtos sem estoque
     */
    public function getOutOfStockItems(): \Illuminate\Database\Eloquent\Collection
    {
        return Livro::where('estoque', 0)
                   ->where('ativo', true)
                   ->with('categoria')
                   ->orderBy('updated_at', 'desc')
                   ->get();
    }

    /**
     * Relatório de movimentação de estoque
     */
    public function getStockMovements(int $livroId = null, int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        $query = StockMovement::with('livro')
                             ->where('created_at', '>=', now()->subDays($days))
                             ->orderBy('created_at', 'desc');
        
        if ($livroId) {
            $query->where('livro_id', $livroId);
        }
        
        return $query->get();
    }

    /**
     * Calcular valor total do estoque
     */
    public function getTotalStockValue(): float
    {
        return Livro::where('ativo', true)
                   ->where('estoque', '>', 0)
                   ->get()
                   ->sum(function ($livro) {
                       return $livro->estoque * $livro->preco_final;
                   });
    }

    /**
     * Validar disponibilidade de itens
     */
    public function validateAvailability(array $items): array
    {
        $errors = [];
        
        foreach ($items as $item) {
            $livro = Livro::find($item['livro_id']);
            
            if (!$livro) {
                $errors[] = "Livro ID {$item['livro_id']} não encontrado";
                continue;
            }
            
            if (!$livro->ativo) {
                $errors[] = "O livro '{$livro->titulo}' não está mais ativo";
                continue;
            }
            
            if ($livro->estoque < $item['quantity']) {
                $errors[] = "Estoque insuficiente para '{$livro->titulo}'. Disponível: {$livro->estoque}, Solicitado: {$item['quantity']}";
            }
        }
        
        return $errors;
    }

    /**
     * Ajustar estoque (para correções manuais)
     */
    public function adjustStock(int $livroId, int $newQuantity, string $reason = 'ajuste_manual'): bool
    {
        return DB::transaction(function () use ($livroId, $newQuantity, $reason) {
            $livro = Livro::lockForUpdate()->find($livroId);
            
            if (!$livro) {
                throw new \Exception('Livro não encontrado');
            }
            
            $oldQuantity = $livro->estoque;
            $change = $newQuantity - $oldQuantity;
            
            // Atualizar estoque
            $livro->update(['estoque' => $newQuantity]);
            
            // Registrar movimento
            StockMovement::create([
                'livro_id' => $livro->id,
                'type' => $reason,
                'quantity_before' => $oldQuantity,
                'quantity_change' => $change,
                'quantity_after' => $newQuantity,
                'reference_type' => 'manual',
                'reference_id' => auth()->id(),
            ]);
            
            return true;
        });
    }
}