@extends('layouts.app')

@section('title', $livro->titulo . ' - ' . $livro->autor . ' - Livraria Mil Páginas')

@section('content')
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('loja.index') }}">Início</a></li>
            <li class="breadcrumb-item"><a href="{{ route('loja.catalogo') }}">Catálogo</a></li>
            @if($livro->categoria)
                <li class="breadcrumb-item">
                    {{-- Este link ainda leva para a categoria, o que é o padrão para breadcrumbs --}}
                    <a href="{{ route('loja.categoria', $livro->categoria->slug) }}">
                        {{ $livro->categoria->nome }}
                    </a>
                </li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $livro->titulo }}</li>
        </ol>
    </nav>

    <div class="row">
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

        <div class="col-lg-8 col-md-7">
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
                        @include('components.livro-card', ['livro' => $livroRelacionado])
                    @endforeach
                </div>
                
                {{-- BOTÃO ALTERADO --}}
                <div class="text-center mt-4">
                    <a href="{{ route('loja.catalogo') }}" 
                       class="btn btn-outline-primary">
                        Ver todo o Catálogo
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>

            </div>
        </div>
    @endif
</div>

{{-- O restante do seu código (push scripts e styles) permanece o mesmo --}}
@push('scripts')
<script>
// SEU CÓDIGO JAVASCRIPT AQUI
</script>
@endpush

@push('styles')
<style>
/* SEU CÓDIGO CSS AQUI */
</style>
@endpush
@endsection