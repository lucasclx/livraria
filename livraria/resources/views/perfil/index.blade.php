@extends('layouts.app')
@section('title', 'Meu Perfil - Livraria Mil Páginas')

@section('content')
<div class="perfil-container">
    <div class="perfil-header bg-gradient-primary text-white rounded-3 p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <div class="avatar-container d-inline-block">
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
        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Últimos Pedidos</h5>
                    <a href="{{ route('perfil.pedidos') }}" class="btn btn-sm btn-outline-primary">Ver Todos</a>
                </div>
                <div class="card-body">
                    @forelse($ultimosPedidos as $pedido)
                        <div class="pedido-item @if(!$loop->last) border-bottom @endif py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">Pedido #{{ $pedido->order_number ?? $pedido->id }}</h6>
                                    <p class="text-muted small mb-1">{{ $pedido->created_at->format('d/m/Y H:i') }}</p>
                                    <span class="badge bg-{{ $pedido->status == 'delivered' ? 'success' : ($pedido->status == 'cancelled' ? 'danger' : 'warning') }}">{{ ucfirst($pedido->status) }}</span>
                                </div>
                                <div class="text-end">
                                    <strong class="text-success">R$ {{ number_format($pedido->total, 2, ',', '.') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $pedido->cart->items->count() }} {{ Str::plural('item', $pedido->cart->items->count()) }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <h6>Nenhum pedido realizado</h6>
                            <p class="text-muted">Que tal começar explorando nosso catálogo?</p>
                            <a href="{{ route('loja.catalogo') }}" class="btn btn-primary"><i class="fas fa-book me-1"></i>Explorar Livros</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-heart me-2"></i>Livros Favoritos</h5>
                    <a href="{{ route('perfil.favoritos') }}" class="btn btn-sm btn-outline-danger">Ver Todos</a>
                </div>
                <div class="card-body">
                    @forelse($favoritos as $livro)
                        <a href="{{ route('loja.detalhes', $livro) }}" class="favorito-item d-flex align-items-center text-decoration-none text-dark mb-3">
                            <img src="{{ $livro->imagem_url ?? asset('images/placeholder_livro.png') }}" class="rounded me-3" style="width: 50px; height: 70px; object-fit: cover;" alt="{{ $livro->titulo }}">
                            <div>
                                <h6 class="small mb-0">{{ Str::limit($livro->titulo, 35) }}</h6>
                                <p class="text-muted small mb-0">{{ $livro->autor }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                            <h6>Nenhum favorito ainda</h6>
                            <p class="text-muted small">Favorite livros para vê-los aqui</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.perfil-header { background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-info) 100%); }
.avatar-placeholder { width: 80px; height: 80px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); }
.stats-card { transition: transform 0.3s ease; }
.stats-card:hover { transform: translateY(-5px); }
.stat-number { font-family: 'Inter', sans-serif; font-weight: 700; }
.favorito-item:hover h6 { color: var(--bs-primary) !important; }
</style>
@endsection