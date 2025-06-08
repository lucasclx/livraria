@extends('layouts.app')
@section('title', 'Catálogo - Biblioteca Literária')

@section('content')
<style>
    .filter-sidebar {
        background: rgba(245, 245, 220, 0.8);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 1.5rem;
        border: 1px solid rgba(139, 69, 19, 0.1);
        position: sticky;
        top: 2rem;
    }
    
    .livro-grid {
        gap: 1.5rem;
    }
    
    .livro-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(139, 69, 19, 0.1);
        background: white;
    }
    
    .livro-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(139, 69, 19, 0.2);
    }
    
    .livro-image {
        height: 280px;
        object-fit: cover;
        width: 100%;
        transition: transform 0.3s ease;
    }
    
    .livro-card:hover .livro-image {
        transform: scale(1.05);
    }
    
    .price-range-slider {
        -webkit-appearance: none;
        width: 100%;
        height: 5px;
        border-radius: 5px;
        background: #ddd;
        outline: none;
    }
    
    .price-range-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--primary-brown);
        cursor: pointer;
    }
    
    .search-header {
        background: linear-gradient(135deg, var(--aged-paper) 0%, var(--cream) 100%);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .view-toggle {
        background: white;
        border-radius: 25px;
        padding: 0.25rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .view-toggle .btn {
        border-radius: 20px;
        border: none;
        padding: 0.5rem 1rem;
    }
    
    .category-chip {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, var(--light-brown) 0%, var(--primary-brown) 100%);
        color: white;
        border-radius: 25px;
        text-decoration: none;
        margin: 0.25rem;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }
    
    .category-chip:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(139, 69, 19, 0.3);
        color: white;
    }
    
    .results-info {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid var(--gold);
    }
</style>

