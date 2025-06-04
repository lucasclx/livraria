<?php

namespace App\Http\Controllers;

use App\Models\Livro;
use Illuminate\Support\Facades\Auth;


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LivroController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FavoriteController;


class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function toggle(Livro $livro)
    {
        $user = Auth::user();

        if ($user->favorites()->where('livro_id', $livro->id)->exists()) {
            $user->favorites()->detach($livro->id);
            $favorited = false;
        } else {
            $user->favorites()->attach($livro->id);
            $favorited = true;
        }

        return response()->json(['favorited' => $favorited]);
    }
}
