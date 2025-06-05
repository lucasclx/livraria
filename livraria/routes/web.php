<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;

// Página inicial - redireciona para o catálogo
Route::get('/', function () {
    return redirect()->route('produtos.index');
})->name('home');

// Rotas para Produtos
Route::resource('produtos', ProdutoController::class);

// Rotas especiais para produtos
Route::get('/destaques', [ProdutoController::class, 'destaques'])->name('produtos.destaques');
Route::get('/promocoes', [ProdutoController::class, 'promocoes'])->name('produtos.promocoes');
Route::get('/categoria/{categoria}', [ProdutoController::class, 'categoria'])->name('produtos.categoria');

// Favoritos (requer autenticação)
Route::post('produtos/{produto}/favorite', [FavoriteController::class, 'toggle'])
    ->name('produtos.favorite')
    ->middleware('auth');

// Carrinho de compras
Route::prefix('carrinho')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/adicionar/{produto}', [CartController::class, 'add'])->name('add');
    Route::post('/item/{item}/atualizar', [CartController::class, 'update'])->name('item.update');
    Route::delete('/item/{item}', [CartController::class, 'remove'])->name('item.remove');
    Route::post('/limpar', [CartController::class, 'clear'])->name('clear');
});

// Checkout (requer autenticação)
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CartController::class, 'processCheckout'])->name('checkout.process');
    
    // Pedidos do usuário
    Route::get('/meus-pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pedido/{order}', [OrderController::class, 'show'])->name('orders.show');
    
    // Favoritos
    Route::get('/favoritos', [FavoriteController::class, 'index'])->name('favorites.index');
    
    // Dashboard administrativo
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
});

// Autenticação
Auth::routes();

// Rotas API para AJAX
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/produtos/buscar', [ProdutoController::class, 'buscarAjax'])->name('produtos.buscar');
    Route::get('/categorias', function() {
        return \App\Models\Produto::select('categoria')
            ->whereNotNull('categoria')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');
    })->name('categorias');
    
    Route::get('/marcas', function() {
        return \App\Models\Produto::select('marca')
            ->whereNotNull('marca')
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca');
    })->name('marcas');
});