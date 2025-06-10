@extends('layouts.app')
@section('title', 'Catálogo - Livraria Mil Páginas')

@section('content')
<!-- Header do Catálogo -->
<section class="catalogo-header py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold text-dark mb-3">
                    <i class="fas fa-book-open me-3 text-primary"></i>
                    Explore Nosso Catálogo
                </h1>
                <p class="lead text-muted mb-0">
                    Descubra entre <strong class="text-success">{{ $livros->total() ?? 0 }}</strong> livros o seu próximo grande encontro literário.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="catalogo-stats">
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <div class="h4 text-primary mb-1">{{ $livros->total() ?? 0 }}</div>
                                <small class="text-muted">Livros</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <div class="h4 text-success mb-1">{{ $categorias->count() ?? 0 }}</div>
                                <small class="text-muted">Categorias</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <div class="h4 text-warning mb-1">{{ $livros->where('estoque', '>', 0)->count() ?? 0 }}</div>
                                <small class="text-muted">Disponíveis</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar de Filtros -->
        <div class="col-lg-3 col-xl-2">
            <div class="filter-sidebar">
                <div class="filter-header">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-filter me-2"></i>Filtros
                    </h5>
                </div>
                
                <form method="GET" action="{{ route('loja.catalogo') }}" id="filterForm">
                    <!-- Busca por Texto -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-search me-1"></i>Buscar
                        </label>
                        <div class="input-group">
                            <input type="text" name="busca" class="form-control" 
                                   value="{{ request('busca') }}" 
                                   placeholder="Título, autor..."
                                   autocomplete="off">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Filtro por Categoria -->
                    <div class="filter-group">
                        <label class="filter-label">
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
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-dollar-sign me-1"></i>Faixa de Preço
                        </label>
                        <div class="price-inputs">
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="preco_min" class="form-control form-control-sm" 
                                           placeholder="Min" value="{{ request('preco_min') }}" min="0" step="0.01">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="preco_max" class="form-control form-control-sm" 
                                           placeholder="Max" value="{{ request('preco_max') }}" min="0" step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="price-shortcuts mt-2">
                            <small class="text-muted d-block mb-2">Faixas populares:</small>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-outline-primary btn-xs" onclick="setPrice(0, 25)">Até R$ 25</button>
                                <button type="button" class="btn btn-outline-primary btn-xs" onclick="setPrice(25, 50)">R$ 25-50</button>
                                <button type="button" class="btn btn-outline-primary btn-xs" onclick="setPrice(50, 100)">R$ 50-100</button>
                                <button type="button" class="btn btn-outline-primary btn-xs" onclick="setPrice(100, 999)">R$ 100+</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Disponibilidade -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-warehouse me-1"></i>Disponibilidade
                        </label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="disponivel" value="1" 
                                   {{ request('disponivel') ? 'checked' : '' }} id="disponivel">
                            <label class="form-check-label" for="disponivel">
                                Apenas em estoque
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="promocao" value="1" 
                                   {{ request('promocao') ? 'checked' : '' }} id="promocao">
                            <label class="form-check-label" for="promocao">
                                Em promoção
                            </label>
                        </div>
                    </div>
                    
                    <!-- Ordenação -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-sort me-1"></i>Ordenar por
                        </label>
                        <select name="ordem" class="form-select">
                            <option value="relevancia" {{ request('ordem') == 'relevancia' ? 'selected' : '' }}>Relevância</option>
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
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-search me-1"></i>Aplicar Filtros
                        </button>
                        <a href="{{ route('loja.catalogo') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-1"></i>Limpar Filtros
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Lista de Livros -->
        <div class="col-lg-9 col-xl-10">
            <!-- Barra de Resultados -->
            <div class="results-bar">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="results-info">
                            @if(request()->hasAny(['busca', 'categoria', 'preco_min', 'preco_max', 'disponivel', 'promocao']))
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <strong class="text-primary">{{ $livros->total() ?? 0 }}</strong> 
                                        <span class="text-muted">resultados encontrados</span>
                                        @if(request('busca'))
                                            <span class="text-muted">para</span> 
                                            <strong>"{{ request('busca') }}"</strong>
                                        @endif
                                    </div>
                                    <a href="{{ route('loja.catalogo') }}" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-times me-1"></i>Limpar
                                    </a>
                                </div>
                                
                                <!-- Filtros Ativos -->
                                <div class="active-filters mt-2">
                                    @if(request('categoria'))
                                        <span class="filter-tag">
                                            <i class="fas fa-tag me-1"></i>{{ request('categoria') }}
                                            <button type="button" onclick="removeFilter('categoria')" class="btn-close btn-close-sm ms-1"></button>
                                        </span>
                                    @endif
                                    @if(request('preco_min') || request('preco_max'))
                                        <span class="filter-tag">
                                            <i class="fas fa-dollar-sign me-1"></i>
                                            R$ {{ request('preco_min', '0') }} - R$ {{ request('preco_max', '∞') }}
                                            <button type="button" onclick="removeFilter('preco')" class="btn-close btn-close-sm ms-1"></button>
                                        </span>
                                    @endif
                                    @if(request('disponivel'))
                                        <span class="filter-tag">
                                            <i class="fas fa-check me-1"></i>Em estoque
                                            <button type="button" onclick="removeFilter('disponivel')" class="btn-close btn-close-sm ms-1"></button>
                                        </span>
                                    @endif
                                    @if(request('promocao'))
                                        <span class="filter-tag">
                                            <i class="fas fa-fire me-1"></i>Promoção
                                            <button type="button" onclick="removeFilter('promocao')" class="btn-close btn-close-sm ms-1"></button>
                                        </span>
                                    @endif
                                </div>
                            @else
                                <strong class="text-primary">{{ $livros->total() ?? 0 }}</strong> 
                                <span class="text-muted">livros no catálogo</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-4 text-end">
                        <!-- Toggle de visualização -->
                        <div class="view-toggle">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary active" id="gridView" title="Visualização em Grid">
                                    <i class="fas fa-th"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="listView" title="Visualização em Lista">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Grid de Livros -->
            @if($livros->count() > 0)
                <div class="livros-container" id="livrosContainer">
                    <div class="row" id="gridContainer">
                        @foreach($livros as $livro)
                            <x-livro-card :livro="$livro" />
                        @endforeach
                    </div>
                </div>
                
                <!-- Paginação -->
                <div class="pagination-wrapper">
                    {{ $livros->withQueryString()->links() }}
                </div>
            @else
                <!-- Estado Vazio -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-search fa-5x text-muted"></i>
                    </div>
                    <h3 class="empty-title">Nenhum livro encontrado</h3>
                    <p class="empty-text">
                        @if(request()->hasAny(['busca', 'categoria', 'preco_min', 'preco_max', 'disponivel', 'promocao']))
                            Tente ajustar os filtros de busca ou explore outras opções.
                        @else
                            Ainda não temos livros cadastrados no catálogo.
                        @endif
                    </p>
                    @if(request()->hasAny(['busca', 'categoria', 'preco_min', 'preco_max', 'disponivel', 'promocao']))
                        <a href="{{ route('loja.catalogo') }}" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i>Ver Todos os Livros
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Categorias Populares -->
@if(!request()->hasAny(['busca', 'categoria', 'preco_min', 'preco_max']) && $categorias->count() > 0)
<section class="categorias-section">
    <div class="container">
        <h3 class="section-title">
            <i class="fas fa-tags me-2"></i>Explorar por Categoria
        </h3>
        <div class="categorias-grid">
            @foreach($categorias->take(8) as $categoria)
                <a href="{{ route('loja.categoria', $categoria) }}" class="category-chip">
                    <i class="fas fa-bookmark me-2"></i>{{ $categoria }}
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection

