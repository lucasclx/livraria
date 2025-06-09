<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\AvaliacaoController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LivroController;
use App\Http\Controllers\LojaController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\RelatorioController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Página inicial - redireciona para a loja
Route::get('/', function () {
    return redirect()->route('loja.index');
});

// Rotas de autenticação
Auth::routes();

// Rota home (dashboard) - com middleware de admin
Route::get('/home', [HomeController::class, 'index'])->name('dashboard')->middleware('auth');

// Rotas da Loja (públicas)
Route::prefix('loja')->name('loja.')->group(function () {
    Route::get('/', [LojaController::class, 'index'])->name('index');
    Route::get('/catalogo', [LojaController::class, 'catalogo'])->name('catalogo');
    Route::get('/categoria/{categoria}', [LojaController::class, 'categoria'])->name('categoria');
    Route::get('/livro/{livro}', [LojaController::class, 'detalhes'])->name('detalhes');
    Route::get('/favoritos', [LojaController::class, 'favoritos'])->name('favoritos');
    Route::get('/buscar', [LojaController::class, 'buscar'])->name('buscar');
});

// Rotas de Favoritos (requer autenticação)
Route::middleware('auth')->group(function () {
    Route::post('/livros/{livro}/favorite', [FavoriteController::class, 'toggle'])->name('livros.favorite');
});

// Rotas de Carrinho
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add/{livro}', [CartController::class, 'add'])->name('add');
    Route::post('/update/{item}', [CartController::class, 'update'])->name('item.update');
    Route::post('/remove/{item}', [CartController::class, 'remove'])->name('item.remove');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    Route::get('/count', [CartController::class, 'getCartCount'])->name('count');
});

// Rotas de Checkout (requer autenticação)
Route::middleware('auth')->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/process', [CheckoutController::class, 'process'])->name('process');
    Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
    Route::post('/calculate-shipping', [CheckoutController::class, 'calculateShipping'])->name('calculate-shipping');
});

// Rotas alternativas para checkout (compatibilidade)
Route::middleware('auth')->group(function () {
    Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::post('/cart/checkout', [CartController::class, 'processCheckout'])->name('cart.process-checkout');
});

// Rotas de Pedidos (requer autenticação)
Route::middleware('auth')->prefix('orders')->name('orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/{order}', [OrderController::class, 'show'])->name('show');
});

// Rotas de Perfil do Usuário (requer autenticação)
Route::middleware('auth')->prefix('perfil')->name('perfil.')->group(function () {
    Route::get('/', [PerfilController::class, 'index'])->name('index');
    Route::get('/editar', [PerfilController::class, 'editar'])->name('editar');
    Route::put('/', [PerfilController::class, 'atualizar'])->name('atualizar');
    Route::post('/alterar-senha', [PerfilController::class, 'alterarSenha'])->name('alterar-senha');
    Route::get('/pedidos', [PerfilController::class, 'pedidos'])->name('pedidos');
    Route::get('/favoritos', [PerfilController::class, 'favoritos'])->name('favoritos');
    Route::get('/avaliacoes', [PerfilController::class, 'avaliacoes'])->name('avaliacoes');
});

// Rotas de Avaliações (requer autenticação)
Route::middleware('auth')->group(function () {
    Route::post('/livros/{livro}/avaliacoes', [AvaliacaoController::class, 'store'])->name('avaliacoes.store');
    Route::get('/livros/{livro}/avaliacoes', [AvaliacaoController::class, 'index'])->name('avaliacoes.index');
    Route::post('/avaliacoes/{avaliacao}/util', [AvaliacaoController::class, 'marcarUtil'])->name('avaliacoes.util');
});

// Rotas Administrativas (requer autenticação e permissão de admin)
Route::middleware(['auth', 'admin'])->group(function () {
    
    // Rotas de Livros (CRUD)
    Route::resource('livros', LivroController::class);
    Route::get('/livros/{livro}/delete', [LivroController::class, 'confirmDelete'])->name('livros.delete');
    
    // API para busca de livros
    Route::get('/api/livros/search', [LivroController::class, 'searchApi'])->name('livros.search.api');
    
    // Rotas de Categorias (CRUD)
    Route::resource('categorias', CategoriaController::class);
    Route::get('/categorias/{categoria}/delete', [CategoriaController::class, 'confirmDelete'])->name('categorias.delete');
    
    // Rotas de Relatórios
    Route::prefix('relatorios')->name('relatorios.')->group(function () {
        Route::get('/', [RelatorioController::class, 'index'])->name('index');
        Route::get('/vendas', [RelatorioController::class, 'vendas'])->name('vendas');
        Route::get('/estoque', [RelatorioController::class, 'estoque'])->name('estoque');
        Route::get('/categorias', [RelatorioController::class, 'categorias'])->name('categorias');
    });
});

// Middleware personalizado para admin
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', function () {
        if (!auth()->user()->is_admin) {
            abort(403, 'Acesso negado. Apenas administradores podem acessar esta área.');
        }
        return redirect()->route('dashboard');
    })->name('admin');
});

// Rotas de fallback para compatibilidade
Route::get('/carrinho', function () {
    return redirect()->route('cart.index');
})->name('carrinho');

Route::get('/checkout', function () {
    return redirect()->route('checkout.index');
})->middleware('auth');