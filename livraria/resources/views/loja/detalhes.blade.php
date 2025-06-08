@extends('layouts.app')
@section('title', $livro->titulo . ' - Biblioteca Literária')

@section('content')
<style>
    .book-hero {
        background: linear-gradient(135deg, var(--aged-paper) 0%, var(--cream) 100%);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .book-cover {
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(139, 69, 19, 0.3);
        transition: transform 0.3s ease;
        max-height: 500px;
        object-fit: cover;
    }
    
    .book-cover:hover {
        transform: scale(1.05);
    }
    
    .book-info-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 8px 25px rgba(139, 69, 19, 0.1);
        border: none;
    }
    
    .price-section {
        background: linear-gradient(135deg, var(--forest-green) 0%, #32CD32 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 15px;
        text-align: center;
        margin-bottom: 1.5rem;
    }
    
    .action-buttons .btn {
        border-radius: 25px;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .book-specs {
        background: var(--aged-paper);
        border-radius: 15px;
        padding: 1.5rem;
        border-left: 4px solid var(--gold);
    }
    
    .spec-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(139, 69, 19, 0.1);
    }
    
    .spec-item:last-child {
        border-bottom: none;
    }
    
    .related-books {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(139, 69, 19, 0.05);
    }
    
    .quantity-selector {
        display: flex;
        align-items: center;
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 25px;
        overflow: hidden;
    }
    
    .quantity-selector button {
        background: none;
        border: none;
        padding: 0.5rem 1rem;
        color: var(--primary-brown);
        font-weight: bold;
    }
    
    .quantity-selector input {
        border: none;
        text-align: center;
        width: 60px;
        padding: 0.5rem;
    }
    
    .favorite-btn {
        transition: all 0.3s ease;
    }
    
    .favorite-btn:hover {
        transform: scale(1.1);
    }
    
    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 1rem;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        content: "📖";
        color: var(--gold);
    }
    
    .tab-content {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(139, 69, 19, 0.05);
    }
    
    .nav-tabs {
        border-bottom: 2px solid var(--light-brown);
    }
    
    .nav-tabs .nav-link {
        border: none;
        color: var(--primary-brown);
        font-weight: 500;
        padding: 1rem 1.5rem;
        border-radius: 10px 10px 0 0;
    }
    
    .nav-tabs .nav-link.active {
        background: var(--gold);
        color: white;
    }
    
    .stock-indicator {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .stock-bar {
        flex-grow: 1;
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .stock-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }
</style>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('loja.index') }}">Início</a></li>
        <li class="breadcrumb-item"><a href="{{ route('loja.catalogo') }}">Catálogo</a></li>
        @if($livro->categoria)
        <li class="breadcrumb-item"><a href="{{ route('loja.categoria', $livro->categoria) }}">{{ $livro->categoria }}</a></li>
        @endif
        <li class="breadcrumb-item active">{{ Str::limit($livro->titulo, 30) }}</li>
    </ol>
</nav>