@push('styles')
<style>
/* Header do Catálogo */
.catalogo-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
}

.stat-item {
    padding: 1rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Sidebar de Filtros */
.filter-sidebar {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    position: sticky;
    top: 2rem;
    max-height: calc(100vh - 4rem);
    overflow-y: auto;
}

.filter-header {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
}

.filter-group {
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #f1f3f4;
}

.filter-group:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.filter-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
    display: block;
    font-size: 0.9rem;
}

.btn-xs {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 4px;
}

.price-shortcuts .btn-xs:hover {
    transform: translateY(-1px);
}

.filter-actions {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #dee2e6;
}

/* Barra de Resultados */
.results-bar {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}

.results-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.active-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.25rem 0.5rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.filter-tag .btn-close {
    font-size: 0.6rem;
    padding: 0;
    margin: 0;
    opacity: 0.7;
}

.filter-tag .btn-close:hover {
    opacity: 1;
}

.view-toggle .btn {
    border: 1px solid #dee2e6;
    background: white;
    color: #6c757d;
}

.view-toggle .btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

/* Container de Livros */
.livros-container {
    min-height: 400px;
}

/* Estado Vazio */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.empty-icon {
    margin-bottom: 2rem;
    opacity: 0.5;
}

.empty-title {
    color: #495057;
    margin-bottom: 1rem;
}

.empty-text {
    color: #6c757d;
    margin-bottom: 2rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

/* Paginação */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 3rem;
    padding-top: 2rem;
}

/* Seção de Categorias */
.categorias-section {
    background: #f8f9fa;
    padding: 4rem 0;
    margin-top: 4rem;
}

.section-title {
    text-align: center;
    margin-bottom: 2rem;
    color: #495057;
    font-weight: 700;
}

