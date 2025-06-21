<?php

namespace App\Services;

interface PaymentService
{
    /**
     * Process a payment for the given amount.
     */
    public function process(float $amount): bool;
}
