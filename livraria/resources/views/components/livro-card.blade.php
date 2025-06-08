{{-- resources/views/components/livro-card.blade.php --}}
<div class="card livro-card h-100 shadow-sm">
    <div class="position-relative">
        <!-- Imagem do Livro -->
        <div class="livro-image-container">
            @if($livro->imagem)
                <img src="{{ $livro->imagem_url }}" class="card-img-top livro-image" alt="{{ $livro->titulo }}">
            @else
                <div class="livro-image placeholder-image d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <i class="fas fa-book fa-3x text-muted mb-2"></i>
                        <p class="text-muted small mb-0">Sem Capa</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Badge de Oferta -->
        @if(isset($showOffer) && $showOffer && $livro->preco < 50)
            <div class="position-absolute top-0 start-0 m-2">
                <span class="badge bg-success">
                    <i class="fas fa-tags"></i> Oferta
                </span>
            </div>
        @endif

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
                <button class="btn btn-sm btn-light rounded-circle favorite-btn" 
                        data-livro-id="{{ $livro->id }}"
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
        <h6 class="card-title livro-titulo mb-2">
            <a href="{{ route('loja.detalhes', $livro) }}" class="text-decoration-none text-dark">
                {{ Str::limit($livro->titulo, 50) }}
            </a>
        </h6>

        <!-- Autor -->
        <p class="text-muted small mb-2">
            <i class="fas fa-user-edit"></i> {{ $livro->autor }}
        </p>

        <!-- Sinopse (opcional) -->
        @if($livro->sinopse && strlen($livro->sinopse) > 50)
            <p class="card-text small text-muted mb-3 livro-sinopse">
                {{ Str::limit($livro->sinopse, 80) }}
            </p>
        @endif

        <!-- Espaçador flexível -->
        <div class="mt-auto">
            <!-- Preço -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="preco">
                    <span class="h5 text-success mb-0 fw-bold">{{ $livro->preco_formatado }}</span>
                    @if(isset($showOffer) && $showOffer)
                        <small class="text-muted text-decoration-line-through ms-1">
                            R$ {{ number_format($livro->preco * 1.2, 2, ',', '.') }}
                        </small>
                    @endif
                </div>
                <small class="text-muted">
                    <i class="fas fa-boxes"></i> {{ $livro->estoque }}
                </small>
            </div>

            <!-- Botões de Ação -->
            <div class="livro-actions">
                @if($livro->estoque > 0)
                    <form method="POST" action="{{ route('cart.add', $livro) }}" class="d-inline-block w-100">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-primary btn-sm w-100 mb-2">
                            <i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho
                        </button>
                    </form>
                @else
                    <button class="btn btn-secondary btn-sm w-100 mb-2" disabled>
                        <i class="fas fa-ban"></i> Indisponível
                    </button>
                @endif
                
                <a href="{{ route('loja.detalhes', $livro) }}" 
                   class="btn btn-outline-primary btn-sm w-100">
                    <i class="fas fa-eye"></i> Ver Detalhes
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.livro-card {
    transition: all 0.3s ease;
    border: none;
    overflow: hidden;
}

.livro-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.livro-image-container {
    height: 250px;
    overflow: hidden;
    position: relative;
}

.livro-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.livro-card:hover .livro-image {
    transform: scale(1.05);
}

.placeholder-image {
    height: 100%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
}

.livro-titulo a:hover {
    color: var(--bs-primary) !important;
}

.livro-sinopse {
    line-height: 1.4;
}

.favorite-btn {
    opacity: 0;
    transition: opacity 0.3s ease;
    border: none !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.livro-card:hover .favorite-btn {
    opacity: 1;
}

.livro-actions .btn {
    transition: all 0.2s ease;
}

.livro-actions .btn:hover {
    transform: translateY(-1px);
}

.badge {
    font-size: 0.7rem;
    padding: 0.4rem 0.6rem;
}

.preco {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
}
</style>

<script>
// Funcionalidade dos favoritos
document.addEventListener('DOMContentLoaded', function() {
    // Botões de favorito
    document.querySelectorAll('.favorite-btn').forEach(button => {
        button.addEventListener('click', function() {
            const livroId = this.dataset.livroId;
            const icon = this.querySelector('i');
            
            fetch(`/livros/${livroId}/favorite`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.favorited) {
                    icon.classList.remove('far');
                    icon.classList.add('fas', 'text-danger');
                } else {
                    icon.classList.remove('fas', 'text-danger');
                    icon.classList.add('far');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
            });
        });
    });

    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>