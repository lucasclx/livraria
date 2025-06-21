{{-- resources/views/components/livro-card.blade.php --}}
<div class="card livro-card h-100">
    <div class="position-relative">
        @if($livro->imagem)
            <img src="{{ $livro->imagem_url }}" class="livro-image" alt="{{ $livro->titulo }}">
        @else
            <div class="livro-image bg-light d-flex align-items-center justify-content-center">
                <i class="fas fa-book fa-3x text-muted"></i>
            </div>
        @endif
        
        <!-- Badges -->
        <div class="position-absolute top-0 start-0 m-2">
            @if($livro->created_at->diffInDays() < 30)
                <span class="badge bg-success mb-1 d-block">NOVO</span>
            @endif
            @if($livro->tem_promocao)
                <span class="badge bg-danger">{{ $livro->getDesconto() }}% OFF</span>
            @elseif($livro->preco < 30)
                <span class="badge bg-warning">OFERTA</span>
            @endif
        </div>
        
        <!-- Status do Estoque -->
        <div class="position-absolute top-0 end-0 m-2">
            @php $status = $livro->status_estoque @endphp
            <span class="badge bg-{{ $status['cor'] }}">{{ $status['texto'] }}</span>
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
                <a href="{{ route('loja.categoria', $livro->categoria->slug ?? $livro->categoria->nome) }}" 
                   class="badge bg-secondary text-decoration-none">
                    {{ $livro->categoria->nome }}
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
                    @if($livro->tem_promocao)
                        <span class="h5 text-success mb-0 fw-bold">
                            R$ {{ number_format($livro->preco_promocional, 2, ',', '.') }}
                        </span>
                        <small class="text-muted text-decoration-line-through ms-1">
                            {{ $livro->preco_formatado }}
                        </small>
                    @else
                        <span class="h5 text-success mb-0 fw-bold">{{ $livro->preco_formatado }}</span>
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

<style>
.livro-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.livro-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
}

.livro-image {
    height: 250px;
    object-fit: cover;
    width: 100%;
    transition: transform 0.3s ease;
}

.livro-card:hover .livro-image {
    transform: scale(1.05);
}

.livro-card .favorite-btn {
    transition: opacity 0.3s ease;
}

.livro-card:hover .favorite-btn {
    opacity: 1 !important;
}
</style>