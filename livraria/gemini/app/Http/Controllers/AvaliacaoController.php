<?php

namespace App\Http\Controllers;

use App\Models\AvaliacaoLivro;
use App\Models\Livro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvaliacaoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, Livro $livro)
    {
        $request->validate([
            'nota' => 'required|integer|min:1|max:5',
            'titulo' => 'nullable|string|max:100',
            'comentario' => 'required|string|min:10|max:1000',
            'recomenda' => 'boolean'
        ]);

        // Verificar se usuário já avaliou
        $avaliacaoExistente = AvaliacaoLivro::where('livro_id', $livro->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($avaliacaoExistente) {
            return redirect()->back()->with('error', 'Você já avaliou este livro.');
        }

        AvaliacaoLivro::create([
            'livro_id' => $livro->id,
            'user_id' => Auth::id(),
            'nota' => $request->nota,
            'titulo' => $request->titulo,
            'comentario' => $request->comentario,
            'recomenda' => $request->boolean('recomenda', true)
        ]);

        return redirect()->back()->with('success', 'Avaliação publicada com sucesso!');
    }

    public function marcarUtil(Request $request, AvaliacaoLivro $avaliacao)
    {
        $avaliacao->marcarUtil($request->boolean('util'));
        
        return response()->json(['success' => true]);
    }

    public function index(Livro $livro)
    {
        $avaliacoes = $livro->avaliacoes()
            ->with('user')
            ->orderBy('util_positivo', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $estatisticas = [
            'total' => $livro->total_avaliacoes,
            'media' => $livro->avaliacao_media,
            'distribuicao' => []
        ];

        for ($i = 5; $i >= 1; $i--) {
            $count = $livro->avaliacoes()->where('nota', $i)->count();
            $porcentagem = $livro->total_avaliacoes > 0 
                ? round(($count / $livro->total_avaliacoes) * 100) 
                : 0;
            
            $estatisticas['distribuicao'][$i] = [
                'count' => $count,
                'porcentagem' => $porcentagem
            ];
        }

        return view('avaliacoes.index', compact('livro', 'avaliacoes', 'estatisticas'));
    }
}