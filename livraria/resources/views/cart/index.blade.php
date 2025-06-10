@extends('layouts.app')
@section('title', 'Meu Carrinho - Livraria Mil Páginas')

@section('content')
<div class="cart-page">
    <!-- Header do Carrinho -->
    <div class="cart-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="page-title mb-0">
                    <i class="fas fa-shopping-cart text-primary"></i> 
                    Meu Carrinho
                </h1>
                @if($items->count() > 0)
                    <p class="text-muted mb-0">{{ $itemCount }} {{ $itemCount == 1 ? 'item' : 'itens' }} no seu carrinho</p>
                @endif
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('loja.catalogo') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Continuar Comprando
                </a>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($items->count() > 0)
        <div class="row">
            <!-- Lista de Itens -->
            <div class="col-lg-8">
                <div class="cart-items">
                    @foreach($items as $item)
                        <div class="cart-item card mb-3" data-item-id="{{ $item->id }}">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <!-- Imagem do Livro -->
                                    <div class="col-md-2 col-3">
                                        <div class="item-image">
                                            @if($item->livro->imagem)
                                                <img src="{{ $item->livro->imagem_url }}" 
                                                     alt="{{ $item->livro->titulo }}" 
                                                     class="img-fluid rounded">
                                            @else
                                                <div class="no-image d-flex align-items-center justify-content-center rounded">
                                                    <i class="fas fa-book fa-2x text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Informações do Livro -->
                                    <div class="col-md-4 col-9">
                                        <div class="item-info">
                                            <h6 class="item-title mb-1">
                                                <a href="{{ route('loja.detalhes', $item->livro) }}" 
                                                   class="text-decoration-none text-dark">
                                                    {{ $item->livro->titulo }}
                                                </a>
                                            </h6>
                                            <p class="text-muted small mb-1">
                                                <i class="fas fa-user-edit"></i> {{ $item->livro->autor }}
                                            </p>
                                            @if($item->livro->categoria)
                                                <span class="badge bg-secondary small">{{ $item->livro->categoria->nome }}</span>
                                            @endif
                                            
                                            <!-- Status do Estoque -->
                                            <div class="stock-status mt-2">
                                                @if($item->livro->estoque >= $item->quantity)
                                                    <small class="text-success">
                                                        <i class="fas fa-check-circle"></i> Disponível
                                                    </small>
                                                @else
                                                    <small class="text-danger">
                                                        <i class="fas fa-exclamation-triangle"></i> Estoque limitado
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Preço Unitário -->
                                    <div class="col-md-2 col-6">
                                        <div class="item-price text-center">
                                            <strong class="text-success">{{ $item->livro->preco_formatado }}</strong>
                                            <br><small class="text-muted">por unidade</small>
                                        </div>
                                    </div>

                                    <!-- Quantidade -->
                                    <div class="col-md-2 col-6">
                                        <div class="quantity-controls">
                                            <form method="POST" action="{{ route('cart.item.update', $item) }}" 
                                                  class="quantity-form d-flex align-items-center justify-content-center">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn" 
                                                        data-action="decrease">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" name="quantity" 
                                                       class="form-control form-control-sm text-center quantity-input mx-1" 
                                                       value="{{ $item->quantity }}" 
                                                       min="1" 
                                                       max="{{ $item->livro->estoque }}"
                                                       style="width: 60px;">
                                                <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn" 
                                                        data-action="increase">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Subtotal e Ações -->
                                    <div class="col-md-2 col-12">
                                        <div class="item-total text-center">
                                            <strong class="h6 text-success">
                                                R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}
                                            </strong>
                                            <div class="item-actions mt-2">
                                                <form method="POST" action="{{ route('cart.item.remove', $item) }}" 
                                                      class="d-inline-block">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="tooltip" title="Remover item"
                                                            onclick="return confirm('Deseja remover este item do carrinho?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Botão Limpar Carrinho -->
                    <div class="cart-actions text-end mb-4">
                        <form method="POST" action="{{ route('cart.clear') }}" class="d-inline-block">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                    onclick="return confirm('Deseja limpar todo o carrinho?')">
                                <i class="fas fa-trash-alt"></i> Limpar Carrinho
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Resumo do Pedido -->
            <div class="col-lg-4">
                <div class="order-summary sticky-top">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calculator"></i> Resumo do Pedido
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Itens -->
                            <div class="summary-line d-flex justify-content-between mb-2">
                                <span>Subtotal ({{ $itemCount }} itens):</span>
                                <span>R$ {{ number_format($total, 2, ',', '.') }}</span>
                            </div>

                            <!-- Frete -->
                            <div class="summary-line d-flex justify-content-between mb-2">
                                <span>Frete:</span>
                                <span class="text-success">A calcular</span>
                            </div>

                            <!-- Cupom de Desconto -->
                            <div class="coupon-section mb-3">
                                <label class="form-label small">Cupom de Desconto:</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" placeholder="Digite o cupom" id="couponInput">
                                    <button class="btn btn-outline-secondary" id="applyCoupon">
                                        Aplicar
                                    </button>
                                </div>
                                <div id="couponMessage" class="small mt-1"></div>
                            </div>

                            <hr>
                            
                            <!-- Total -->
                            <div class="summary-total d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong class="text-success h5">R$ {{ number_format($total, 2, ',', '.') }}</strong>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="d-grid gap-2">
                                @auth
                                    <a href="{{ route('checkout') }}" class="btn btn-primary btn-lg">
                                        <i class="fas fa-credit-card"></i> Finalizar Compra
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                                        <i class="fas fa-sign-in-alt"></i> Entrar para Comprar
                                    </a>
                                @endauth
                                
                                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#shareCartModal">
                                    <i class="fas fa-share-alt"></i> Compartilhar Carrinho
                                </button>
                            </div>

                            <!-- Segurança -->
                            <div class="security-info mt-3 p-2 bg-light rounded">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="fas fa-shield-alt text-success me-2"></i>
                                    <small>Compra 100% segura</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-truck text-info me-2"></i>
                                    <small>Entrega rápida em todo Brasil</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sugestões de Produtos -->
        @if($sugestoes->count() > 0)
            <div class="suggestions-section mt-5">
                <h3 class="section-title mb-4">
                    <i class="fas fa-lightbulb text-warning"></i> Você também pode gostar
                </h3>
                <div class="row">
                    @foreach($sugestoes as $sugestao)
                        <div class="col-lg-3 col-md-6 mb-4">
                            @include('components.livro-card', ['livro' => $sugestao])
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @else
        <!-- Carrinho Vazio -->
        <div class="empty-cart text-center py-5">
            <div class="empty-cart-icon mb-4">
                <i class="fas fa-shopping-cart fa-5x text-muted"></i>
            </div>
            <h3 class="mb-3">Seu carrinho está vazio</h3>
            <p class="text-muted mb-4">
                Que tal começar adicionando alguns livros incríveis?
            </p>
            <div class="empty-cart-actions">
                <a href="{{ route('loja.catalogo') }}" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-book"></i> Explorar Livros
                </a>
                <a href="{{ route('loja.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-home"></i> Página Inicial
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Modal de Compartilhar Carrinho -->
<div class="modal fade" id="shareCartModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Compartilhar Carrinho</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Compartilhe sua seleção de livros com amigos:</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-success" onclick="shareWhatsApp()">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </button>
                    <button class="btn btn-primary" onclick="shareFacebook()">
                        <i class="fab fa-facebook"></i> Facebook
                    </button>
                    <button class="btn btn-secondary" onclick="copyCartLink()">
                        <i class="fas fa-copy"></i> Copiar Link
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cart-page {
    min-height: 70vh;
}

