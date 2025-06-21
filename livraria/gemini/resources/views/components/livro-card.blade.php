{{-- resources/views/components/livro-card.blade.php --}}
@props(['livro', 'showAdminActions' => false])

<div class="col-lg-3 col-md-4 col-sm-6 mb-4">
    <div class="card book-card h-100">
        <!-- Book Cover -->
        <div class="position-relative book-cover">
            @if($livro->imagem)
                <img src="{{ $livro->imagem_url }}" class="card-img-top" alt="{{ $livro->titulo }}" 
                     style="height: 250px; object-fit: cover;">
            @else
                <img src="{{ asset('images/milpag.jpeg') }}" 
                     class="card-img-top placeholder-image" 
                     alt="Capa não disponível - {{ $livro->titulo }}" 
                     style="height: 250px; object-fit: cover;">
            @endif
            
            <!-- Favorite Icon (topo esquerdo) -->
            <div class="position-absolute top-0 start-0 m-2">
                @auth
                    @php
                        $isFav = auth()->user()->favorites()->where('livro_id', $livro->id)->exists();
                    @endphp
                    <button class="btn btn-sm btn-light rounded-circle" data-favorite-button="{{ $livro->id }}" onclick="toggleFavorite({{ $livro->id }})"
                            data-bs-toggle="tooltip" title="Adicionar aos favoritos">
                        <i class="{{ $isFav ? 'fas' : 'far' }} fa-heart" @if($isFav) style="color:#dc3545" @endif></i>
                    </button>
                @endauth
            </div>
            
            <!-- Stock Status Badge (topo direito) -->
            <div class="position-absolute top-0 end-0 m-2">
                @php
                    $statusEstoque = $livro->status_estoque ?? [
                        'status' => $livro->estoque > 5 ? 'disponivel' : ($livro->estoque > 0 ? 'estoque_baixo' : 'sem_estoque'),
                        'cor' => $livro->estoque > 5 ? 'success' : ($livro->estoque > 0 ? 'warning' : 'danger'),
                        'texto' => $livro->estoque > 5 ? 'Disponível' : ($livro->estoque > 0 ? 'Estoque Baixo' : 'Sem Estoque')
                    ];
                @endphp
                <span class="badge badge-stock-{{ $statusEstoque['status'] == 'disponivel' ? 'ok' : ($statusEstoque['status'] == 'estoque_baixo' ? 'low' : 'out') }}">
                    {{ $statusEstoque['texto'] }}
                </span>
            </div>

            <!-- Promotional/New Badge (esquerda, abaixo do coração) -->
            <div class="position-absolute start-0 m-2" style="top: 50px;">
                @if($livro->created_at->diffInDays() < 30)
                    <span class="badge bg-success mb-1 d-block promo-badge">NOVO</span>
                @endif
                @if($livro->tem_promocao ?? ($livro->preco < 30))
                    <span class="badge bg-warning text-dark d-block promo-badge">OFERTA</span>
                @endif
            </div>
        </div>
        
        <!-- Book Info -->
        <div class="card-body d-flex flex-column">
            <div class="mb-auto">
                <h6 class="card-title fw-bold mb-2" style="min-height: 3rem; line-height: 1.5;">
                    <a href="{{ route('loja.detalhes', $livro) }}" class="text-decoration-none text-dark">
                        {{ Str::limit($livro->titulo, 50) }}
                    </a>
                </h6>
                
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="fas fa-feather-alt me-1"></i>
                        <strong>{{ $livro->autor }}</strong>
                    </small>
                </div>
                
                @if($livro->editora)
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="fas fa-building me-1"></i>
                            {{ $livro->editora }}
                        </small>
                    </div>
                @endif
                
                @if($livro->categoria)
                    <div class="mb-2">
                        <span class="badge badge-category small">
                            {{ $livro->categoria->nome }}
                        </span>
                    </div>
                @endif
                
                @if($livro->sinopse && !$showAdminActions)
                    <p class="card-text small text-muted mb-3">
                        {{ Str::limit($livro->sinopse, 100) }}
                    </p>
                @endif

                <!-- Ratings -->
                @if(isset($livro->total_avaliacoes) && $livro->total_avaliacoes > 0)
                    <div class="mb-2">
                        <div class="d-flex align-items-center">
                            <div class="text-warning me-2">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= ($livro->avaliacao_media ?? 0))
                                        <i class="fas fa-star"></i>
                                    @else
                                        <i class="far fa-star"></i>
                                    @endif
                                @endfor
                            </div>
                            <small class="text-muted">({{ $livro->total_avaliacoes }})</small>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Price -->
            <div class="mt-auto">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        @if($livro->tem_promocao ?? false)
                            <small class="text-muted text-decoration-line-through">
                                R$ {{ number_format($livro->preco, 2, ',', '.') }}
                            </small><br>
                            <span class="price fw-bold text-success">
                                R$ {{ number_format($livro->preco_promocional, 2, ',', '.') }}
                            </span>
                        @else
                            <span class="price fw-bold text-success">{{ $livro->preco_formatado }}</span>
                        @endif
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-boxes"></i> {{ $livro->estoque }}
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Card Footer -->
        <div class="card-footer bg-transparent border-0 pt-0">
            @if($showAdminActions)
                <!-- Admin Actions -->
                <div class="d-flex justify-content-between gap-1">
                    <a href="{{ route('livros.show', $livro) }}" 
                       class="btn btn-outline-info btn-sm flex-fill" 
                       data-bs-toggle="tooltip" title="Ver detalhes">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('livros.edit', $livro) }}" 
                       class="btn btn-outline-warning btn-sm flex-fill" 
                       data-bs-toggle="tooltip" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('livros.destroy', $livro) }}" method="POST" class="flex-fill">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                                data-bs-toggle="tooltip" title="Excluir"
                                onclick="return confirm('Tem certeza que deseja remover este livro?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            @else
                <!-- Customer Actions -->
                <div class="d-grid gap-2">
                    @if($livro->estoque > 0)
                        <form method="POST" action="{{ route('cart.add', $livro) }}" class="form-add-cart">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-shopping-cart me-1"></i>
                                Adicionar ao Carrinho
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-secondary btn-sm w-100" disabled>
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
            @endif
        </div>
    </div>
