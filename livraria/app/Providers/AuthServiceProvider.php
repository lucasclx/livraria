<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Gate para verificar se é admin
        Gate::define('admin', function ($user) {
            return $user->is_admin === true;
        });

        // Gate para verificar se pode gerenciar livros
        Gate::define('manage-books', function ($user) {
            return $user->is_admin === true;
        });

        // Gate para verificar se pode gerenciar categorias
        Gate::define('manage-categories', function ($user) {
            return $user->is_admin === true;
        });

        // Gate para verificar se pode ver relatórios
        Gate::define('view-reports', function ($user) {
            return $user->is_admin === true;
        });

        // Gate para verificar se pode gerenciar pedidos
        Gate::define('manage-orders', function ($user) {
            return $user->is_admin === true;
        });
    }
}