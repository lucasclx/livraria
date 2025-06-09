<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Services\PaymentService;
use App\Services\FakePaymentService;
use App\Services\StockService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentService::class, FakePaymentService::class);
        $this->app->singleton(StockService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configura a paginação para usar Bootstrap 5
        Paginator::useBootstrapFive();
    }
}