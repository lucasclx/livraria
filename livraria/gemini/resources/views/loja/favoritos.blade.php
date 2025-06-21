@extends('layouts.app')

@section('title', 'Meus Favoritos - Livraria Mil Páginas')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('loja.index') }}">Início</a></li>
            <li class="breadcrumb-item active" aria-current="page">Meus Favoritos</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="favoritos-header text-center py-4 bg-gradient-danger text-white rounded">
                <h1 class="display-6 mb-3">
                    <i class="fas fa-heart me-2"></i>
                    Meus Livros Favoritos
                </h1>
                <p class="lead mb-0">
                    @if($favoritos->total() > 0)
                        Você tem {{ $favoritos->total() }} {{ $favoritos->total() == 1 ? 'livro favoritado' : 'livros favoritados' }}
                    @else
                        Você ainda não favoritou nenhum livro
                    @endif
                </p>
            </div>
        </div>
    </div>

    @if($favoritos->count() > 0)
        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <span class="text-muted me-3">Ordenar por:</span>
                    <select class="form-select form-select-sm" style="width: auto;" onchange="ordenarFavoritos(this.value)">
                        <option value="recente">Mais recentes</option>
                        <option value="titulo-asc">Nome (A-Z)</option>
                        <option value="titulo-desc">Nome (Z-A)</option>
                        <option value="preco-asc">Menor preço</option>
                        <option value="preco-desc">Maior preço</option>
                        <option value="categoria">Por categoria</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <span class="text-muted">
                    {{ $favoritos->total() }} {{ $favoritos->total() == 1 ? 'favorito' : 'favoritos' }}
                </span>
            </div>
        </div>

        <!-- Lista de Favoritos -->
        <div class="row">
            @foreach($favoritos as $livro)
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="favorito-item">
                        @include('components.livro-card', ['livro' => $livro])
                        
                        <!-- Data de favoritação -->
                        <div class="text-center mt-2">
                            <small class="text-muted">
                                <i class="fas fa-heart text-danger me-1"></i>
                                Favoritado em {{ $livro->pivot->created_at->format('d/m/Y') }}
                            </small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginação -->
        @if($favoritos->hasPages())
            <div class="row mt-5">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        {{ $favoritos->links() }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Ações em lote -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-light border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">Ações rápidas</h5>
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <button class="btn btn-outline-primary btn-sm" onclick="adicionarTodosAoCarrinho()">
                                <i class="fas fa-shopping-cart me-1"></i>
                                Adicionar Disponíveis ao Carrinho
                            </button>
                            <a href="{{ route('loja.catalogo') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-search me-1"></i>
                                Descobrir Mais Livros
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- Nenhum favorito -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-heart fa-5x text-muted opacity-50"></i>
                    </div>
                    <h3 class="text-muted mb-3">Sua lista de favoritos está vazia</h3>
                    <p class="text-muted mb-4">
                        Comece a favoritar livros para criar sua biblioteca pessoal de preferências!
                    </p>
                    
                    <!-- Como favoritar -->
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Como favoritar um livro?</h5>
                            <p class="text-muted mb-3">
                                É muito fácil! Clique no ícone <i class="fas fa-heart text-danger"></i> 
                                que aparece nos livros para adicioná-los aos seus favoritos.
                            </p>
                            <div class="d-flex gap-2 justify-content-center">
                                <span class="badge bg-primary">1. Encontre um livro</span>
                                <span class="badge bg-secondary">2. Clique no ❤️</span>
                                <span class="badge bg-success">3. Pronto!</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="{{ route('loja.catalogo') }}" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Explorar Catálogo
                        </a>
                        <a href="{{ route('loja.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Voltar ao Início
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Recomendações baseadas nos favoritos -->
    @if($favoritos->count() > 0)
        @php
            $categoriasIds = $favoritos->pluck('categoria_id')->filter()->unique();
            $recomendacoes = \App\Models\Livro::ativo()
                ->emEstoque()
                ->whereIn('categoria_id', $categoriasIds)
                ->whereNotIn('id', $favoritos->pluck('id'))
                ->with('categoria')
                ->inRandomOrder()
                ->limit(4)
                ->get();
        @endphp

        @if($recomendacoes->count() > 0)
            <hr class="my-5">
            <div class="row">
                <div class="col-12">
                    <h3 class="text-center mb-4">
                        <i class="fas fa-magic text-primary me-2"></i>
                        Recomendações para Você
                    </h3>
                    <p class="text-center text-muted mb-4">
                        Baseado nos seus livros favoritos
                    </p>
                    <div class="row">
                        @foreach($recomendacoes as $livro)
                            <div class="col-lg-3 col-md-6 mb-4">
                                @include('components.livro-card', ['livro' => $livro])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

@push('scripts')
<script>
function ordenarFavoritos(valor) {
    const url = new URL(window.location);
    
    switch(valor) {
        case 'recente':
            url.searchParams.delete('ordem');
            url.searchParams.delete('direcao');
            break;
        case 'titulo-asc':
            url.searchParams.set('ordem', 'titulo');
            url.searchParams.set('direcao', 'asc');
            break;
        case 'titulo-desc':
            url.searchParams.set('ordem', 'titulo');
            url.searchParams.set('direcao', 'desc');
            break;
        case 'preco-asc':
            url.searchParams.set('ordem', 'preco');
            url.searchParams.set('direcao', 'asc');
            break;
        case 'preco-desc':
            url.searchParams.set('ordem', 'preco');
            url.searchParams.set('direcao', 'desc');
            break;
        case 'categoria':
            url.searchParams.set('ordem', 'categoria');
            url.searchParams.set('direcao', 'asc');
            break;
    }
    
    url.searchParams.delete('page');
    window.location = url.toString();
}

function adicionarTodosAoCarrinho() {
    const livrosDisponiveis = document.querySelectorAll('[data-livro-id][data-em-estoque="true"]');
    
    if (livrosDisponiveis.length === 0) {
        alert('Nenhum livro disponível em estoque para adicionar ao carrinho.');
        return;
    }
    
    if (!confirm(`Deseja adicionar ${livrosDisponiveis.length} livro(s) ao carrinho?`)) {
        return;
    }
    
    let adicionados = 0;
    let total = livrosDisponiveis.length;
    
    livrosDisponiveis.forEach((elemento, index) => {
        const livroId = elemento.getAttribute('data-livro-id');
        
        setTimeout(() => {
            fetch(`/cart/add/${livroId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ quantity: 1 })
            })
            .then(response => {
                adicionados++;
                if (adicionados === total) {
                    alert(`${adicionados} livro(s) adicionado(s) ao carrinho!`);
                    // Atualizar contador do carrinho se existir
                    if (typeof updateCartCount === 'function') {
                        updateCartCount();
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao adicionar livro:', error);
            });
        }, index * 100); // Delay entre requisições
    });
}
</script>
@endpush

@push('styles')
<style>
.favorito-item {
    transition: transform 0.2s ease;
}

.favorito-item:hover {
    transform: translateY(-5px);
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

@media (max-width: 768px) {
    .d-flex.gap-2 {
        flex-direction: column;
    }
    
    .d-flex.gap-2 > * {
        margin-bottom: 0.5rem;
    }
}
</style>
@endpush
@endsection