</div>

@once
@push('styles')
<style>
.book-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    background: white;
}

.book-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.book-cover img {
    transition: transform 0.3s ease;
}

.book-card:hover .book-cover img {
    transform: scale(1.05);
}

/* Estilos específicos para a imagem placeholder milpag.jpeg */
.placeholder-image {
    transition: all 0.3s ease;
    opacity: 0.95;
    filter: brightness(0.9) contrast(1.1);
}

.book-card:hover .placeholder-image {
    opacity: 1;
    transform: scale(1.05);
    filter: brightness(1) contrast(1);
}

/* Tags de estoque */
.badge-stock-ok {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.badge-stock-low {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    color: #212529;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.badge-stock-out {
    background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
    color: white;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Tags promocionais */
.promo-badge {
    font-size: 0.7rem !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    border-radius: 4px !important;
    padding: 0.35rem 0.6rem !important;
    min-width: 50px;
    text-align: center;
}

/* Espaçamento entre badges promocionais */
.promo-badge + .promo-badge {
    margin-top: 4px !important;
}

/* Tag de categoria */
.badge-category {
    background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
    color: white;
    border-radius: 20px;
    padding: 0.35rem 0.8rem;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Preço */
.price {
    font-size: 1.1rem;
    color: #28a745 !important;
}

/* Botão de favoritos */
.btn-favorite {
    transition: all 0.3s ease;
    opacity: 0.8;
}

.book-card:hover .btn-favorite {
    opacity: 1;
}

/* Melhorias gerais nos badges */
.badge {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    border: none;
}

/* Hover effects nos badges */
.promo-badge:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}

/* Cores personalizadas para badges promocionais */
.bg-success.promo-badge {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    border: 1px solid rgba(255,255,255,0.2);
}

.bg-warning.promo-badge {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
    border: 1px solid rgba(255,255,255,0.2);
    color: #212529 !important;
    font-weight: 700 !important;
}

/* Efeito especial para imagem placeholder */
.placeholder-image {
    position: relative;
    box-shadow: inset 0 0 20px rgba(0,0,0,0.1);
}

.placeholder-image::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        135deg,
        transparent 0%,
        rgba(255,255,255,0.1) 50%,
        transparent 100%
    );
    opacity: 0;
    transition: opacity 0.3s ease;
}