.categorias-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
    max-width: 800px;
    margin: 0 auto;
}

.category-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,123,255,0.2);
}

.category-chip:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,123,255,0.3);
    color: white;
    text-decoration: none;
}

/* Responsividade */
@media (max-width: 991.98px) {
    .filter-sidebar {
        position: static;
        margin-bottom: 2rem;
        max-height: none;
    }
    
    .catalogo-stats {
        margin-top: 2rem;
    }
    
    .results-bar {
        padding: 1rem;
    }
    
    .active-filters {
        margin-top: 1rem;
    }
}

@media (max-width: 767.98px) {
    .filter-group {
        margin-bottom: 1rem;
        padding-bottom: 1rem;
    }
    
    .price-shortcuts .btn-xs {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }
    
    .view-toggle {
        margin-top: 1rem;
    }
    
    .empty-state {
        padding: 2rem 1rem;
    }
    
    .categorias-grid {
        gap: 0.5rem;
    }
    
    .category-chip {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
}

/* Melhorias nos cards usando o component */
.book-card {
    margin-bottom: 2rem;
}

/* Animações suaves */
.filter-sidebar, .results-bar, .empty-state {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading state */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.loading-overlay.show {
    opacity: 1;
    visibility: visible;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush

@push('scripts')
<script>
// Função para definir faixas de preço
function setPrice(min, max) {
    document.querySelector('input[name="preco_min"]').value = min;
    if (max < 999) {
        document.querySelector('input[name="preco_max"]').value = max;
    } else {
        document.querySelector('input[name="preco_max"]').value = '';
    }
}

// Função para remover filtros específicos
function removeFilter(filterType) {
    const form = document.getElementById('filterForm');
    
    switch(filterType) {
        case 'categoria':
            form.querySelector('[name="categoria"]').value = '';
            break;
        case 'preco':
            form.querySelector('[name="preco_min"]').value = '';
            form.querySelector('[name="preco_max"]').value = '';
            break;
        case 'disponivel':
            form.querySelector('[name="disponivel"]').checked = false;
            break;
        case 'promocao':
            form.querySelector('[name="promocao"]').checked = false;
            break;
    }
    
    form.submit();
}

// Toggle de visualização (grid/lista)
document.getElementById('listView').addEventListener('click', function() {
    this.classList.add('active');
    document.getElementById('gridView').classList.remove('active');
    
    // Aplicar estilo de lista (implementar se necessário)
    const container = document.getElementById('gridContainer');
    container.classList.add('list-view');
});

document.getElementById('gridView').addEventListener('click', function() {
    this.classList.add('active');
    document.getElementById('listView').classList.remove('active');
    
    // Aplicar estilo de grid
    const container = document.getElementById('gridContainer');
    container.classList.remove('list-view');
});

// Auto-submit do formulário quando filtros mudarem
document.getElementById('filterForm').addEventListener('change', function(e) {
    if (e.target.name !== 'busca') {
        // Mostrar loading
        showLoading();
        this.submit();
    }
});

// Submit do formulário ao pressionar Enter na busca
document.querySelector('input[name="busca"]').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        showLoading();
        document.getElementById('filterForm').submit();
    }
});

// Função para mostrar loading
function showLoading() {
    let loadingOverlay = document.querySelector('.loading-overlay');
    if (!loadingOverlay) {
        loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'loading-overlay';
        loadingOverlay.innerHTML = '<div class="loading-spinner"></div>';
        document.body.appendChild(loadingOverlay);
    }
    loadingOverlay.classList.add('show');
}

// Esconder loading quando página carregar
window.addEventListener('load', function() {
    const loadingOverlay = document.querySelector('.loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.classList.remove('show');
    }
});

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Smooth scroll para âncoras
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Persistir estado do filtro de visualização
    const savedView = localStorage.getItem('catalogView');
    if (savedView === 'list') {
        document.getElementById('listView').click();
    }
});

// Salvar preferência de visualização
document.getElementById('gridView').addEventListener('click', function() {
    localStorage.setItem('catalogView', 'grid');
});

document.getElementById('listView').addEventListener('click', function() {
    localStorage.setItem('catalogView', 'list');
});

// Função para favoritar livros (já incluída no component livro-card)
// Adicionando feedback visual melhorado
function showToast(message, type = 'info') {
    // Usar a função do component livro-card
    if (window.showToast) {
        window.showToast(message, type);
    }
}

// Infinite scroll (opcional)
let isLoading = false;
let currentPage = 1;

function loadMoreBooks() {
    if (isLoading) return;
    
    isLoading = true;
    // Implementar carregamento de mais livros via AJAX se necessário
}

// Intersection Observer para lazy loading de imagens
const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            if (img.dataset.src) {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        }
    });
});

// Observar todas as imagens quando carregarem
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
});
</script>
@endpush