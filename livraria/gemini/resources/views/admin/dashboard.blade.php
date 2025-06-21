{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')
@section('title', 'Dashboard Administrativo')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title">📊 Dashboard Administrativo</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('livros.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Novo Livro
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card text-center">
            <div class="card-body">
                <i class="fas fa-book fa-3x text-primary mb-3"></i>
                <h3 class="stat-number">{{ $stats['total_livros'] }}</h3>
                <p class="text-muted mb-0">Total de Livros</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card text-center">
            <div class="card-body">
                <i class="fas fa-boxes fa-3x text-success mb-3"></i>
                <h3 class="stat-number">{{ $stats['livros_estoque'] }}</h3>
                <p class="text-muted mb-0">Em Estoque</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card text-center">
            <div class="card-body">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h3 class="stat-number">{{ $stats['estoque_baixo'] }}</h3>
                <p class="text-muted mb-0">Estoque Baixo</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stats-card text-center">
            <div class="card-body">
                <i class="fas fa-dollar-sign fa-3x text-info mb-3"></i>
                <h3 class="stat-number">R$ {{ number_format($stats['valor_estoque'], 0, ',', '.') }}</h3>
                <p class="text-muted mb-0">Valor do Estoque</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Estoque Baixo -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-exclamation-triangle text-warning me-2"></i>Estoque Baixo</h5>
                <a href="{{ route('livros.index', ['estoque' => 'baixo']) }}" class="btn btn-sm btn-outline-warning">Ver Todos</a>
            </div>
            <div class="card-body">
                @if($estoqueBaixo->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Livro</th>
                                    <th>Categoria</th>
                                    <th class="text-center">Estoque</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estoqueBaixo as $livro)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($livro->imagem)
                                                <img src="{{ $livro->imagem_url }}" class="me-2" style="width: 40px; height: 50px; object-fit: cover;">
                                            @endif
                                            <div>
                                                <strong>{{ Str::limit($livro->titulo, 30) }}</strong><br>
                                                <small class="text-muted">{{ $livro->autor }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($livro->categoria)
                                            <span class="badge bg-secondary">{{ $livro->categoria->nome }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning">{{ $livro->estoque }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('livros.edit', $livro) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-check-circle fa-3x mb-2"></i>
                        <p>Nenhum livro com estoque baixo!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Produtos Sem Estoque -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-ban text-danger me-2"></i>Sem Estoque</h5>
                <a href="{{ route('livros.index', ['estoque' => 'sem_estoque']) }}" class="btn btn-sm btn-outline-danger">Ver Todos</a>
            </div>
            <div class="card-body">
                @if($semEstoque->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Livro</th>
                                    <th>Categoria</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($semEstoque as $livro)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($livro->imagem)
                                                <img src="{{ $livro->imagem_url }}" class="me-2" style="width: 40px; height: 50px; object-fit: cover;">
                                            @endif
                                            <div>
                                                <strong>{{ Str::limit($livro->titulo, 30) }}</strong><br>
                                                <small class="text-muted">{{ $livro->autor }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($livro->categoria)
                                            <span class="badge bg-secondary">{{ $livro->categoria->nome }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('livros.edit', $livro) }}" class="btn btn-sm btn-outline-primary" title="Repor Estoque">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-check-circle fa-3x mb-2"></i>
                        <p>Todos os livros têm estoque!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Ações Rápidas -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-bolt me-2"></i>Ações Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="{{ route('livros.index') }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-book fa-2x mb-2 d-block"></i>
                            Gerenciar Livros
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="{{ route('categorias.index') }}" class="btn btn-outline-success w-100">
                            <i class="fas fa-tags fa-2x mb-2 d-block"></i>
                            Gerenciar Categorias
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-info w-100">
                            <i class="fas fa-shopping-cart fa-2x mb-2 d-block"></i>
                            Ver Pedidos
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="{{ route('loja.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-store fa-2x mb-2 d-block"></i>
                            Ver Loja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stats-card {
    transition: transform 0.3s ease;
    border: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.stats-card:hover {
    transform: translateY(-5px);
}
.stat-number {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    color: var(--primary-brown);
}
</style>
@endsection