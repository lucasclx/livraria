<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class FakePaymentService implements PaymentService
{
    public function process(float $amount): bool
    {
        $success = (bool) random_int(0, 1);

        if ($success) {
            Log::info('Pagamento simulado bem-sucedido', ['amount' => $amount]);
        } else {
            Log::warning('Falha no pagamento simulado', ['amount' => $amount]);
        }

        return $success;
    }
}
