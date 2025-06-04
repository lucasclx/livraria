<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LivroController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\HomeController;

// Página inicial redireciona para o catálogo de livros
Route::get('/', function () {
    return redirect()->route('livros.index');
})->name('home');

// Rotas para Livros
Route::resource('livros', LivroController::class);

// Rotas para Categorias (se você quiser usar)
Route::resource('categorias', CategoriaController::class);
Route::get('categorias/{categoria}/delete', [CategoriaController::class, 'confirmDelete'])->name('categorias.delete');

// Dashboard (se estiver usando autenticação)
Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard')->middleware('auth');

// Autenticação (se estiver usando)
Auth::routes();