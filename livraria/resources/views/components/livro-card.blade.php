{{-- resources/views/components/livro-card.blade.php --}}
<div class="livro-card h-100" data-livro-id="{{ $livro->id }}" data-em-estoque="{{ $livro->estoque > 0 ? 'true' : 'false' }}">
    <div class="card h-100 shadow-sm">
        <div class="position-relative">
            <!-- Imagem do Livro -->
            <a href="{{ route('loja.detalhes', $livro) }}">
                <img src="{{ $livro->imagem_url }}" 
                     class="card-img-top livro-imagem" 
                     alt="{{ $livro->titulo }}"
                     style="height: 250px; object-fit: cover;">
            </a>
            
            <!-- Badge de Promoção -->
            @if($livro->tem_promocao)
                <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                    -{{ $livro->getDesconto() }}%
                </span>
            @endif
            
            <!-- Botão Favoritar -->
            @auth
                <button class="btn btn-outline-danger btn-sm position-absolute top-0 end-0 m-2 btn-favoritar"
                        data-livro-id="{{ $livro->id }}"
                        title="Adicionar aos favoritos">
                    <i class="fas fa-heart"></i>
                </button>
            @endauth
            
            <!-- Status do Estoque -->
            <div class="position-absolute bottom-0 start-0 m-2">
                @if($livro->estoque > 0)
                    @if($livro->estoque <= ($livro->estoque_minimo ?? 5))
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-exclamation-triangle me-1"></i>Últimas unidades
                        </span>
                    @else
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Disponível
                        </span>
                    @endif
                @else
                    <span class="badge bg-secondary">
                        <i class="fas fa-times me-1"></i>Esgotado
                    </span>
                @endif
            </div>
        </div>
        
        <div class="card-body d-flex flex-column">
            <!-- Categoria -->
            @if($livro->categoria)
                <div class="mb-2">
                    <a href="{{ route('loja.categoria', $livro->categoria->slug) }}" 
                       class="text-decoration-none">
                        <small class="text-muted">
                            <i class="fas fa-tag me-1"></i>{{ $livro->categoria->nome }}
                        </small>
                    </a>
                </div>
            @endif
            
            <!-- Título -->
            <h6 class="card-title">
                <a href="{{ route('loja.detalhes', $livro) }}" 
                   class="text-decoration-none text-dark">
                    {{ Str::limit($livro->titulo, 60) }}
                </a>
            </h6>
            
            <!-- Autor -->
            <p class="card-text text-muted small mb-2">
                <i class="fas fa-user me-1"></i>{{ $livro->autor }}
            </p>
            
            <!-- Avaliação -->
            @if($livro->total_avaliacoes > 0)
                <div class="mb-2">
                    <div class="d-flex align-items-center">
                        <div class="text-warning me-2">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $livro->avaliacao_media)
                                    <i class="fas fa-star"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </div>
                        <small class="text-muted">
                            ({{ $livro->total_avaliacoes }})
                        </small>
                    </div>
                </div>
            @endif
            
            <!-- Preço -->
            <div class="mt-auto">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="preco">
                        @if($livro->tem_promocao)
                            <span class="text-muted text-decoration-line-through small">
                                R$ {{ number_format($livro->preco, 2, ',', '.') }}
                            </span>
                            <br>
                            <span class="h6 text-danger mb-0">
                                R$ {{ number_format($livro->preco_promocional, 2, ',', '.') }}
                            </span>
                        @else
                            <span class="h6 text-primary mb-0">
                                {{ $livro->preco_formatado }}
                            </span>
                        @endif
                    </div>
                </div>
                
                <!-- Botões de Ação -->
                <div class="d-grid gap-2">
                    @if($livro->estoque > 0)
                        <button type="button" 
                                class="btn btn-primary btn-sm btn-adicionar-carrinho"
                                data-livro-id="{{ $livro->id }}">
                            <i class="fas fa-shopping-cart me-1"></i>
                            Adicionar ao Carrinho
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary btn-sm" disabled>
                            <i class="fas fa-times me-1"></i>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar ao carrinho
    document.querySelectorAll('.btn-adicionar-carrinho').forEach(button => {
        button.addEventListener('click', function() {
            const livroId = this.getAttribute('data-livro-id');
            adicionarAoCarrinho(livroId);
        });
    });
    
    // Favoritar
    document.querySelectorAll('.btn-favoritar').forEach(button => {
        button.addEventListener('click', function() {
            const livroId = this.getAttribute('data-livro-id');
            toggleFavorito(livroId, this);
        });
    });
});

function adicionarAoCarrinho(livroId) {
    fetch(`/cart/add/${livroId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ quantity: 1 })
    })
    .then(response => response.json())
    .then(data => {
        // Mostrar mensagem de sucesso
        showToast('Livro adicionado ao carrinho!', 'success');
        
        // Atualizar contador do carrinho se existir
        if (typeof updateCartCount === 'function') {
            updateCartCount();
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao adicionar livro ao carrinho', 'error');
    });
}

function toggleFavorito(livroId, button) {
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
            icon.classList.remove('far');
            icon.classList.add('fas');
            button.classList.remove('btn-outline-danger');
            button.classList.add('btn-danger');
            showToast('Livro adicionado aos favoritos!', 'success');
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
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

function showToast(message, type = 'info') {
    // Implementação simples de toast/notificação
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'} me-2"></i>
            ${message}
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Remover automaticamente após 3 segundos
    setTimeout(() => {
        if (toast && toast.parentElement) {
            toast.remove();
        }
    }, 3000);
}
</script>
@endpush

@push('styles')
<style>
.livro-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.livro-card:hover {
    transform: translateY(-5px);
}

.livro-card:hover .card {
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.livro-imagem {
    transition: transform 0.3s ease;
}

.livro-card:hover .livro-imagem {
    transform: scale(1.05);
}

.btn-favoritar {
    opacity: 0.8;
    transition: opacity 0.2s ease;
}

.btn-favoritar:hover {
    opacity: 1;
}

.preco {
    min-height: 50px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
</style>
@endpush