.book-card:hover .placeholder-image::after {
    opacity: 1;
}

/* Animação para badges de oferta */
@keyframes badge-pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.promo-badge.bg-warning {
    animation: badge-pulse 2s infinite;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .book-card {
        margin-bottom: 1.5rem;
    }
    
    .book-cover {
        height: 200px !important;
    }
    
    .book-cover img {
        height: 200px !important;
    }
    
    .promo-badge {
        font-size: 0.6rem !important;
        padding: 0.25rem 0.5rem !important;
        min-width: 45px;
    }
    
    .badge-stock-ok,
    .badge-stock-low,
    .badge-stock-out {
        font-size: 0.6rem;
        padding: 0.25rem 0.5rem;
    }
    
    .placeholder-image {
        filter: brightness(0.95) contrast(1.05);
    }
    
    .book-card:hover .placeholder-image {
        filter: brightness(1) contrast(1);
        transform: scale(1.02); /* Menos zoom em mobile */
    }
}

/* Melhorias visuais adicionais */
.card-title a:hover {
    color: #495057 !important;
    transition: color 0.3s ease;
}

.btn:focus {
    box-shadow: none;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,123,255,0.3);
}

.btn-outline-secondary:hover {
    transform: translateY(-1px);
}
</style>
@endpush

@push('scripts')
<script>
// Função para favoritar livros
function toggleFavorite(livroId) {
    @auth
    fetch('/livros/' + livroId + '/favorite', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const button = document.querySelector('[data-favorite-button="' + livroId + '"]');
        const icon = button.querySelector('i');
        if (data.favorited) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            icon.style.color = '#dc3545';
            
            // Animação de sucesso
            button.style.transform = 'scale(1.2)';
            setTimeout(() => {
                button.style.transform = 'scale(1)';
            }, 200);
            
            showToast('Livro adicionado aos favoritos!', 'success');
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            icon.style.color = '';
            showToast('Livro removido dos favoritos!', 'info');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao atualizar favoritos', 'danger');
    });
    @else
    window.location.href = '{{ route("login") }}';
    @endauth
}

// Interceptar submissão dos formulários de adicionar ao carrinho
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.form-add-cart').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            
            // Mostrar loading
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Adicionando...';
            button.disabled = true;
            
            // Enviar formulário via fetch
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    // Animação de sucesso
                    button.innerHTML = '<i class="fas fa-check me-1"></i>Adicionado!';
                    button.classList.remove('btn-primary');
                    button.classList.add('btn-success');
                    
                    showToast('Livro adicionado ao carrinho!', 'success');
                    
                    // Restaurar após 2 segundos
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.classList.remove('btn-success');
                        button.classList.add('btn-primary');
                        button.disabled = false;
                    }, 2000);
                } else if (data && data.message) {
                    showToast(data.message, 'warning');
                    button.innerHTML = originalText;
                    button.disabled = false;
                } else {
                    showToast('Livro adicionado com sucesso!', 'success');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showToast('Erro ao adicionar livro', 'danger');
                button.innerHTML = originalText;
                button.disabled = false;
            });
        });
    });
    
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Adicionar efeito hover aos cards
    document.querySelectorAll('.book-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
    });
});

function showToast(message, type = 'info') {
    // Criar container se não existir
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Ícones para cada tipo
    const icons = {
        success: 'check-circle',
        danger: 'exclamation-triangle',
        warning: 'exclamation-circle',
        info: 'info-circle'
    };
    
    // Criar toast
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${icons[type] || 'info'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Mostrar toast
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    
    // Remover após esconder
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}
</script>
@endpush
@endonce