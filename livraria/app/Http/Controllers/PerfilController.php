<?php
// app/Http/Controllers/PerfilController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\Order;

class PerfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        // Estatísticas do usuário
        $stats = [
            'total_pedidos' => $user->orders()->count(),
            'valor_total_gasto' => $user->orders()->where('status', '!=', 'cancelled')->sum('total'),
            'livros_favoritos' => $user->favorites()->count(),
            'avaliacoes_feitas' => $user->avaliacoes()->count(),
        ];
        
        // Últimos pedidos
        $ultimosPedidos = $user->orders()
            ->with('cart.items.livro')
            ->latest()
            ->limit(5)
            ->get();
        
        // Livros favoritos
        $favoritos = $user->favorites()
            ->with('categoria')
            ->latest()
            ->limit(6)
            ->get();

        return view('perfil.index', compact('user', 'stats', 'ultimosPedidos', 'favoritos'));
    }

    public function editar()
    {
        return view('perfil.editar', ['user' => Auth::user()]);
    }

    public function atualizar(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'telefone' => 'nullable|string|max:20',
            'data_nascimento' => 'nullable|date|before:today',
            'genero' => 'nullable|in:masculino,feminino,outro,prefiro_nao_informar',
        ]);

        $user->update($validated);

        return redirect()->route('perfil.index')
            ->with('success', 'Perfil atualizado com sucesso!');
    }

    public function alterarSenha(Request $request)
    {
        $request->validate([
            'senha_atual' => 'required',
            'nova_senha' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->senha_atual, $user->password)) {
            return back()->withErrors(['senha_atual' => 'A senha atual está incorreta.']);
        }

        $user->update([
            'password' => Hash::make($request->nova_senha)
        ]);

        return redirect()->route('perfil.index')
            ->with('success', 'Senha alterada com sucesso!');
    }

    public function pedidos()
    {
        $pedidos = Auth::user()->orders()
            ->with(['cart.items.livro'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('perfil.pedidos', compact('pedidos'));
    }

    public function favoritos()
    {
        $favoritos = Auth::user()->favorites()
            ->with('categoria')
            ->paginate(12);

        return view('perfil.favoritos', compact('favoritos'));
    }

    public function avaliacoes()
    {
        $avaliacoes = Auth::user()->avaliacoes()
            ->with('livro')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('perfil.avaliacoes', compact('avaliacoes'));
    }
}