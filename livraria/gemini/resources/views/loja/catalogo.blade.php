{{-- resources/views/loja/catalogo-with-components.blade.php --}}
{{-- Exemplo completo de uma página usando todos os componentes --}}
@extends('layouts.app')

@section('title', 'Catálogo - Livraria Mil Páginas')

@section('content')
<div class="container-fluid py-4">
    
    {{-- Header com Busca Avançada --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="search-section text-center py-4 bg-gradient rounded-3 mb-4">
                <h1 class="display-6 text-white mb-3">
                    📚 Encontre seu Próximo Livro Favorito
                </h1>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <x-search-bar 
                            placeholder="Buscar por título, autor, ISBN..."
                            :categories="$categorias"
                            :showFilters="true"
                            value="{{ request('q') }}"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Estatísticas e Filtros Ativos --}}
    <div class="row mb-4">
        <div class="col-md-8">
            @if(request()->hasAny(['q', 'categoria', 'preco_min', 'preco_max']))
                <div class="active-filters">
                    <h6 class="mb-2">Filtros Ativos:</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @if(request('q'))
                            <span class="badge bg-primary">
                                Busca: "{{ request('q') }}"
                                <button type="button" class="btn-close btn-close-white ms-1" 
                                        onclick="removeFilter('q')"></button>
                            </span>
                        @endif
                        @if(request('categoria'))
                            <span class="badge bg-secondary">
                                Categoria: {{ request('categoria') }}
                                <button type="button" class="btn-close btn-close-white ms-1" 
                                        onclick="removeFilter('categoria')"></button>
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        <div class="col-md-4 text-end">
            <div class="results-info">
                <span class="text-muted">
                    {{ $livros->total() }} {{ $livros->total() == 1 ? 'livro encontrado' : 'livros encontrados' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Grid de Livros --}}
    @if($livros->count() > 0)
        <div class="row" id="livros-grid">
            @foreach($livros as $livro)
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card book-card h-100 shadow-sm">
                        {{-- Imagem --}}
                        <div class="position-relative">
                            <img src="{{ $livro->imagem_url }}" 
                                 class="card-img-top" 
                                 alt="{{ $livro->titulo }}"
                                 style="height: 280px; object-fit: cover;">
                            
                            {{-- Badge de Desconto --}}
                            @if($livro->preco_original && $livro->preco_original > $livro->preco)
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                    -{{ round((($livro->preco_original - $livro->preco) / $livro->preco_original) * 100) }}%
                                </span>
                            @endif
                            
                            {{-- Botão de Favorito --}}
                            @auth
                                <button class="btn btn-light btn-sm rounded-circle position-absolute top-0 start-0 m-2 favorite-btn"
                                        data-livro-id="{{ $livro->id }}"
                                        onclick="toggleFavorite({{ $livro->id }})">
                                    <i class="fas fa-heart {{ $livro->isFavorited ? 'text-danger' : 'text-muted' }}"></i>
                                </button>
                            @endauth
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            {{-- Título e Autor --}}
                            <h6 class="card-title fw-bold mb-2" style="min-height: 3rem;">
                                <a href="{{ route('loja.detalhes', $livro) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ Str::limit($livro->titulo, 50) }}
                                </a>
                            </h6>
                            
                            <p class="text-muted small mb-2">
                                <i class="fas fa-user me-1"></i>{{ $livro->autor }}
                            </p>
                            
                            {{-- Categoria --}}
                            @if($livro->categoria)
                                <div class="mb-2">
                                    <span class="badge bg-secondary small">{{ $livro->categoria->nome }}</span>
                                </div>
                            @endif
                            
                            {{-- Avaliação --}}
                            <div class="mb-3">
                                <x-rating-stars 
                                    :rating="$livro->avaliacao_media ?? 0"
                                    :totalReviews="$livro->total_avaliacoes ?? 0"
                                    :showValue="true"
                                    :showCount="true"
                                    size="sm"
                                />
                            </div>
                            
                            {{-- Preço --}}
                            <div class="mt-auto">
                                <x-price-display 
                                    :price="$livro->preco"
                                    :originalPrice="$livro->preco_original"
                                    size="md"
                                    :showSavings="true"
                                    :installments="$livro->preco > 50 ? ['count' => 3, 'value' => $livro->preco / 3] : null"
                                />
                                
                                {{-- Botões de Ação --}}
                                <div class="d-grid gap-2 mt-3">
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
                                    
                                    <button class="btn btn-outline-primary btn-sm" 
                                            onclick="quickView({{ $livro->id }})">
                                        <i class="fas fa-eye me-1"></i>
                                        Visualização Rápida
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        {{-- Paginação Customizada --}}
        <div class="row mt-4">
            <div class="col-12">
                <x-pagination-custom 
                    :paginator="$livros"
                    theme="rounded"
                    size="md"
                    :showJumper="$livros->lastPage() > 10"
                    :showInfo="true"
                />
            </div>
        </div>
        
    @else
        {{-- Estado Vazio --}}
        <div class="row">
            <div class="col-12">
                <div class="empty-state text-center py-5">
                    <i class="fas fa-search fa-5x text-muted mb-4"></i>
                    <h3>Nenhum livro encontrado</h3>
                    <p class="text-muted mb-4">
                        Tente ajustar os filtros ou fazer uma nova busca.
                    </p>
                    <button class="btn btn-primary" onclick="clearAllFilters()">
                        <i class="fas fa-undo me-1"></i>
                        Limpar Filtros
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Modal de Visualização Rápida --}}
<x-modal 
    id="quick-view-modal"
    title="Visualização Rápida"
    size="lg"
    :centered="true"
>
    <div id="quick-view-content">
        {{-- Conteúdo carregado via AJAX --}}
    </div>
    
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Fechar
        </button>
        <button type="button" class="btn btn-primary" id="quick-add-cart">
            <i class="fas fa-shopping-cart me-1"></i>
            Adicionar ao Carrinho
        </button>
    </x-slot>
</x-modal>

{{-- Modal de Confirmação de Remoção --}}
<x-modal 
    id="confirm-remove-favorite"
    type="confirmation"
    title="Remover dos Favoritos"
    icon="fas fa-heart-broken text-danger"
    confirmText="Sim, Remover"
    cancelText="Cancelar"
    confirmClass="btn-outline-danger"
>
    Deseja remover este livro dos seus favoritos?
</x-modal>

{{-- Container para Notificações --}}
<div id="notification-container"></div>

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
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar sistema de notificações
    if (typeof NotificationSystem !== 'undefined') {
        NotificationSystem.init();
    }
    
    // Mostrar notificações de sessão
    @if(session('success'))
        NotificationSystem.success('{{ session('success') }}');
    @endif
    
    @if(session('error'))
        NotificationSystem.error('{{ session('error') }}');
    @endif
    
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
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
        
        const data = await response.json();
        
        if (data.success) {
            // Sucesso - mostrar notificação
            NotificationSystem.success('Livro adicionado ao carrinho!', {
                actions: [
                    {
                        text: 'Ver Carrinho',
                        onclick: 'window.location.href="/cart"'
                    },
                    {
                        text: 'Continuar',
                        action: 'dismiss'
                    }
                ]
            });
            
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
            throw new Error(data.message || 'Erro ao adicionar ao carrinho');
        }
        
    } catch (error) {
        console.error('Erro:', error);
        NotificationSystem.error