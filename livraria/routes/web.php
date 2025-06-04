<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LivroController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;


// Página inicial
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rotas para Livros
Route::resource('livros', LivroController::class);
Route::post('livros/{livro}/favorite', [FavoriteController::class, 'toggle'])->name('livros.favorite');

// Rotas para Categorias (se você quiser usar)
Route::resource('categorias', CategoriaController::class);
Route::get('categorias/{categoria}/delete', [CategoriaController::class, 'confirmDelete'])->name('categorias.delete');

// Carrinho de compras
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{livro}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/item/{item}/update', [CartController::class, 'update'])->name('cart.item.update');
Route::post('/cart/item/{item}/remove', [CartController::class, 'remove'])->name('cart.item.remove');
Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
Route::post('/checkout', [CartController::class, 'processCheckout'])->name('checkout.process');

// Dashboard (se estiver usando autenticação)
Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard')->middleware('auth');

// Autenticação (se estiver usando)
Auth::routes();