<!-- Header de Busca -->
<div class="search-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="page-title mb-3">
                <i class="fas fa-search text-primary me-2"></i>
                Explore Nosso Catálogo
            </h1>
            <p class="lead text-muted mb-0">
                Descubra entre {{ $livros->total() ?? 0 }} livros o seu próximo grande encontro literário.
            </p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <div class="d-flex flex-column">
                <span class="h4 text-success mb-0">{{ $livros->total() ?? 0 }}</span>
                <small class="text-muted">livros encontrados</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sidebar de Filtros -->
    <div class="col-lg-3">
        <div class="filter-sidebar">
            <h5 class="fw-bold mb-3">
                <i class="fas fa-filter me-2"></i>Filtrar Livros
            </h5>
            
            <form method="GET" action="{{ route('loja.catalogo') }}" id="filterForm">
                <!-- Busca por Texto -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-1"></i>Buscar
                    </label>
                    <input type="text" name="busca" class="form-control" 
                           value="{{ request('busca') }}" 
                           placeholder="Título, autor ou ISBN...">
                </div>
                
                <!-- Filtro por Categoria -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-tags me-1"></i>Categoria
                    </label>
                    <select name="categoria" class="form-select">
                        <option value="">Todas as categorias</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria }}" 
                                    {{ request('categoria') == $categoria ? 'selected' : '' }}>
                                {{ $categoria }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Filtro por Preço -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-dollar-sign me-1"></i>Faixa de Preço
                    </label>
                    <div class="row">
                        <div class="col-6">
                            <input type="number" name="preco_min" class="form-control form-control-sm" 
                                   placeholder="Min" value="{{ request('preco_min') }}" min="0" step="0.01">
                        </div>
                        <div class="col-6">
                            <input type="number" name="preco_max" class="form-control form-control-sm" 
                                   placeholder="Max" value="{{ request('preco_max') }}" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Faixas populares:</small><br>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setPrice(0, 25)">Até R$ 25</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setPrice(25, 50)">R$ 25-50</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setPrice(50, 100)">R$ 50-100</button>
                        </div>
                    </div>
                </div>
                
                <!-- Disponibilidade -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-warehouse me-1"></i>Disponibilidade
                    </label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="disponivel" value="1" 
                               {{ request('disponivel') ? 'checked' : '' }} id="disponivel">
                        <label class="form-check-label" for="disponivel">
                            Apenas livros em estoque
                        </label>
                    </div>
                </div>
                
                <!-- Ordenação -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-sort me-1"></i>Ordenar por
                    </label>
                    <select name="ordem" class="form-select">
                        <option value="titulo" {{ request('ordem') == 'titulo' ? 'selected' : '' }}>Título (A-Z)</option>
                        <option value="titulo_desc" {{ request('ordem') == 'titulo_desc' ? 'selected' : '' }}>Título (Z-A)</option>
                        <option value="preco" {{ request('ordem') == 'preco' ? 'selected' : '' }}>Menor Preço</option>
                        <option value="preco_desc" {{ request('ordem') == 'preco_desc' ? 'selected' : '' }}>Maior Preço</option>
                        <option value="autor" {{ request('ordem') == 'autor' ? 'selected' : '' }}>Autor (A-Z)</option>
                        <option value="created_at" {{ request('ordem') == 'created_at' ? 'selected' : '' }}>Mais Recentes</option>
                        <option value="popularidade" {{ request('ordem') == 'popularidade' ? 'selected' : '' }}>Mais Populares</option>
                    </select>
                </div>
                
                <!-- Botões de Ação -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Filtrar
                    </button>
                    <a href="{{ route('loja.catalogo') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Lista de Livros -->
    <div class="col-lg-9">
        <!-- Barra de Resultados e Visualização -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="results-info flex-grow-1 me-3">
                @if(request()->hasAny(['busca', 'categoria', 'preco_min', 'preco_max', 'disponivel']))
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <strong>{{ $livros->total() ?? 0 }}</strong> resultados encontrados
                            @if(request('busca'))
                                para "<em>{{ request('busca') }}</em>"
                            @endif
                        </div>
                        <a href="{{ route('loja.catalogo') }}" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-times me-1"></i>Limpar filtros
                        </a>
                    </div>
                @else
                    <strong>{{ $livros->total() ?? 0 }}</strong> livros no catálogo
                @endif
            </div>
            
            <!-- Toggle de visualização -->
            <div class="view-toggle">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-secondary active" id="gridView">
                        <i class="fas fa-th"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="listView">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Grid de Livros -->
        @if($livros->count() > 0)
            <div class="livro-grid" id="livrosContainer">
                <div class="row" id="gridContainer">
                    @foreach($livros as $livro)
                    <div class="col-lg-4 col-md-6 mb-4 livro-item">
                        <div class="card livro-card h-100">
                            <div class="position-relative overflow-hidden">
                                @if($livro->imagem)
                                    <img src="{{ $livro->imagem_url }}" class="livro-image" alt="{{ $livro->titulo }}">
                                @else
                                    <div class="livro-image bg-light d-flex align-items-center justify-content-center">
                                        <div class="text-center">
                                            <i class="fas fa-book fa-3x text-muted mb-2"></i>
                                            <p class="text-muted small mb-0">Sem Capa</p>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Badges -->
                                <div class="position-absolute top-0 start-0 m-2">
                                    @if($livro->created_at->diffInDays() < 30)
                                        <span class="badge bg-success mb-1 d-block">NOVO</span>
                                    @endif
                                    @if($livro->preco < 30)
                                        <span class="badge bg-warning">OFERTA</span>
                                    @endif
                                </div>
                                
                                <!-- Status do Estoque -->
                                <div class="position-absolute top-0 end-0 m-2">
                                    @if($livro->estoque > 5)
                                        <span class="badge bg-success">Disponível</span>
                                    @elseif($livro->estoque > 0)
                                        <span class="badge bg-warning">Últimas unidades</span>
                                    @else
                                        <span class="badge bg-danger">Esgotado</span>
                                    @endif
                                </div>
                                
                                <!-- Botão de Favorito -->
                                @auth
                                <div class="position-absolute bottom-0 end-0 m-2">
                                    <button class="btn btn-sm btn-light rounded-circle favorite-btn opacity-0" 
                                            data-livro-id="{{ $livro->id }}"
                                            onclick="toggleFavorite({{ $livro->id }})"
                                            data-bs-toggle="tooltip" 
                                            title="Adicionar aos favoritos">
                                        @php
                                            $isFavorito = auth()->user()->favorites()->where('livro_id', $livro->id)->exists();
                                        @endphp
                                        <i class="{{ $isFavorito ? 'fas text-danger' : 'far' }} fa-heart"></i>
                                    </button>
                                </div>
                                @endauth
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <!-- Categoria -->
                                @if($livro->categoria)
                                    <div class="mb-2">
                                        <a href="{{ route('loja.categoria', $livro->categoria) }}" 
                                           class="badge bg-secondary text-decoration-none">
                                            {{ $livro->categoria }}
                                        </a>
                                    </div>
                                @endif
                                
                                <!-- Título -->
                                <h6 class="card-title fw-bold mb-2" style="min-height: 3rem;">
                                    <a href="{{ route('loja.detalhes', $livro) }}" class="text-decoration-none text-dark">
                                        {{ Str::limit($livro->titulo, 50) }}
                                    </a>
                                </h6>
                                
                                <!-- Autor -->
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-user-edit me-1"></i>{{ $livro->autor }}
                                </p>
                                
                                <!-- Informações Adicionais -->
                                @if($livro->editora || $livro->ano_publicacao)
                                <div class="small text-muted mb-3">
                                    @if($livro->editora)
                                        <span><i class="fas fa-building me-1"></i>{{ $livro->editora }}</span>
                                    @endif
                                    @if($livro->editora && $livro->ano_publicacao) • @endif
                                    @if($livro->ano_publicacao)
                                        <span>{{ $livro->ano_publicacao }}</span>
                                    @endif
                                </div>
                                @endif
                                
                                <!-- Sinopse (truncada) -->
                                @if($livro->sinopse)
                                <p class="card-text small text-muted mb-3" style="line-height: 1.4;">
                                    {{ Str::limit($livro->sinopse, 100) }}
                                </p>
                                @endif
                                
                                <!-- Preço e Ações -->
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <span class="h5 text-success mb-0 fw-bold">{{ $livro->preco_formatado }}</span>
                                            @if($livro->preco < 30)
                                                <small class="text-muted text-decoration-line-through ms-1">
                                                    R$ {{ number_format($livro->preco * 1.3, 2, ',', '.') }}
                                                </small>
                                            @endif
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-boxes"></i> {{ $livro->estoque }}
                                        </small>
                                    </div>
                                    
                                    <!-- Botões de Ação -->
                                    <div class="d-grid gap-2">
                                        @if($livro->estoque > 0)
                                            <form method="POST" action="{{ route('cart.add', $livro) }}">
                                                @csrf
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                                    <i class="fas fa-shopping-cart me-1"></i>
                                                    Adicionar ao Carrinho
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-secondary btn-sm w-100" disabled>
                                                <i class="fas fa-ban me-1"></i>
                                                Indisponível
                                            </button>
                                        @endif
                                        
                                        <a href="{{ route('loja.detalhes', $livro) }}" 
                                           class="btn btn-outline-secondary btn-sm w-100">
                                            <i class="fas fa-eye me-1"></i>
                                            Ver Detalhes
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Paginação -->
            <div class="d-flex justify-content-center mt-5">
                {{ $livros->links() }}
            </div>
        @else
            <!-- Estado Vazio -->
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-search fa-5x text-muted"></i>
                </div>
                <h3>Nenhum livro encontrado</h3>
                <p class="text-muted mb-4">
                    @if(request()->hasAny(['busca', 'categoria', 'preco_min', 'preco_max']))
                        Tente ajustar os filtros de busca ou
                        <a href="{{ route('loja.catalogo') }}">ver todos os livros</a>.
                    @else
                        Ainda não temos livros cadastrados no catálogo.
                    @endif
                </p>
                @if(request()->hasAny(['busca', 'categoria', 'preco_min', 'preco_max']))
                    <a href="{{ route('loja.catalogo') }}" class="btn btn-primary">
                        <i class="fas fa-list"></i> Ver Todos os Livros
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Categorias Populares (se não houver filtros ativos) -->
@if(!request()->hasAny(['busca', 'categoria', 'preco_min', 'preco_max']) && $categorias->count() > 0)
<section class="mt-5 pt-5 border-top">
    <h3 class="section-title text-center mb-4">
        <i class="fas fa-tags me-2"></i>Explorar por Categoria
    </h3>
    <div class="text-center">
        @foreach($categorias as $categoria)
            <a href="{{ route('loja.categoria', $categoria) }}" class="category-chip">
                {{ $categoria }}
            </a>
        @endforeach
    </div>
</section>
@endif
@endsection

@push('styles')
<style>
    .livro-card .favorite-btn {
        transition: opacity 0.3s ease;
    }
    
    .livro-card:hover .favorite-btn {
        opacity: 1 !important;
    }
</style>
@endpush

@push('scripts')
<script>
// Função para definir faixas de preço
function setPrice(min, max) {
    document.querySelector('input[name="preco_min"]').value = min;
    document.querySelector('input[name="preco_max"]').value = max;
}

// Toggle de visualização (grid/lista)
document.getElementById('listView').addEventListener('click', function() {
    // Implementar vista em lista se necessário
    this.classList.add('active');
    document.getElementById('gridView').classList.remove('active');
});

document.getElementById('gridView').addEventListener('click', function() {
    this.classList.add('active');
    document.getElementById('listView').classList.remove('active');
});

// Função para favoritar livros
function toggleFavorite(livroId) {
    @auth
    fetch(`/livros/${livroId}/favorite`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const button = document.querySelector(`[data-livro-id="${livroId}"]`);
        const icon = button.querySelector('i');
        if (data.favorited) {
            icon.classList.remove('far');
            icon.classList.add('fas', 'text-danger');
        } else {
            icon.classList.remove('fas', 'text-danger');
            icon.classList.add('far');
        }
    })
    .catch(error => console.error('Erro:', error));
    @else
    window.location.href = '{{ route("login") }}';
    @endauth
}

// Auto-submit do formulário quando filtros mudarem
document.getElementById('filterForm').addEventListener('change', function(e) {
    if (e.target.name !== 'busca') {
        this.submit();
    }
});

// Submit do formulário ao pressionar Enter na busca
document.querySelector('input[name="busca"]').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('filterForm').submit();
    }
});

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush