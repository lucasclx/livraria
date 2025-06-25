@extends('layouts.app')
@section('title', 'Meus Pedidos - Livraria Mil Páginas')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('perfil.index') }}">Meu Perfil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Meus Pedidos</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="page-title mb-0"><i class="fas fa-shopping-bag me-2"></i>Meus Pedidos</h2>
        @if($pedidos->total() > 0)
            <span class="badge bg-primary rounded-pill fs-6">{{ $pedidos->total() }} {{ Str::plural('pedido', $pedidos->total()) }}</span>
        @endif
    </div>

    @forelse($pedidos as $pedido)
        <div class="card mb-4 order-card">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-1">Pedido #{{ $pedido->order_number ?? $pedido->id }}</h5>
                        <small class="text-muted">Realizado em: {{ $pedido->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                        <span class="badge bg-{{ $pedido->status == 'delivered' ? 'success' : 'warning' }} fs-6 me-2">{{ ucfirst($pedido->status) }}</span>
                        <strong class="h5 mb-0">R$ {{ number_format($pedido->total, 2, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="mb-3">Itens do Pedido</h6>
                        @foreach($pedido->cart->items as $item)
                        <div class="d-flex align-items-center @if(!$loop->last) border-bottom pb-3 mb-3 @endif">
                            <a href="{{ route('loja.detalhes', $item->livro) }}"><img src="{{ $item->livro->imagem_url ?? asset('images/placeholder_livro.png') }}" class="rounded me-3" style="width: 60px; height: 80px; object-fit: cover;" alt="{{ $item->livro->titulo }}"></a>
                            <div class="flex-grow-1">
                                <a href="{{ route('loja.detalhes', $item->livro) }}" class="text-decoration-none fw-bold">{{ $item->livro->titulo }}</a>
                                <p class="small text-muted mb-1">{{ $item->livro->autor }}</p>
                                <p class="small mb-0">Qtd: {{ $item->quantity }} | Preço: R$ {{ number_format($item->price, 2, ',', '.') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="col-md-4">
                        <h6 class="mb-3">Detalhes da Entrega</h6>
                         @if($pedido->shipping_address)
                            <div class="mb-3">
                                <strong class="small d-block">Endereço:</strong>
                                <p class="small text-muted mb-0">
                                    {{ $pedido->shipping_address['street'] ?? 'Não informado' }}<br>
                                    {{ $pedido->shipping_address['city'] ?? '' }}, {{ $pedido->shipping_address['state'] ?? '' }} - CEP: {{ $pedido->shipping_address['postal_code'] ?? '' }}
                                </p>
                            </div>
                        @endif
                         @if($pedido->payment_method)
                            <div class="mb-3">
                                <strong class="small d-block">Pagamento:</strong>
                                <p class="small text-muted mb-0">{{ ucfirst(str_replace('_', ' ', $pedido->payment_method)) }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light d-flex justify-content-end gap-2">
                @if($pedido->status == 'pending' && $pedido->canBeCancelled())
                    <button class="btn btn-outline-danger btn-sm"><i class="fas fa-times me-1"></i>Cancelar</button>
                @endif
                <button class="btn btn-outline-secondary btn-sm"><i class="fas fa-redo me-1"></i>Comprar Novamente</button>
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <i class="fas fa-shopping-bag fa-5x text-muted mb-4"></i>
            <h4>Nenhum pedido encontrado.</h4>
            <p class="text-muted">Você ainda não fez nenhuma compra.</p>
            <a href="{{ route('loja.catalogo') }}" class="btn btn-primary mt-2">Explorar Livros</a>
        </div>
    @endforelse

    <div class="d-flex justify-content-center">
        {{ $pedidos->links() }}
    </div>
</div>
@endsection