.cart-item {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.cart-item:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.item-image {
    height: 100px;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    height: 100px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
}

.quantity-input {
    border-left: none;
    border-right: none;
}

.quantity-btn {
    border-radius: 0;
}

.quantity-controls {
    max-width: 120px;
    margin: 0 auto;
}

.order-summary {
    top: 2rem;
}

.summary-line {
    font-size: 0.9rem;
}

.summary-total {
    font-size: 1.1rem;
}

.security-info {
    border-left: 3px solid var(--bs-success);
}

.empty-cart {
    min-height: 400px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.suggestions-section {
    border-top: 2px solid #e9ecef;
    padding-top: 2rem;
}

.section-title {
    font-family: 'Playfair Display', serif;
    color: var(--bs-dark);
}

@media (max-width: 768px) {
    .cart-item .row > div {
        margin-bottom: 1rem;
    }
    
    .quantity-controls {
        max-width: 100px;
    }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Controles de quantidade
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;
            const form = this.closest('.quantity-form');
            const input = form.querySelector('.quantity-input');
            const currentValue = parseInt(input.value);
            const max = parseInt(input.max);
            
            if (action === 'increase' && currentValue < max) {
                input.value = currentValue + 1;
                form.submit();
            } else if (action === 'decrease' && currentValue > 1) {
                input.value = currentValue - 1;
                form.submit();
            }
        });
    });

    // Auto-submit quando quantidade muda
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            if (this.value >= 1 && this.value <= this.max) {
                this.closest('.quantity-form').submit();
            }
        });
    });

    // Cupom de desconto
    document.getElementById('applyCoupon').addEventListener('click', function() {
        const coupon = document.getElementById('couponInput').value;
        const message = document.getElementById('couponMessage');
        
        if (coupon) {
            // Simulação de validação de cupom
            const validCoupons = ['LIVROS10', 'DESCONTO15', 'PRIMEIRA20'];
            
            if (validCoupons.includes(coupon.toUpperCase())) {
                message.innerHTML = '<span class="text-success">✓ Cupom aplicado com sucesso!</span>';
            } else {
                message.innerHTML = '<span class="text-danger">✗ Cupom inválido</span>';
            }
        }
    });

    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Funções de compartilhamento
function shareWhatsApp() {
    const text = "Confira minha seleção de livros na Livraria Mil Páginas!";
    const url = window.location.href;
    window.open(`https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`);
}

function shareFacebook() {
    const url = window.location.href;
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`);
}

function copyCartLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Link do carrinho copiado!');
    });
}
</script>
@endpush
@endsection