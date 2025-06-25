@extends('layouts.app')

@section('title', 'Catálogo - Livraria Mil Páginas')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Header com Busca -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="search-section text-center py-4 bg-gradient rounded-3 mb-4">
                <h1 class="display-6 text-white mb-3">
                    📚 Encontre seu Próximo Livro Favorito
                </h1>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <!-- Busca Simplificada -->
                        <form action="{{ route('loja.catalogo') }}" method="GET" class="search-form">
                            <div class="input-group input-group-lg">
                                <input type="text" 
                                       name="busca" 
                                       class="form-control" 
                                       placeholder="Buscar por título, autor, ISBN..."
                                       value="{{ request('busca') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros e Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h4 class="mb-0">Catálogo</h4>
                <span class="badge bg-primary fs-6">
                    {{ $livros->total() }} {{ $livros->total() == 1 ? 'livro' : 'livros' }}
                </span>
                
                @if(request()->hasAny(['busca', 'categoria', 'preco_min', 'preco_max']))
                    <div class="active-filters">
                        @if(request('busca'))
                            <span class="badge bg-secondary">
                                Busca: "{{ request('busca') }}"
                                <a href="{{ route('loja.catalogo', request()->except('busca')) }}" class="text-white ms-1">×</a>
                            </span>
                        @endif
                        @if(request('categoria'))
                            <span class="badge bg-secondary">
                                Categoria: {{ request('categoria') }}
                                <a href="{{ route('loja.catalogo', request()->except('categoria')) }}" class="text-white ms-1">×</a>
                            </span>
                        @endif
                        <a href="{{ route('loja.catalogo') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Limpar Filtros
                        </a>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-4 text-end">
            <!-- Ordenação -->
            <form action="{{ route('loja.catalogo') }}" method="GET" class="d-inline-block">
                @foreach(request()->except('ordem') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <select name="ordem" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                    <option value="titulo" {{ request('ordem') == 'titulo' ? 'selected' : '' }}>A-Z</option>
                    <option value="preco" {{ request('ordem') == 'preco' ? 'selected' : '' }}>Menor Preço</option>
                    <option value="preco_desc" {{ request('ordem') == 'preco_desc' ? 'selected' : '' }}>Maior Preço</option>
                    <option value="created_at" {{ request('ordem') == 'created_at' ? 'selected' : '' }}>Mais Novos</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Filtros Laterais -->
    <div class="row">
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('loja.catalogo') }}" method="GET">
                        @if(request('busca'))
                            <input type="hidden" name="busca" value="{{ request('busca') }}">
                        @endif
                        
                        <!-- Categorias -->
                        <div class="mb-4">
                            <h6>Categorias</h6>
                            @foreach($categorias as $categoria)
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="categoria" 
                                           value="{{ $categoria->id }}" 
                                           id="cat{{ $categoria->id }}"
                                           {{ request('categoria') == $categoria->id ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="cat{{ $categoria->id }}">
                                        {{ $categoria->nome }}
                                        <span class="text-muted">({{ $categoria->livros_count }})</span>
                                    </label>
                                </div>
                            @endforeach
                            @if(request('categoria'))
                                <a href="{{ route('loja.catalogo', request()->except('categoria')) }}" class="small text-muted">
                                    <i class="fas fa-times me-1"></i>Limpar categoria
                                </a>
                            @endif
                        </div>

                        <!-- Faixa de Preço -->
                        <div class="mb-4">
                            <h6>Faixa de Preço</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" 
                                           class="form-control form-control-sm" 
                                           name="preco_min" 
                                           placeholder="Min" 
                                           value="{{ request('preco_min') }}"
                                           step="0.01">
                                </div>
                                <div class="col-6">
                                    <input type="number" 
                                           class="form-control form-control-sm" 
                                           name="preco_max" 
                                           placeholder="Max" 
                                           value="{{ request('preco_max') }}"
                                           step="0.01">
                                </div>
                            </div>
                        </div>

                        <!-- Disponibilidade -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="disponivel" 
                                       value="1" 
                                       id="disponivel"
                                       {{ request('disponivel') ? 'checked' : '' }}>
                                <label class="form-check-label small" for="disponivel">
                                    Apenas disponíveis
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-search me-1"></i>Aplicar Filtros
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Grid de Livros -->
        <div class="col-lg-9">
            @if($livros->count() > 0)
                <!-- Loading inicial -->
                <div id="initial-loading" style="display: none;">
                    <x-loading-spinner />
                </div>
                
                <div class="row" id="livros-grid">
                    @foreach($livros as $livro)
                        <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                            <div class="card book-card h-100 shadow-sm">
                                <!-- Imagem -->
                                <div class="position-relative">
                                    <img src="{{ $livro->imagem_url }}" 
                                         class="card-img-top" 
                                         alt="{{ $livro->titulo }}"
                                         style="height: 280px; object-fit: cover;">
                                    
                                    <!-- Badge de Desconto -->
                                    @if($livro->tem_promocao)
                                        <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                            -{{ $livro->getDesconto() }}%
                                        </span>
                                    @endif
                                    
                                    <!-- Botão de Favorito -->
                                    @auth
                                        @php
                                            $isFavorito = auth()->user()->favorites()->where('livro_id', $livro->id)->exists();
                                        @endphp
                                        <button class="btn btn-light btn-sm rounded-circle position-absolute top-0 start-0 m-2 favorite-btn"
                                                data-livro-id="{{ $livro->id }}"
                                                data-favorited="{{ $isFavorito ? 'true' : 'false' }}"
                                                onclick="toggleFavorite({{ $livro->id }}, this)">
                                            <i class="fas fa-heart {{ $isFavorito ? 'text-danger' : 'text-muted' }}"></i>
                                        </button>
                                    @endauth
                                </div>
                                
                                <div class="card-body d-flex flex-column">
                                    <!-- Título e Autor -->
                                    <h6 class="card-title fw-bold mb-2" style="min-height: 3rem;">
                                        <a href="{{ route('loja.detalhes', $livro) }}" 
                                           class="text-decoration-none text-dark">
                                            {{ Str::limit($livro->titulo, 50) }}
                                        </a>
                                    </h6>
                                    
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-user me-1"></i>{{ $livro->autor }}
                                    </p>
                                    
                                    <!-- Categoria -->
                                    @if($livro->categoria)
                                        <div class="mb-2">
                                            <span class="badge bg-secondary small">{{ $livro->categoria->nome }}</span>
                                        </div>
                                    @endif
                                    
                                    <!-- Avaliação -->
                                    @if($livro->avaliacao_media > 0)
                                        <div class="mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="text-warning me-2">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star{{ $i <= $livro->avaliacao_media ? '' : '-o' }}"></i>
                                                    @endfor
                                                </div>
                                                <small class="text-muted">
                                                    {{ number_format($livro->avaliacao_media, 1) }}
                                                    ({{ $livro->total_avaliacoes }})
                                                </small>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Preço -->
                                    <div class="mt-auto">
                                        <div class="price-section mb-3">
                                            @if($livro->tem_promocao)
                                                <div class="d-flex align-items-center">
                                                    <span class="text-muted text-decoration-line-through me-2">
                                                        R$ {{ number_format($livro->preco, 2, ',', '.') }}
                                                    </span>
                                                    <span class="text-success fw-bold h5 mb-0">
                                                        R$ {{ number_format($livro->preco_promocional, 2, ',', '.') }}
                                                    </span>
                                                </div>
                                                <small class="text-success">
                                                    Economize R$ {{ number_format($livro->preco - $livro->preco_promocional, 2, ',', '.') }}
                                                </small>
                                            @else
                                                <span class="text-success fw-bold h5">
                                                    R$ {{ number_format($livro->preco, 2, ',', '.') }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <!-- Botões de Ação -->
                                        <div class="d-grid gap-2">
                                            @if($livro->estoque > 0)
                                                <button class="btn btn-primary btn-add-cart" 
                                                        data-livro-id="{{ $livro->id }}"
                                                        onclick="addToCart({{ $livro->id }})">
                                                    <i class="fas fa-shopping-cart me-1"></i>
                                                    Adicionar ao Carrinho
                                                </button>
                                            @else
                                                <button class="btn btn-secondary" disabled>
                                                    <i class="fas fa-ban me-1"></i>
                                                    Indisponível
                                                </button>
                                            @endif
                                            
                                            <a href="{{ route('loja.detalhes', $livro) }}" 
                                               class="btn btn-outline-primary btn-sm">
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
                
                <!-- Paginação -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-center">
                            {{ $livros->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
                
            @else
                <!-- Estado Vazio com Loading para busca -->
                <div class="row">
                    <div class="col-12">
                        <div id="search-loading" style="display: none;">
                            <x-loading-spinner />
                        </div>
                        
                        <div class="empty-state text-center py-5">
                            <i class="fas fa-search fa-5x text-muted mb-4"></i>
                            <h3>Nenhum livro encontrado</h3>
                            <p class="text-muted mb-4">
                                Tente ajustar os filtros ou fazer uma nova busca.
                            </p>
                            <a href="{{ route('loja.catalogo') }}" class="btn btn-primary">
                                <i class="fas fa-undo me-1"></i>
                                Ver Todos os Livros
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.search-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.book-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 15px;
    overflow: hidden;
}

.book-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.favorite-btn {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.book-card:hover .favorite-btn {
    opacity: 1;
}

.active-filters .badge {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
}

.empty-state {
    min-height: 400px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Loading states */
.btn-add-cart.loading {
    pointer-events: none;
    opacity: 0.7;
}

.btn-add-cart.loading::after {
    content: '';
    width: 16px;
    height: 16px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    display: inline-block;
    animation: spin 1s linear infinite;
    margin-left: 8px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .book-card {
        margin-bottom: 2rem;
    }
    
    .favorite-btn {
        opacity: 1;
    }
    
    .d-flex.gap-3 {
        gap: 1rem !important;
    }
}

.price-section {
    min-height: 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Mostrar loading ao fazer busca
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function() {
            const loadingContainer = document.getElementById('initial-loading');
            if (loadingContainer) {
                loadingContainer.style.display = 'block';
                document.getElementById('livros-grid').style.display = 'none';
            }
        });
    }
    
    // Mostrar loading ao aplicar filtros
    const filterForms = document.querySelectorAll('form[action*="catalogo"]');
    filterForms.forEach(form => {
        form.addEventListener('submit', function() {
            const loadingContainer = document.getElementById('search-loading');
            if (loadingContainer) {
                loadingContainer.style.display = 'block';
            }
        });
    });
});

// Adicionar ao carrinho
async function addToCart(livroId) {
    const button = document.querySelector(`.btn-add-cart[data-livro-id="${livroId}"]`);
    const originalText = button.innerHTML;
    
    // Loading state
    button.classList.add('loading');
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Adicionando...';
    button.disabled = true;
    
    try {
        const response = await fetch(`/cart/add/${livroId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ quantity: 1 })
        });
        
        if (response.ok) {
            // Sucesso
            showToast('Livro adicionado ao carrinho!', 'success');
            
            // Atualizar contador do carrinho (se existir)
            updateCartCounter();
            
            // Animação de sucesso
            button.innerHTML = '<i class="fas fa-check me-1"></i>Adicionado!';
            button.classList.remove('btn-primary');
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
                button.classList.add('btn-primary');
                button.disabled = false;
            }, 2000);
            
        } else {
            throw new Error('Erro ao adicionar ao carrinho');
        }
        
    } catch (error) {
        console.error('Erro:', error);
        showToast('Erro ao adicionar ao carrinho', 'error');
        
        button.innerHTML = originalText;
        button.disabled = false;
    } finally {
        button.classList.remove('loading');
    }
}

// Toggle favorito
function toggleFavorite(livroId, button) {
    // Verificar se usuário está logado
    @guest
        showToast('Você precisa estar logado para favoritar livros', 'warning');
        setTimeout(() => {
            window.location.href = '/login';
        }, 2000);
        return;
    @endguest
    
    const icon = button.querySelector('i');
    const originalText = button.innerHTML;
    
    // Loading state
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch(`/favoritos/toggle/${livroId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        button.innerHTML = originalText;
        const icon = button.querySelector('i');
        
        if (data.favorited) {
            icon.classList.remove('text-muted');
            icon.classList.add('text-danger');
            button.setAttribute('data-favorited', 'true');
            showToast('Adicionado aos favoritos!', 'success');
        } else {
            icon.classList.remove('text-danger');
            icon.classList.add('text-muted');
            button.setAttribute('data-favorited', 'false');
            showToast('Removido dos favoritos', 'info');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        button.innerHTML = originalText;
        
        // Tentar rota alternativa
        fetch(`/livros/${livroId}/favorite`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const icon = button.querySelector('i');
            
            if (data.favorited) {
                icon.classList.remove('text-muted');
                icon.classList.add('text-danger');
                button.setAttribute('data-favorited', 'true');
                showToast('Adicionado aos favoritos!', 'success');
            } else {
                icon.classList.remove('text-danger');
                icon.classList.add('text-muted');
                button.setAttribute('data-favorited', 'false');
                showToast('Removido dos favoritos', 'info');
            }
        })
        .catch(error => {
            console.error('Erro em rota alternativa:', error);
            showToast('Erro ao atualizar favoritos. Tente novamente.', 'error');
        });
    })
    .finally(() => {
        button.disabled = false;
    });
}

// Sistema de notificações
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'} me-2"></i>
            ${message}
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast && toast.parentElement) {
            toast.remove();
        }
    }, 4000);
}

// Atualizar contador do carrinho
function updateCartCounter() {
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            const counter = document.querySelector('.cart-counter');
            if (counter && data.count !== undefined) {
                counter.textContent = data.count;
                if (data.count > 0) {
                    counter.style.display = 'inline';
                } else {
                    counter.style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Erro ao atualizar contador:', error));
}
</script>
@endpush