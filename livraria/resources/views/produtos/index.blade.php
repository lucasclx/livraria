{{-- resources/views/produtos/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Catálogo de Produtos')

@section('content')
<!-- Hero Section -->
<div class="row mb-5">
    <div class="col-12">
        <div class="text-center py-5 position-relative">
            <h1 class="page-title floating-shop">🛍️ Nossa Loja Virtual</h1>
            <p class="lead text-muted mt-3">
                Descubra produtos incríveis com os melhores preços
            </p>
        </div>
    </div>
</div>

<!-- Actions Bar -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <h2 class="mb-0">
            <i class="fas fa-shopping-bag text-primary me-2"></i>
            Todos os Produtos
        </h2>
        <span class="badge badge-category">
            {{ $produtos->total() ?? 0 }} produtos encontrados
        </span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('produtos.destaques') }}" class="btn btn-outline-warning">
            <i class="fas fa-star me-1"></i> Destaques
        </a>
        <a href="{{ route('produtos.promocoes') }}" class="btn btn-outline-danger">
            <i class="fas fa-fire me-1"></i> Promoções
        </a>
        <a href="{{ route('produtos.create') }}" class="btn btn-gold">
            <i class="fas fa-plus-circle me-2"></i>
            Adicionar Produto
        </a>
    </div>
</div>

