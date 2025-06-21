<?php

namespace App\Http\Controllers;

use App\Models\Livro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Toggle favorito (adicionar/remover)
     */
    public function toggle(Livro $livro)
    {
        $user = Auth::user();

        // Verificar se já está nos favoritos
        $isFavorited = $user->favorites()->where('livro_id', $livro->id)->exists();

        if ($isFavorited) {
            // Remover dos favoritos
            $user->favorites()->detach($livro->id);
            $favorited = false;
            $message = 'Livro removido dos favoritos';
        } else {
            // Adicionar aos favoritos
            $user->favorites()->attach($livro->id);
            $favorited = true;
            $message = 'Livro adicionado aos favoritos';
        }

        return response()->json([
            'success' => true,
            'favorited' => $favorited,
            'message' => $message
        ]);
    }

    /**
     * Toggle favorito por ID (rota alternativa)
     */
    public function toggleById($livroId)
    {
        $livro = Livro::findOrFail($livroId);
        return $this->toggle($livro);
    }

    /**
     * Listar favoritos do usuário
     */
    public function index()
    {
        $favoritos = Auth::user()->favorites()
            ->ativo()
            ->with('categoria')
            ->orderBy('favorites.created_at', 'desc')
            ->paginate(12);

        return view('loja.favoritos', compact('favoritos'));
    }

    /**
     * Remover favorito
     */
    public function remove(Livro $livro)
    {
        Auth::user()->favorites()->detach($livro->id);

        return redirect()->back()->with('success', 'Livro removido dos favoritos!');
    }

    /**
     * Verificar se livro está nos favoritos (API)
     */
    public function check(Livro $livro)
    {
        $isFavorited = Auth::user()->favorites()->where('livro_id', $livro->id)->exists();

        return response()->json([
            'favorited' => $isFavorited
        ]);
    }

    /**
     * Adicionar múltiplos livros aos favoritos
     */
    public function addMultiple(Request $request)
    {
        $request->validate([
            'livros' => 'required|array',
            'livros.*' => 'exists:livros,id'
        ]);

        $user = Auth::user();
        $livrosIds = $request->livros;

        // Filtrar apenas livros que ainda não estão nos favoritos
        $existingFavorites = $user->favorites()->pluck('livro_id')->toArray();
        $newFavorites = array_diff($livrosIds, $existingFavorites);

        if (!empty($newFavorites)) {
            $user->favorites()->attach($newFavorites);
        }

        return response()->json([
            'success' => true,
            'added' => count($newFavorites),
            'message' => count($newFavorites) . ' livro(s) adicionado(s) aos favoritos'
        ]);
    }

    /**
     * Limpar todos os favoritos
     */
    public function clear()
    {
        $count = Auth::user()->favorites()->count();
        Auth::user()->favorites()->detach();

        return response()->json([
            'success' => true,
            'removed' => $count,
            'message' => "Todos os {$count} favoritos foram removidos"
        ]);
    }
}