<!-- Hero Section do Livro -->
<div class="book-hero">
    <div class="row align-items-center">
        <div class="col-lg-5">
            <div class="text-center">
                @if($livro->imagem)
                    <img src="{{ $livro->imagem_url }}" class="book-cover img-fluid" alt="{{ $livro->titulo }}">
                @else
                    <div class="book-cover bg-light d-flex align-items-center justify-content-center" style="height: 400px; width: 280px; margin: 0 auto;">
                        <div class="text-center">
                            <i class="fas fa-book fa-5x text-muted mb-3"></i>
                            <p class="text-muted">Capa não disponível</p>
                        </div>
                    </div>
                @endif
                
                <!-- Badges -->
                <div class="mt-3">
                    @if($livro->created_at->diffInDays() < 30)
                        <span class="badge bg-success me-2">NOVO</span>
                    @endif
                    @if($livro->preco < 30)
                        <span class="badge bg-warning me-2">OFERTA</span>
                    @endif
                    @if($livro->estoque > 0)
                        <span class="badge bg-success">DISPONÍVEL</span>
                    @else
                        <span class="badge bg-danger">ESGOTADO</span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-7">
            <div class="book-info-card">
                <!-- Título e Autor -->
                <div class="mb-4">
                    <h1 class="page-title mb-3">{{ $livro->titulo }}</h1>
                    <h4 class="text-muted mb-2">
                        <i class="fas fa-user-edit me-2"></i>{{ $livro->autor }}
                    </h4>
                    @if($livro->categoria)
                    <div class="mb-3">
                        <a href="{{ route('loja.categoria', $livro->categoria) }}" 
                           class="badge bg-secondary text-decoration-none fs-6">
                            <i class="fas fa-tag me-1"></i>{{ $livro->categoria }}
                        </a>
                    </div>
                    @endif
                </div>
                
                <!-- Preço -->
                <div class="price-section">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">{{ $livro->preco_formatado }}</h2>
                            @if($livro->preco < 30)
                                <small class="text-light">
                                    <del>R$ {{ number_format($livro->preco * 1.3, 2, ',', '.') }}</del>
                                    <span class="badge bg-warning text-dark ms-2">
                                        {{ round((1 - ($livro->preco / ($livro->preco * 1.3))) * 100) }}% OFF
                                    </span>
                                </small>
                            @endif
                        </div>
                        <div class="text-end">
                            <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Indicador de Estoque -->
                <div class="stock-indicator">
                    <span class="text-muted small">Estoque:</span>
                    <div class="stock-bar">
                        @php
                            $stockPercentage = $livro->estoque > 0 ? min(($livro->estoque / 20) * 100, 100) : 0;
                            $stockColor = $livro->estoque > 10 ? 'success' : ($livro->estoque > 0 ? 'warning' : 'danger');
                        @endphp
                        <div class="stock-fill bg-{{ $stockColor }}" style="width: {{ $stockPercentage }}%"></div>
                    </div>
                    <span class="text-muted small">{{ $livro->estoque }} unidades</span>
                </div>
                
                <!-- Ações -->
                @if($livro->estoque > 0)
                <form method="POST" action="{{ route('cart.add', $livro) }}" class="mb-3">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Quantidade:</label>
                            <div class="quantity-selector">
                                <button type="button" onclick="decreaseQuantity()">-</button>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" max="{{ $livro->estoque }}" readonly>
                                <button type="button" onclick="increaseQuantity()">+</button>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="action-buttons d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    Adicionar ao Carrinho
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                @else
                <div class="alert alert-danger text-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Este livro está temporariamente esgotado
                </div>
                @endif
                
                <!-- Botões Secundários -->
                <div class="row">
                    <div class="col-6">
                        @auth
                        <button class="btn btn-outline-danger w-100 favorite-btn" 
                                onclick="toggleFavorite({{ $livro->id }})"
                                data-livro-id="{{ $livro->id }}">
                            <i class="{{ $isFavorito ? 'fas' : 'far' }} fa-heart me-1"></i>
                            {{ $isFavorito ? 'Remover dos' : 'Adicionar aos' }} Favoritos
                        </button>
                        @else
                        <a href="{{ route('login') }}" class="btn btn-outline-danger w-100">
                            <i class="far fa-heart me-1"></i>
                            Favoritar
                        </a>
                        @endauth
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-secondary w-100" onclick="shareBook()">
                            <i class="fas fa-share-alt me-1"></i>
                            Compartilhar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detalhes e Informações -->
