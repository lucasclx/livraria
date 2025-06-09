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

// resources/views/perfil/index.blade.php - Página principal do perfil

@extends('layouts.app')
@section('title', 'Meu Perfil - Biblioteca Literária')

@section('content')
<div class="perfil-container">
    <!-- Header do Perfil -->
    <div class="perfil-header bg-gradient-primary text-white rounded-3 p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-md-2">
                <div class="avatar-container">
                    <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fas fa-user fa-3x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-10">
                <h2 class="mb-2">Olá, {{ $user->name }}! 👋</h2>
                <p class="mb-3 opacity-75">
                    <i class="fas fa-envelope me-2"></i>{{ $user->email }}
                    @if($user->telefone)
                        <i class="fas fa-phone ms-3 me-2"></i>{{ $user->telefone }}
                    @endif
                </p>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('perfil.editar') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-edit me-1"></i>Editar Perfil
                    </a>
                    <a href="{{ route('perfil.pedidos') }}" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-shopping-bag me-1"></i>Meus Pedidos
                    </a>
                    <a href="{{ route('perfil.favoritos') }}" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-heart me-1"></i>Favoritos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon text-primary mb-3">
                        <i class="fas fa-shopping-bag fa-2x"></i>
                    </div>
                    <h3 class="stat-number text-primary">{{ $stats['total_pedidos'] }}</h3>
                    <p class="text-muted mb-0">Pedidos Realizados</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon text-success mb-3">
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                    <h3 class="stat-number text-success">R$ {{ number_format($stats['valor_total_gasto'], 2, ',', '.') }}</h3>
                    <p class="text-muted mb-0">Total Gasto</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon text-danger mb-3">
                        <i class="fas fa-heart fa-2x"></i>
                    </div>
                    <h3 class="stat-number text-danger">{{ $stats['livros_favoritos'] }}</h3>
                    <p class="text-muted mb-0">Livros Favoritos</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon text-warning mb-3">
                        <i class="fas fa-star fa-2x"></i>
                    </div>
                    <h3 class="stat-number text-warning">{{ $stats['avaliacoes_feitas'] }}</h3>
                    <p class="text-muted mb-0">Avaliações Feitas</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Últimos Pedidos -->
        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>Últimos Pedidos
                    </h5>
                    <a href="{{ route('perfil.pedidos') }}" class="btn btn-sm btn-outline-primary">Ver Todos</a>
                </div>
                <div class="card-body">
                    @if($ultimosPedidos->count() > 0)
                        @foreach($ultimosPedidos as $pedido)
                        <div class="pedido-item border-bottom py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Pedido #{{ $pedido->order_number ?? $pedido->id }}</h6>
                                    <p class="text-muted small mb-1">
                                        {{ $pedido->created_at->format('d/m/Y H:i') }}
                                    </p>
                                    <span class="badge bg-{{ $pedido->status == 'delivered' ? 'success' : ($pedido->status == 'cancelled' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($pedido->status) }}
                                    </span>
                                </div>
                                <div class="text-end">
                                    <strong class="text-success">R$ {{ number_format($pedido->total, 2, ',', '.') }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pedido->cart->items->count() }} {{ $pedido->cart->items->count() == 1 ? 'item' : 'itens' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <h6>Nenhum pedido realizado</h6>
                            <p class="text-muted">Que tal começar explorando nosso catálogo?</p>
                            <a href="{{ route('loja.catalogo') }}" class="btn btn-primary">
                                <i class="fas fa-book me-1"></i>Explorar Livros
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Livros Favoritos -->
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-heart me-2"></i>Livros Favoritos
                    </h5>
                    <a href="{{ route('perfil.favoritos') }}" class="btn btn-sm btn-outline-danger">Ver Todos</a>
                </div>
                <div class="card-body">
                    @if($favoritos->count() > 0)
                        <div class="row">
                            @foreach($favoritos as $livro)
                            <div class="col-6 mb-3">
                                <div class="favorito-item">
                                    <a href="{{ route('loja.detalhes', $livro) }}" class="text-decoration-none">
                                        <div class="livro-thumb mb-2">
                                            @if($livro->imagem)
                                                <img src="{{ $livro->imagem_url }}" 
                                                     class="img-fluid rounded" 
                                                     style="height: 120px; width: 100%; object-fit: cover;"
                                                     alt="{{ $livro->titulo }}">
                                            @else
                                                <div class="placeholder-thumb bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="height: 120px;">
                                                    <i class="fas fa-book fa-2x text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <h6 class="small text-dark mb-1">{{ Str::limit($livro->titulo, 30) }}</h6>
                                        <p class="text-muted small mb-0">{{ $livro->autor }}</p>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                            <h6>Nenhum favorito ainda</h6>
                            <p class="text-muted small">Favorite livros para vê-los aqui</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-bolt me-2"></i>Ações Rápidas
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-6 mb-3">
                    <a href="{{ route('loja.catalogo') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                        <i class="fas fa-book fa-2x mb-2"></i>
                        <span>Explorar Livros</span>
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <a href="{{ route('cart.index') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                        <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                        <span>Meu Carrinho</span>
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <a href="{{ route('perfil.avaliacoes') }}" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                        <i class="fas fa-star fa-2x mb-2"></i>
                        <span>Minhas Avaliações</span>
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <button class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3" 
                            data-bs-toggle="modal" data-bs-target="#suporteModal">
                        <i class="fas fa-headset fa-2x mb-2"></i>
                        <span>Suporte</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Suporte -->
<div class="modal fade" id="suporteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-headset me-2"></i>Precisa de Ajuda?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="d-grid">
                            <a href="mailto:suporte@bibliotecaliteraria.com" class="btn btn-outline-primary">
                                <i class="fas fa-envelope me-2"></i>
                                Enviar Email
                            </a>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="d-grid">
                            <a href="https://wa.me/5511999999999" target="_blank" class="btn btn-outline-success">
                                <i class="fab fa-whatsapp me-2"></i>
                                WhatsApp
                            </a>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-grid">
                            <button class="btn btn-outline-info">
                                <i class="fas fa-comment me-2"></i>
                                Chat Online
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.perfil-header {
    background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-info) 100%);
}

.avatar-placeholder {
    width: 80px;
    height: 80px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
}

.stats-card {
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
}

.stat-number {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
}

.pedido-item:last-child {
    border-bottom: none !important;
}

.favorito-item:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}

.livro-thumb {
    overflow: hidden;
    border-radius: 8px;
}
</style>
@endsection 