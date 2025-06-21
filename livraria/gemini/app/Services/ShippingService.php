<?php

namespace App\Services;

class ShippingService
{
    private $options = [
        'sedex' => ['name' => 'SEDEX', 'days' => 2, 'base_price' => 15.00],
        'pac' => ['name' => 'PAC', 'days' => 5, 'base_price' => 8.50],
        'expressa' => ['name' => 'Expressa', 'days' => 1, 'base_price' => 25.00]
    ];

    public function calculate($items, $postalCode)
    {
        $weight = $this->calculateWeight($items);
        $distance = $this->getDistanceMultiplier($postalCode);
        
        $options = [];
        foreach ($this->options as $code => $option) {
            $price = $this->calculatePrice($option['base_price'], $weight, $distance);
            $options[] = [
                'code' => $code,
                'name' => $option['name'],
                'price' => $price,
                'days' => $option['days'],
                'formatted_price' => 'R$ ' . number_format($price, 2, ',', '.')
            ];
        }

        // Retornar a opção mais barata como padrão
        $cheapest = collect($options)->sortBy('price')->first();
        
        return [
            'options' => $options,
            'option' => $cheapest['code'],
            'price' => $cheapest['price'],
            'days' => $cheapest['days'],
            'name' => $cheapest['name']
        ];
    }

    public function getOption($code, $postalCode, $items)
    {
        if (!isset($this->options[$code])) {
            throw new \Exception('Opção de frete inválida');
        }

        $option = $this->options[$code];
        $weight = $this->calculateWeight($items);
        $distance = $this->getDistanceMultiplier($postalCode);
        $price = $this->calculatePrice($option['base_price'], $weight, $distance);

        return [
            'code' => $code,
            'name' => $option['name'],
            'price' => $price,
            'days' => $option['days']
        ];
    }

    private function calculateWeight($items)
    {
        $totalWeight = 0;
        foreach ($items as $item) {
            // Peso padrão por livro: 0.3kg
            $itemWeight = $item->livro->peso ?? 0.3;
            $totalWeight += $itemWeight * $item->quantity;
        }
        return max($totalWeight, 0.1); // Mínimo 100g
    }

    private function getDistanceMultiplier($postalCode)
    {
        // Simulação baseada no CEP
        $firstDigit = (int) substr(preg_replace('/\D/', '', $postalCode), 0, 1);
        
        return match (true) {
            $firstDigit <= 2 => 1.0,    // Sudeste
            $firstDigit <= 4 => 1.2,    // Sul
            $firstDigit <= 6 => 1.5,    // Nordeste
            $firstDigit <= 7 => 1.8,    // Norte
            default => 1.3              // Centro-Oeste
        };
    }

    private function calculatePrice($basePrice, $weight, $distanceMultiplier)
    {
        // Fórmula: preço base + (peso * R$ 2,00) * multiplicador de distância
        $price = ($basePrice + ($weight * 2.00)) * $distanceMultiplier;
        
        // Frete grátis acima de R$ 150
        return $price > 150 ? 0 : round($price, 2);
    }

    public function trackPackage($trackingCode)
    {
        // Simulação de rastreamento
        $statuses = [
            'Objeto postado',
            'Objeto em trânsito',
            'Objeto saiu para entrega',
            'Objeto entregue'
        ];

        return [
            'code' => $trackingCode,
            'status' => $statuses[array_rand($statuses)],
            'last_update' => now()->subHours(rand(1, 48))
        ];
    }
}