<div class="row">
    <div class="col-lg-8">
        <!-- Tabs de Informações -->
        <ul class="nav nav-tabs" id="bookTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">
                    <i class="fas fa-align-left me-1"></i>Sinopse
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button" role="tab">
                    <i class="fas fa-info-circle me-1"></i>Especificações
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
                    <i class="fas fa-star me-1"></i>Avaliações
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="bookTabsContent">
            <!-- Sinopse -->
            <div class="tab-pane fade show active" id="description" role="tabpanel">
                @if($livro->sinopse)
                    <div class="prose">
                        {!! nl2br(e($livro->sinopse)) !!}
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                        <p>Sinopse não disponível para este livro.</p>
                    </div>
                @endif
            </div>
            
            <!-- Especificações -->
            <div class="tab-pane fade" id="specs" role="tabpanel">
                <div class="book-specs">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-list-ul me-2"></i>Detalhes Técnicos
                    </h5>
                    
                    @if($livro->isbn)
                    <div class="spec-item">
                        <span><i class="fas fa-barcode me-2"></i>ISBN:</span>
                        <strong>{{ $livro->isbn }}</strong>
                    </div>
                    @endif
                    
                    @if($livro->editora)
                    <div class="spec-item">
                        <span><i class="fas fa-building me-2"></i>Editora:</span>
                        <strong>{{ $livro->editora }}</strong>
                    </div>
                    @endif
                    
                    @if($livro->ano_publicacao)
                    <div class="spec-item">
                        <span><i class="fas fa-calendar me-2"></i>Ano de Publicação:</span>
                        <strong>{{ $livro->ano_publicacao }}</strong>
                    </div>
                    @endif
                    
                    @if($livro->paginas)
                    <div class="spec-item">
                        <span><i class="fas fa-file-alt me-2"></i>Páginas:</span>
                        <strong>{{ $livro->paginas }} páginas</strong>
                    </div>
                    @endif
                    
                    <div class="spec-item">
                        <span><i class="fas fa-tag me-2"></i>Categoria:</span>
                        <strong>{{ $livro->categoria ?? 'Não categorizado' }}</strong>
                    </div>
                    
                    <div class="spec-item">
                        <span><i class="fas fa-boxes me-2"></i>Disponibilidade:</span>
                        <strong class="text-{{ $livro->estoque > 0 ? 'success' : 'danger' }}">
                            {{ $livro->estoque > 0 ? $livro->estoque . ' em estoque' : 'Esgotado' }}
                        </strong>
                    </div>
                    
                    <div class="spec-item">
                        <span><i class="fas fa-calendar-plus me-2"></i>Adicionado em:</span>
                        <strong>{{ $livro->created_at->format('d/m/Y') }}</strong>
                    </div>
                </div>
            </div>
            
            <!-- Avaliações -->
            <div class="tab-pane fade" id="reviews" role="tabpanel">
                <div class="text-center text-muted py-4">
                    <i class="fas fa-star fa-3x mb-3"></i>
                    <h5>Sistema de Avaliações</h5>
                    <p>Em breve você poderá avaliar e ver avaliações de outros leitores!</p>
                    <div class="d-flex justify-content-center">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star text-warning mx-1"></i>
                        @endfor
                    </div>
                    <small class="text-muted">Avaliação média: 5.0 (baseado em experiência do usuário)</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar com Informações Extras -->
    <div class="col-lg-4">
        <!-- Informações Rápidas -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Informações Rápidas
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-truck text-primary me-2"></i>
                        <strong>Entrega:</strong> Em todo o Brasil
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-shield-alt text-success me-2"></i>
                        <strong>Garantia:</strong> Troca em 7 dias
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-credit-card text-info me-2"></i>
                        <strong>Pagamento:</strong> À vista ou parcelado
                    </li>
                    <li>
                        <i class="fas fa-medal text-warning me-2"></i>
                        <strong>Qualidade:</strong> Livro novo e original
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Livros Relacionados -->
        @if($livrosRelacionados->count() > 0)
        <div class="related-books">
            <h5 class="fw-bold mb-3">
                <i class="fas fa-bookmark me-2"></i>Livros Relacionados
            </h5>
            
            @foreach($livrosRelacionados as $relacionado)
            <div class="d-flex mb-3 pb-3 border-bottom">
                <div class="flex-shrink-0 me-3">
                    @if($relacionado->imagem)
                        <img src="{{ $relacionado->imagem_url }}" 
                             style="width: 60px; height: 80px; object-fit: cover; border-radius: 5px;" 
                             alt="{{ $relacionado->titulo }}">
                    @else
                        <div style="width: 60px; height: 80px;" 
                             class="bg-light border rounded d-flex align-items-center justify-content-center">
                            <i class="fas fa-book text-muted"></i>
                        </div>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">
                        <a href="{{ route('loja.detalhes', $relacionado) }}" class="text-decoration-none text-dark">
                            {{ Str::limit($relacionado->titulo, 40) }}
                        </a>
                    </h6>
                    <p class="text-muted small mb-1">{{ $relacionado->autor }}</p>
                    <strong class="text-success">{{ $relacionado->preco_formatado }}</strong>
                    @if($relacionado->estoque > 0)
                        <form method="POST" action="{{ route('cart.add', $relacionado) }}" class="d-inline-block ms-2">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endforeach
            
            <div class="text-center">
                <a href="{{ route('loja.categoria', $livro->categoria) }}" class="btn btn-outline-primary btn-sm">
                    Ver mais de {{ $livro->categoria }}
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal de Compartilhamento -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-share-alt me-2"></i>Compartilhar Livro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Compartilhe este livro com seus amigos:</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-success" onclick="shareWhatsApp()">
                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                    </button>
                    <button class="btn btn-primary" onclick="shareFacebook()">
                        <i class="fab fa-facebook me-2"></i>Facebook
                    </button>
                    <button class="btn btn-info" onclick="shareTwitter()">
                        <i class="fab fa-twitter me-2"></i>Twitter
                    </button>
                    <button class="btn btn-secondary" onclick="copyLink()">
                        <i class="fas fa-copy me-2"></i>Copiar Link
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Controle de quantidade
function increaseQuantity() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.getAttribute('max'));
    const current = parseInt(input.value);
    if (current < max) {
        input.value = current + 1;
    }
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    const current = parseInt(input.value);
    if (current > 1) {
        input.value = current - 1;
    }
}

