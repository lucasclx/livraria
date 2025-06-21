@extends('layouts.app')

@section('title', $livro->titulo . ' - ' . $livro->autor . ' - Livraria Mil Páginas')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('loja.index') }}">Início</a></li>
            <li class="breadcrumb-item"><a href="{{ route('loja.catalogo') }}">Catálogo</a></li>
            @if($livro->categoria)
                <li class="breadcrumb-item">
                    <a href="{{ route('loja.categoria', $livro->categoria->slug) }}">
                        {{ $livro->categoria->nome }}
                    </a>
                </li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $livro->titulo }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Imagem do Livro -->
        <div class="col-lg-4 col-md-5 mb-4">
            <div class="livro-imagem-container position-sticky" style="top: 20px;">
                <div class="card border-0 shadow">
                    <div class="position-relative">
                        <img src="{{ $livro->imagem_url }}" 
                             class="card-img-top livro-imagem-detalhes" 
                             alt="{{ $livro->titulo }}"
                             style="height: 500px; object-fit: cover;">
                        
                        @if($livro->tem_promocao)
                            <span class="badge bg-danger position-absolute top-0 start-0 m-3 fs-6">
                                -{{ $livro->getDesconto() }}% OFF
                            </span>
                        @endif

                        @if($livro->destaque)
                            <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3">
                                <i class="fas fa-star me-1"></i>Destaque
                            </span>
                        @endif
                    </div>
                </div>
                
                <!-- Status do Estoque -->
                <div class="mt-3 text-center">
                    @if($livro->estoque > 0)
                        @if($livro->estoque <= ($livro->estoque_minimo ?? 5))
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Últimas {{ $livro->estoque }} unidades!</strong>
                            </div>
                        @else
                            <div class="alert alert-success">
                                <i class="fas fa-check me-2"></i>
                                <strong>{{ $livro->estoque }} unidades disponíveis</strong>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-danger">
                            <i class="fas fa-times me-2"></i>
                            <strong>Produto esgotado</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informações do Livro -->
        <div class="col-lg-8 col-md-7">
            <!-- Cabeçalho -->
            <div class="mb-4">
                <h1 class="display-5 fw-bold text-dark mb-3">{{ $livro->titulo }}</h1>
                
                <div class="mb-3">
                    <h4 class="text-muted mb-0">
                        <i class="fas fa-user me-2"></i>{{ $livro->autor }}
                    </h4>
                </div>

                @if($livro->categoria)
                    <div class="mb-3">
                        <a href="{{ route('loja.categoria', $livro->categoria->slug) }}" 
                           class="badge bg-primary text-decoration-none fs-6 py-2 px-3">
                            <i class="fas fa-tag me-1"></i>{{ $livro->categoria->nome }}
                        </a>
                    </div>
                @endif

                <!-- Avaliações -->
                @if($livro->total_avaliacoes > 0)
                    <div class="mb-3">
                        <div class="d-flex align-items-center">
                            <div class="text-warning me-3">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $livro->avaliacao_media)
                                        <i class="fas fa-star"></i>
                                    @else
                                        <i class="far fa-star"></i>
                                    @endif
                                @endfor
                                <span class="text-dark ms-2 fw-bold">{{ number_format($livro->avaliacao_media, 1) }}</span>
                            </div>
                            <span class="text-muted">
                                ({{ $livro->total_avaliacoes }} {{ $livro->total_avaliacoes == 1 ? 'avaliação' : 'avaliações' }})
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Preço -->
            <div class="card border-0 bg-light mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            @if($livro->tem_promocao)
                                <div class="mb-2">
                                    <span class="text-muted text-decoration-line-through fs-5">
                                        R$ {{ number_format($livro->preco, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="text-danger">
                                    <span class="display-6 fw-bold">
                                        R$ {{ number_format($livro->preco_promocional, 2, ',', '.') }}
                                    </span>
                                    <small class="ms-2 badge bg-danger">
                                        Economize R$ {{ number_format($livro->preco - $livro->preco_promocional, 2, ',', '.') }}
                                    </small>
                                </div>
                            @else
                                <span class="display-6 fw-bold text-primary">
                                    R$ {{ number_format($livro->preco, 2, ',', '.') }}
                                </span>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            @if($livro->estoque > 0)
                                <form class="compra-form">
                                    <div class="row g-2 mb-3">
                                        <div class="col-4">
                                            <label for="quantidade" class="form-label small text-muted">Quantidade</label>
                                            <select class="form-select" id="quantidade" name="quantidade">
                                                @for($i = 1; $i <= min(10, $livro->estoque); $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-8 d-flex align-items-end">
                                            <button type="button" 
                                                    class="btn btn-success btn-lg w-100 btn-adicionar-carrinho"
                                                    data-livro-id="{{ $livro->id }}">
                                                <i class="fas fa-shopping-cart me-2"></i>
                                                Adicionar ao Carrinho
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                
                                <!-- Ações Secundárias -->
                                <div class="d-flex gap-2">
                                    @auth
                                        <button class="btn btn-outline-danger btn-favoritar"
                                                data-livro-id="{{ $livro->id }}"
                                                data-favorited="{{ $isFavorito ? 'true' : 'false' }}">
                                            <i class="fas fa-heart me-1"></i>
                                            {{ $isFavorito ? 'Remover dos Favoritos' : 'Adicionar aos Favoritos' }}
                                        </button>
                                    @endauth
                                    
                                    <button class="btn btn-outline-secondary" onclick="compartilhar()">
                                        <i class="fas fa-share me-1"></i>
                                        Compartilhar
                                    </button>
                                </div>
                            @else
                                <div class="text-center">
                                    <button class="btn btn-secondary btn-lg w-100" disabled>
                                        <i class="fas fa-times me-2"></i>
                                        Produto Indisponível
                                    </button>
                                    <small class="text-muted d-block mt-2">
                                        Avise-me quando disponível
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalhes do Livro -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Detalhes</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                @if($livro->isbn)
                                    <tr>
                                        <td class="text-muted">ISBN:</td>
                                        <td class="fw-bold">{{ $livro->isbn }}</td>
                                    </tr>
                                @endif
                                @if($livro->editora)
                                    <tr>
                                        <td class="text-muted">Editora:</td>
                                        <td>{{ $livro->editora }}</td>
                                    </tr>
                                @endif
                                @if($livro->ano_publicacao)
                                    <tr>
                                        <td class="text-muted">Ano:</td>
                                        <td>{{ $livro->ano_publicacao }}</td>
                                    </tr>
                                @endif
                                @if($livro->paginas)
                                    <tr>
                                        <td class="text-muted">Páginas:</td>
                                        <td>{{ $livro->paginas }}</td>
                                    </tr>
                                @endif
                                @if($livro->idioma)
                                    <tr>
                                        <td class="text-muted">Idioma:</td>
                                        <td>{{ $livro->idioma }}</td>
                                    </tr>
                                @endif
                                @if($livro->encadernacao)
                                    <tr>
                                        <td class="text-muted">Encadernação:</td>
                                        <td>{{ $livro->encadernacao }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Entrega</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-shipping-fast text-success me-3 fs-4"></i>
                                <div>
                                    <strong>Entrega em todo o Brasil</strong>
                                    <br>
                                    <small class="text-muted">Calcule o frete no carrinho</small>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-shield-alt text-primary me-3 fs-4"></i>
                                <div>
                                    <strong>Compra 100% segura</strong>
                                    <br>
                                    <small class="text-muted">Seus dados protegidos</small>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center">
                                <i class="fas fa-undo text-warning me-3 fs-4"></i>
                                <div>
                                    <strong>Política de troca</strong>
                                    <br>
                                    <small class="text-muted">7 dias para trocas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sinopse -->
            @if($livro->sinopse)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-book-open me-2"></i>Sinopse</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-justify lh-lg">{{ $livro->sinopse }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Livros Relacionados -->
    @if($livrosRelacionados->count() > 0)
        <hr class="my-5">
        <div class="row">
            <div class="col-12">
                <h3 class="text-center mb-4">
                    <i class="fas fa-heart text-danger me-2"></i>
                    Você também pode gostar
                </h3>
                <div class="row">
                    @foreach($livrosRelacionados as $livroRelacionado)
                        <div class="col-lg-3 col-md-6 mb-4">
                            @include('components.livro-card', ['livro' => $livroRelacionado])
                        </div>
                    @endforeach
                </div>
                
                @if($livro->categoria)
                    <div class="text-center mt-4">
                        <a href="{{ route('loja.categoria', $livro->categoria->slug) }}" 
                           class="btn btn-outline-primary">
                            Ver todos os livros de {{ $livro->categoria->nome }}
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar ao carrinho
    document.querySelector('.btn-adicionar-carrinho')?.addEventListener('click', function() {
        const livroId = this.getAttribute('data-livro-id');
        const quantidade = document.getElementById('quantidade')?.value || 1;
        adicionarAoCarrinho(livroId, quantidade);
    });
    
    // Favoritar
    document.querySelector('.btn-favoritar')?.addEventListener('click', function() {
        const livroId = this.getAttribute('data-livro-id');
        toggleFavorito(livroId, this);
    });
});

function adicionarAoCarrinho(livroId, quantidade = 1) {
    const button = document.querySelector('.btn-adicionar-carrinho');
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adicionando...';
    
    fetch(`/cart/add/${livroId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ quantity: parseInt(quantidade) })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na requisição');
        }
        return response.text(); // Laravel pode retornar redirect
    })
    .then(data => {
        showToast(`${quantidade} livro(s) adicionado(s) ao carrinho!`, 'success');
        
        // Atualizar contador do carrinho se existir
        if (typeof updateCartCount === 'function') {
            updateCartCount();
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao adicionar livro ao carrinho', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

function toggleFavorito(livroId, button) {
    const isFavorited = button.getAttribute('data-favorited') === 'true';
    
    fetch(`/livros/${livroId}/favorite`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        const icon = button.querySelector('i');
        if (data.favorited) {
            button.setAttribute('data-favorited', 'true');
            button.innerHTML = '<i class="fas fa-heart me-1"></i>Remover dos Favoritos';
            button.classList.remove('btn-outline-danger');
            button.classList.add('btn-danger');
            showToast('Livro adicionado aos favoritos!', 'success');
        } else {
            button.setAttribute('data-favorited', 'false');
            button.innerHTML = '<i class="far fa-heart me-1"></i>Adicionar aos Favoritos';
            button.classList.remove('btn-danger');
            button.classList.add('btn-outline-danger');
            showToast('Livro removido dos favoritos', 'info');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao atualizar favoritos', 'error');
    });
}

function compartilhar() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $livro->titulo }}',
            text: 'Confira este livro: {{ $livro->titulo }} por {{ $livro->autor }}',
            url: window.location.href
        });
    } else {
        // Fallback para copiar URL
        navigator.clipboard.writeText(window.location.href).then(() => {
            showToast('Link copiado para a área de transferência!', 'success');
        });
    }
}

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
</script>
@endpush

@push('styles')
<style>
.livro-imagem-detalhes {
    transition: transform 0.3s ease;
    border-radius: 8px;
}

.livro-imagem-detalhes:hover {
    transform: scale(1.02);
}

.text-justify {
    text-align: justify;
}

@media (max-width: 768px) {
    .display-5 {
        font-size: 1.8rem;
    }
    
    .display-6 {
        font-size: 1.4rem;
    }
    
    .livro-imagem-container {
        position: static !important;
    }
}

.alert {
    border: none;
    border-radius: 10px;
}

.card {
    border-radius: 10px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.btn {
    border-radius: 8px;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.badge {
    border-radius: 6px;
}
</style>
@endpush
@endsection