<!-- Search and Filters -->
<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('produtos.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-1"></i> Buscar Produtos
                    </label>
                    <input type="text" name="busca" class="form-control" 
                           value="{{ request('busca') }}" 
                           placeholder="Digite o nome, marca ou categoria...">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-tags me-1"></i> Categoria
                    </label>
                    <select name="categoria" class="form-select">
                        <option value="">Todas</option>
                        @if(isset($categorias) && $categorias->count() > 0)
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria }}" 
                                        {{ request('categoria') == $categoria ? 'selected' : '' }}>
                                    {{ $categoria }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-copyright me-1"></i> Marca
                    </label>
                    <select name="marca" class="form-select">
                        <option value="">Todas</option>
                        @if(isset($marcas) && $marcas->count() > 0)
                            @foreach($marcas as $marca)
                                <option value="{{ $marca }}" 
                                        {{ request('marca') == $marca ? 'selected' : '' }}>
                                    {{ $marca }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-warehouse me-1"></i> Estoque
                    </label>
                    <select name="estoque" class="form-select">
                        <option value="">Todos</option>
                        <option value="disponivel" {{ request('estoque') == 'disponivel' ? 'selected' : '' }}>
                            ✅ Disponível
                        </option>
                        <option value="baixo" {{ request('estoque') == 'baixo' ? 'selected' : '' }}>
                            ⚠️ Estoque Baixo
                        </option>
                        <option value="sem_estoque" {{ request('estoque') == 'sem_estoque' ? 'selected' : '' }}>
                            ❌ Sem Estoque
                        </option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-sort me-1"></i> Ordenar
                    </label>
                    <select name="ordem" class="form-select">
                        <option value="nome" {{ request('ordem') == 'nome' ? 'selected' : '' }}>
                            🔤 Nome
                        </option>
                        <option value="preco" {{ request('ordem') == 'preco' ? 'selected' : '' }}>
                            💰 Preço
                        </option>
                        <option value="popularidade" {{ request('ordem') == 'popularidade' ? 'selected' : '' }}>
                            🔥 Popularidade
                        </option>
                        <option value="lancamento" {{ request('ordem') == 'lancamento' ? 'selected' : '' }}>
                            🆕 Lançamentos
                        </option>
                        <option value="avaliacao" {{ request('ordem') == 'avaliacao' ? 'selected' : '' }}>
                            ⭐ Avaliação
                        </option>
                    </select>
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <div class="d-grid w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Filtros Avançados -->
            <div class="row mt-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">💰 Preço Mínimo</label>
                    <input type="number" name="preco_min" class="form-control" 
                           value="{{ request('preco_min') }}" 
                           placeholder="R$ 0,00" step="0.01" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">💰 Preço Máximo</label>
                    <input type="number" name="preco_max" class="form-control" 
                           value="{{ request('preco_max') }}" 
                           placeholder="R$ 999,99" step="0.01" min="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Filtros</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="destaque" value="1" 
                               {{ request('destaque') ? 'checked' : '' }}>
                        <label class="form-check-label">⭐ Destaque</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">&nbsp;</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="promocao" value="1" 
                               {{ request('promocao') ? 'checked' : '' }}>
                        <label class="form-check-label">🔥 Promoção</label>
                    </div>
                </div>
            </div>
            
            @if(request()->hasAny(['busca', 'categoria', 'marca', 'estoque', 'ordem', 'preco_min', 'preco_max', 'destaque', 'promocao']))
                <div class="row mt-3">
                    <div class="col-12">
                        <a href="{{ route('produtos.index') }}" class="btn btn-outline-elegant btn-sm">
                            <i class="fas fa-times me-1"></i> Limpar Filtros
                        </a>
                        <span class="text-muted ms-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Filtros aplicados - {{ $produtos->total() ?? 0 }} resultado(s)
                        </span>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>

@if(isset($produtos) && $produtos->count() > 0)
    <!-- Products Grid -->
    <div class="row">
        @foreach($produtos as $produto)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card product-card h-100">
                <!-- Product Cover -->
                <div class="position-relative product-cover">
                    <img src="{{ $produto->imagem_url }}" class="card-img-top" alt="{{ $produto->nome }}" 
                         style="height: 250px; object-fit: cover;">
                    
                    <!-- Badges -->
                    <div class="position-absolute top-0 end-0 m-2">
                        @if($produto->destaque)
                            <span class="badge bg-warning text-dark mb-1 d-block">
                                ⭐ Destaque
                            </span>
                        @endif
                        @if($produto->tem_desconto)
                            <span class="badge bg-danger mb-1 d-block">
                                🔥 -{{ number_format($produto->desconto_percentual, 0) }}%
                            </span>
                        @endif
                        @php
                            $statusEstoque = $produto->status_estoque;
                        @endphp
                        <span class="badge badge-stock-{{ $statusEstoque['status'] == 'disponivel' ? 'ok' : ($statusEstoque['status'] == 'estoque_baixo' ? 'low' : 'out') }}">
                            {{ $statusEstoque['texto'] }}
                        </span>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="position-absolute top-0 start-0 m-2">
                        <button class="btn btn-sm btn-light rounded-circle" onclick="toggleFavorite({{ $produto->id }})"
                                data-bs-toggle="tooltip" title="Adicionar aos favoritos">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="card-body d-flex flex-column">
                    <div class="mb-auto">
                        <h6 class="card-title fw-bold mb-2" style="min-height: 3rem; line-height: 1.5;">
                            {{ Str::limit($produto->nome, 50) }}
                        </h6>
                        
                        @if($produto->marca)
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-copyright me-1"></i>
                                    <strong>{{ $produto->marca }}</strong>
                                </small>
                            </div>
                        @endif
                        
                        @if($produto->categoria)
                            <div class="mb-2">
                                <span class="badge badge-category small">
                                    {{ $produto->categoria }}
                                </span>
                            </div>
                        @endif
                        
                        @if($produto->descricao)
                            <p class="card-text small text-muted mb-3">
                                {{ Str::limit($produto->descricao, 100) }}
                            </p>
                        @endif
                    </div>
                    
                    <!-- Price Section -->
                    <div class="mt-auto">
                        <div class="price-section mb-2">
                            @if($produto->tem_desconto)
                                <div class="d-flex align-items-center gap-2">
                                    <span class="price fw-bold text-success">{{ $produto->preco_com_desconto_formatado }}</span>
                                    <small class="text-muted text-decoration-line-through">{{ $produto->preco_formatado }}</small>
                                </div>
                                <small class="text-success">
                                    💰 Economize {{ $produto->economia_formatada }}
                                </small>
                            @else
                                <span class="price fw-bold text-success">{{ $produto->preco_formatado }}</span>
                            @endif
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-boxes"></i> {{ $produto->estoque }} em estoque
                            </small>
                            @if($produto->avaliacao_media > 0)
                                <small class="text-warning">
                                    <i class="fas fa-star"></i> {{ number_format($produto->avaliacao_media, 1) }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Card Footer -->
                <div class="card-footer bg-transparent border-0 pt-0">
                    <div class="d-flex justify-content-between gap-1">
                        <a href="{{ route('produtos.show', $produto) }}" 
                           class="btn btn-outline-info btn-sm flex-fill" 
                           data-bs-toggle="tooltip" title="Ver detalhes">
                            <i class="fas fa-eye"></i>
                        </a>
                        
                        @if($produto->estoque > 0)
                            <form action="{{ route('cart.add', $produto) }}" method="POST" class="flex-fill">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm w-100" 
                                        data-bs-toggle="tooltip" title="Adicionar ao carrinho">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ route('produtos.edit', $produto) }}" 
                           class="btn btn-outline-warning btn-sm" 
                           data-bs-toggle="tooltip" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        
                        <form action="{{ route('produtos.destroy', $produto) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" 
                                    data-bs-toggle="tooltip" title="Excluir"
                                    onclick="return confirm('Tem certeza que deseja remover este produto?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $produtos->links() }}
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-search fa-5x text-muted mb-3"></i>
        <h4>Nenhum produto encontrado</h4>
        @if(request()->hasAny(['busca', 'categoria', 'marca', 'estoque', 'preco_min', 'preco_max']))
            <p class="text-muted">Tente ajustar os filtros de busca.</p>
            <a href="{{ route('produtos.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list"></i> Ver Todos os Produtos
            </a>
        @else
            <p class="text-muted">Comece adicionando o primeiro produto ao seu catálogo.</p>
            <a href="{{ route('produtos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Cadastrar Primeiro Produto
            </a>
        @endif
    </div>
@endif

<!-- Statistics Summary -->
@if(isset($produtos) && $produtos->total() > 0)
<div class="card mt-4 stats-card">
    <div class="card-header">
        <h5><i class="fas fa-chart-bar"></i> Resumo da Loja</h5>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-3">
                <div class="border-end">
                    <div class="stat-number">{{ $produtos->total() }}</div>
                    <small class="text-muted">Total de Produtos</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border-end">
                    <div class="stat-number">{{ \App\Models\Produto::sum('estoque') }}</div>
                    <small class="text-muted">Itens em Estoque</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border-end">
                    <div class="stat-number">{{ \App\Models\Produto::where('estoque', '<=', 5)->where('estoque', '>', 0)->count() }}</div>
                    <small class="text-muted">Estoque Baixo</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-number">
                    R$ {{ number_format(\App\Models\Produto::sum(\DB::raw('preco * estoque')), 2, ',', '.') }}
                </div>
                <small class="text-muted">Valor Total</small>
            </div>
        </div>
    </div>
</div>
@endif

<style>
.product-card {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.product-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(218, 165, 32, 0.3), transparent);
    transition: left 0.6s ease;
    z-index: 1;
}

.product-card:hover::before {
    left: 100%;
}

.product-card:hover {
    transform: translateY(-12px) scale(1.02);
    box-shadow: 0 25px 50px rgba(139, 69, 19, 0.25);
}

.product-cover {
    position: relative;
    overflow: hidden;
    border-radius: 8px 8px 0 0;
}

.price-section {
    min-height: 3rem;
}

.floating-shop {
    animation: shopFloat 6s ease-in-out infinite;
}

@keyframes shopFloat {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}
</style>

<!-- JavaScript for favorites and cart -->
<script>
function toggleFavorite(produtoId) {
    fetch('/produtos/' + produtoId + '/favorite', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const button = document.querySelector('[onclick="toggleFavorite(' + produtoId + ')"]');
        const icon = button.querySelector('i');
        if (data.favorited) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            icon.style.color = '#dc3545';
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            icon.style.color = '';
        }
    })
    .catch(error => {
        console.log('Erro ao favoritar produto:', error);
    });
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection