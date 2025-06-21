<?php

namespace App\Services;

use Illuminate\Support\Collection;

class FreteService implements ShippingService
{
    /**
     * Calcula o custo do frete com base nos itens do carrinho.
     * O valor por item agora é obtido do arquivo de configuração.
     *
     * @param Collection $cartItems
     * @return float
     */
    public function calculateShipping(Collection $cartItems): float
    {
        if ($cartItems->isEmpty()) {
            return 0.0;
        }

        $custoPorItem = (float) config('services.frete.custo_por_item', 5.00);
        $quantidadeTotalItens = $cartItems->sum('quantidade');

        // Lógica de cálculo de frete (ex: custo fixo por item)
        $custoFrete = $quantidadeTotalItens * $custoPorItem;

        return $custoFrete;
    }
}