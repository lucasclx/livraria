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

// Rota home (dashboard)
Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');

// Rotas da Loja (públicas)
Route::prefix('loja')->name('loja.')->group(function () {
    Route::get('/', [LojaController::class, 'index'])->name('index');
    Route::get('/catalogo', [LojaController::class, 'catalogo'])->name('catalogo');
    Route::get('/categoria/{categoria}', [LojaController::class, 'categoria'])->name('categoria');
    Route::get('/livro/{livro}', [LojaController::class, 'detalhes'])->name('detalhes');
    Route::get('/favoritos', [LojaController::class, 'favoritos'])->name('favoritos')->middleware('auth');
    Route::get('/buscar', [LojaController::class, 'buscar'])->name('buscar');
});

// Rotas de Favoritos
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

// Rotas de Checkout
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CartController::class, 'processCheckout'])->name('checkout.process');
});

// Rotas de Pedidos
Route::middleware('auth')->prefix('orders')->name('orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/{order}', [OrderController::class, 'show'])->name('show');
});

// --- GRUPO DE ROTAS DO PERFIL CORRIGIDO ---
Route::middleware('auth')->prefix('perfil')->name('perfil.')->group(function () {
    Route::get('/', [PerfilController::class, 'index'])->name('index');
    Route::get('/editar', [PerfilController::class, 'editar'])->name('editar');
    Route::put('/atualizar', [PerfilController::class, 'atualizar'])->name('atualizar'); // Alterado para PUT e URL mais clara
    
    // Rota corrigida para corresponder à view
    Route::put('/alterar-senha', [PerfilController::class, 'alterarSenha'])->name('alterarSenha'); 
    
    // Rota adicionada para o formulário de preferências
    Route::post('/preferencias', [PerfilController::class, 'atualizarPreferencias'])->name('preferencias');

    Route::get('/pedidos', [PerfilController::class, 'pedidos'])->name('pedidos');
    Route::get('/favoritos', [PerfilController::class, 'favoritos'])->name('favoritos');
    Route::get('/avaliacoes', [PerfilController::class, 'avaliacoes'])->name('avaliacoes');
});

// Rotas de Avaliações
Route::middleware('auth')->group(function () {
    Route::post('/livros/{livro}/avaliacoes', [AvaliacaoController::class, 'store'])->name('avaliacoes.store');
    Route::get('/livros/{livro}/avaliacoes', [AvaliacaoController::class, 'index'])->name('avaliacoes.index');
    Route::post('/avaliacoes/{avaliacao}/util', [AvaliacaoController::class, 'marcarUtil'])->name('avaliacoes.util');
});

// Rotas Administrativas
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', function () { return redirect()->route('home'); })->name('admin.dashboard');
    Route::resource('livros', LivroController::class);
    Route::resource('categorias', CategoriaController::class);
    // ... outras rotas de admin
});