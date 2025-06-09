<?php
// routes/web.php - Adicionar as novas rotas

// Rotas de Perfil do Usuário
Route::middleware('auth')->group(function () {
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::get('/perfil/editar', [PerfilController::class, 'editar'])->name('perfil.editar');
    Route::put('/perfil', [PerfilController::class, 'atualizar'])->name('perfil.atualizar');
    Route::post('/perfil/alterar-senha', [PerfilController::class, 'alterarSenha'])->name('perfil.alterar-senha');
    Route::post('/perfil/preferencias', [PerfilController::class, 'salvarPreferencias'])->name('perfil.preferencias');
    
    // Subpáginas do perfil
    Route::get('/perfil/pedidos', [PerfilController::class, 'pedidos'])->name('perfil.pedidos');
    Route::get('/perfil/favoritos', [PerfilController::class, 'favoritos'])->name('perfil.favoritos');
    Route::get('/perfil/avaliacoes', [PerfilController::class, 'avaliacoes'])->name('perfil.avaliacoes');
});

// Rotas de Avaliações
Route::middleware('auth')->group(function () {
    Route::post('/livros/{livro}/avaliacoes', [AvaliacaoController::class, 'store'])->name('avaliacoes.store');
    Route::post('/avaliacoes/{avaliacao}/util', [AvaliacaoController::class, 'marcarUtil'])->name('avaliacoes.util');
});

// Rotas de Frete
Route::group(['prefix' => 'frete'], function () {
    Route::post('/calcular', [FreteController::class, 'calcular'])->name('frete.calcular');
    Route::post('/buscar-cep', [FreteController::class, 'buscarCep'])->name('frete.buscar-cep');
});

// Rotas de Favoritos
Route::middleware('auth')->group(function () {
    Route::post('/livros/{livro}/favorite', [FavoriteController::class, 'toggle'])->name('livros.favorite');
});

// app/Models/User.php - Adicionar campos e relacionamentos necessários

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'telefone',
        'data_nascimento',
        'genero',
        'preferencias',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'data_nascimento' => 'date',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'preferencias' => 'array',
        ];
    }

    // Relacionamentos
    public function favorites()
    {
        return $this->belongsToMany(Livro::class, 'favorites')->withTimestamps();
    }

    public function avaliacoes()
    {
        return $this->hasMany(AvaliacaoLivro::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }
}

// database/migrations/xxxx_xx_xx_add_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telefone')->nullable()->after('email');
            $table->date('data_nascimento')->nullable()->after('telefone');
            $table->enum('genero', ['masculino', 'feminino', 'outro', 'prefiro_nao_informar'])->nullable()->after('data_nascimento');
            $table->json('preferencias')->nullable()->after('genero');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telefone', 'data_nascimento', 'genero', 'preferencias']);
        });
    }
};

// app/Http/Controllers/PerfilController.php - Métodos adicionais

public function salvarPreferencias(Request $request)
{
    $user = Auth::user();
    
    $preferencias = [
        'email_promocoes' => $request->boolean('email_promocoes'),
        'email_novidades' => $request->boolean('email_novidades'),
        'email_pedidos' => $request->boolean('email_pedidos'),
        'perfil_publico' => $request->boolean('perfil_publico'),
        'mostrar_nome' => $request->boolean('mostrar_nome'),
    ];
    
    $user->update(['preferencias' => $preferencias]);
    
    return redirect()->back()->with('success', 'Preferências salvas com sucesso!');
}

// resources/views/loja/detalhes.blade.php - Integrar calculadora de frete
// Adicionar após a seção de preço (cerca da linha 150):

<!-- Calculadora de Frete -->
@include('components.calculadora-frete')

// app/Http/Controllers/AvaliacaoController.php - Método para marcar como útil

public function marcarUtil(Request $request, AvaliacaoLivro $avaliacao)
{
    $request->validate([
        'util' => 'required|boolean'
    ]);
    
    if ($request->util) {
        $avaliacao->increment('util_positivo');
    } else {
        $avaliacao->increment('util_negativo');
    }
    
    return response()->json(['success' => true]);
}

// resources/views/components/navegacao-perfil.blade.php - Menu lateral do perfil

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-user me-2"></i>Minha Conta
        </h6>
    </div>
    <div class="list-group list-group-flush">
        <a href="{{ route('perfil.index') }}" 
           class="list-group-item list-group-item-action {{ request()->routeIs('perfil.index') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a href="{{ route('perfil.editar') }}" 
           class="list-group-item list-group-item-action {{ request()->routeIs('perfil.editar') ? 'active' : '' }}">
            <i class="fas fa-edit me-2"></i>Editar Perfil
        </a>
        <a href="{{ route('perfil.pedidos') }}" 
           class="list-group-item list-group-item-action {{ request()->routeIs('perfil.pedidos') ? 'active' : '' }}">
            <i class="fas fa-shopping-bag me-2"></i>Meus Pedidos
        </a>
        <a href="{{ route('perfil.favoritos') }}" 
           class="list-group-item list-group-item-action {{ request()->routeIs('perfil.favoritos') ? 'active' : '' }}">
            <i class="fas fa-heart me-2"></i>Favoritos
        </a>
        <a href="{{ route('perfil.avaliacoes') }}" 
           class="list-group-item list-group-item-action {{ request()->routeIs('perfil.avaliacoes') ? 'active' : '' }}">
            <i class="fas fa-star me-2"></i>Minhas Avaliações
        </a>
        <div class="dropdown-divider"></div>
        <a href="{{ route('logout') }}" 
           class="list-group-item list-group-item-action text-danger"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt me-2"></i>Sair
        </a>
    </div>
</div>

// config/app.php - Adicionar configuração de CEP de origem

'cep_origem' => env('CEP_ORIGEM', '01001-000'),

// .env - Adicionar variáveis de ambiente

# Frete
CEP_ORIGEM=01001-000

# APIs externas
VIACEP_URL=https://viacep.com.br/ws/
CORREIOS_API_URL=https://api.correios.com.br/

// resources/views/layouts/app.blade.php - Atualizar navegação

<!-- No navbar, adicionar link para perfil: -->
@auth
<div class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
        <i class="fas fa-user-circle me-1"></i>{{ Auth::user()->name }}
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="{{ route('perfil.index') }}">
            <i class="fas fa-user me-2"></i>Meu Perfil
        </a></li>
        <li><a class="dropdown-item" href="{{ route('perfil.pedidos') }}">
            <i class="fas fa-shopping-bag me-2"></i>Meus Pedidos
        </a></li>
        <li><a class="dropdown-item" href="{{ route('perfil.favoritos') }}">
            <i class="fas fa-heart me-2"></i>Favoritos
        </a></li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt me-2"></i>Sair
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>
    </ul>
</div>
@endauth

// composer.json - Adicionar dependência do Guzzle

"require": {
    "guzzlehttp/guzzle": "^7.0"
}

// Para instalar execute: composer install

// app/Providers/AppServiceProvider.php - Registrar serviços

public function register(): void
{
    $this->app->singleton(FreteService::class);
}

// resources/views/loja/detalhes.blade.php - Adicionar na aba de especificações

@if($livro->peso)
<div class="spec-item">
    <span><i class="fas fa-weight-hanging me-2"></i>Peso:</span>
    <strong>{{ number_format($livro->peso, 3) }}kg</strong>
</div>
@endif

@if($livro->dimensoes)
<div class="spec-item">
    <span><i class="fas fa-ruler me-2"></i>Dimensões:</span>
    <strong>{{ $livro->dimensoes }}</strong>
</div>
@endif