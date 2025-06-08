<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LivroController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\LojaController;
use Illuminate\Support\Facades\Auth;

// Página inicial da loja
Route::get('/', [LojaController::class, 'index'])->name('loja.index');

// Rotas da loja pública
Route::group(['prefix' => 'loja', 'as' => 'loja.'], function() {
    Route::get('/catalogo', [LojaController::class, 'catalogo'])->name('catalogo');
    Route::get('/categoria/{categoria}', [LojaController::class, 'categoria'])->name('categoria');
    Route::get('/buscar', [LojaController::class, 'buscar'])->name('buscar');
    Route::get('/livro/{livro}', [LojaController::class, 'detalhes'])->name('detalhes');
    Route::get('/favoritos', [LojaController::class, 'favoritos'])->middleware('auth')->name('favoritos');
});

// Rotas administrativas para Livros (protegidas por autenticação)
Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function() {
    Route::resource('livros', LivroController::class);
    Route::resource('categorias', CategoriaController::class);
    Route::get('categorias/{categoria}/delete', [CategoriaController::class, 'confirmDelete'])->name('categorias.delete');
});

// Rotas para funcionalidades de usuário
Route::post('livros/{livro}/favorite', [FavoriteController::class, 'toggle'])->name('livros.favorite');

// Carrinho de compras
Route::group(['prefix' => 'carrinho', 'as' => 'cart.'], function() {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/adicionar/{livro}', [CartController::class, 'add'])->name('add');
    Route::post('/adicionar-rapido/{livro}', [CartController::class, 'quickAdd'])->name('quick-add');
    Route::post('/item/{item}/atualizar', [CartController::class, 'update'])->name('item.update');
    Route::post('/item/{item}/remover', [CartController::class, 'remove'])->name('item.remove');
    Route::post('/limpar', [CartController::class, 'clear'])->name('clear');
    Route::get('/contador', [CartController::class, 'getCartCount'])->name('count');
});

// Checkout
Route::group(['middleware' => 'auth'], function() {
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CartController::class, 'processCheckout'])->name('checkout.process');
    Route::get('/pedidos', [OrderController::class, 'index'])->name('orders.index');
});

// Dashboard administrativo
Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard')->middleware('auth');

// Rotas de autenticação
Auth::routes();

// API Routes para AJAX
Route::group(['prefix' => 'api', 'middleware' => 'web'], function() {
    Route::post('/cart/quick-add/{livro}', [CartController::class, 'quickAdd'])->name('api.cart.quick-add');
    Route::get('/livros/search', [LivroController::class, 'searchApi'])->name('api.livros.search');
    Route::get('/categorias/popular', [LojaController::class, 'categoriasPopulares'])->name('api.categorias.popular');
});

// Redirecionamento para compatibilidade
Route::redirect('/home', '/dashboard');
Route::redirect('/livros', '/loja/catalogo');