// Função para favoritar livros
function toggleFavorite(livroId) {
    @auth
    const button = document.querySelector(`[data-livro-id="${livroId}"]`);
    const icon = button.querySelector('i');
    const originalText = button.innerHTML;
    
    // Feedback visual imediato
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processando...';
    
    fetch(`/livros/${livroId}/favorite`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.favorited) {
            button.innerHTML = '<i class="fas fa-heart me-1"></i>Remover dos Favoritos';
            button.classList.remove('btn-outline-danger');
            button.classList.add('btn-danger');
        } else {
            button.innerHTML = '<i class="far fa-heart me-1"></i>Adicionar aos Favoritos';
            button.classList.remove('btn-danger');
            button.classList.add('btn-outline-danger');
        }
        button.disabled = false;
    })
    .catch(error => {
        console.error('Erro:', error);
        button.innerHTML = originalText;
        button.disabled = false;
    });
    @else
    window.location.href = '{{ route("login") }}';
    @endauth
}

// Funções de compartilhamento
function shareBook() {
    const modal = new bootstrap.Modal(document.getElementById('shareModal'));
    modal.show();
}

function shareWhatsApp() {
    const text = `Confira este livro: {{ $livro->titulo }} por {{ $livro->autor }}`;
    const url = window.location.href;
    window.open(`https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`);
}

function shareFacebook() {
    const url = window.location.href;
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`);
}

function shareTwitter() {
    const text = `Recomendo a leitura: {{ $livro->titulo }} por {{ $livro->autor }}`;
    const url = window.location.href;
    window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`);
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Link copiado para a área de transferência!');
    });
}

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush