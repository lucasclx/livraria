@extends('layouts.app')

@section('title', "Busca por '{$termo}' - Livraria Mil Páginas")

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('loja.index') }}">Início</a></li>
            <li class="breadcrumb-item"><a href="{{ route('loja.catalogo') }}">Catálogo</a></li>
            <li class="breadcrumb-item active" aria-current="page">Busca</li>
        </ol>
    </nav>

    <!-- Header da Busca -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="busca-header text-center py-4 bg-light rounded">
                <h1 class="display-6 mb-3">
                    <i class="fas fa-search text-primary me-2"></i>
                    Resultados da Busca
                </h1>
                <p class="lead text-muted mb-0">
                    Você buscou por: <strong>"{{ $termo }}"</strong>
                </p>
                @if($livros->total() > 0)
                    <p class="text-muted">
                        {{ $livros->total() }} {{ $livros->total() == 1 ? 'resultado encontrado' : 'resultados encontrados' }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Nova Busca -->
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <form action="{{ route('loja.buscar') }}" method="GET" class="d-flex">
                <input type="text" 
                       name="q" 
                       class="form-control form-control-lg me-2" 
                       placeholder="Digite o que você procura..." 
                       value="{{ $termo }}"
                       required>
                <button type="submit" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Resultados -->
    @if($livros->count() > 0)
        <!-- Filtros e Ordenação -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <span class="text-muted me-3">Ordenar por:</span>
                    <select class="form-select form-select-sm" style="width: auto;" onchange="ordenarResultados(this.value)">
                        <option value="relevancia">Relevância</option>
                        <option value="titulo-asc" {{ request('ordem') == 'titulo' && request('direcao') == 'asc' ? 'selected' : '' }}>
                            Nome (A-Z)
                        </option>
                        <option value="titulo-desc" {{ request('ordem') == 'titulo' && request('direcao') == 'desc' ? 'selected' : '' }}>
                            Nome (Z-A)
                        </option>
                        <option value="preco-asc" {{ request('ordem') == 'preco' && request('direcao') == 'asc' ? 'selected' : '' }}>
                            Menor preço
                        </option>
                        <option value="preco-desc" {{ request('ordem') == 'preco' && request('direcao') == 'desc' ? 'selected' : '' }}>
                            Maior preço
                        </option>
                    </select>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <span class="text-muted">
                    Mostrando {{ $livros->firstItem() ?? 0 }} - {{ $livros->lastItem() ?? 0 }} 
                    de {{ $livros->total() }} resultados
                </span>
            </div>
        </div>

        <!-- Lista de Livros -->
        <div class="row">
            @foreach($livros as $livro)
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    @include('components.livro-card', ['livro' => $livro])
                </div>
            @endforeach
        </div>

        <!-- Paginação -->
        @if($livros->hasPages())
            <div class="row mt-5">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        {{ $livros->appends(['q' => $termo])->links() }}
                    </div>
                </div>
            </div>
        @endif

    @else
        <!-- Nenhum resultado encontrado -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-search fa-5x text-muted opacity-50"></i>
                    </div>
                    <h3 class="text-muted mb-3">Nenhum resultado encontrado</h3>
                    <p class="text-muted mb-4">
                        Não encontramos livros que correspondam à sua busca por <strong>"{{ $termo }}"</strong>.
                    </p>
                    
                    <!-- Sugestões -->
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h5 class="card-title">Dicas para melhorar sua busca:</h5>
                            <ul class="list-unstyled text-start mb-0">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Verifique a ortografia das palavras</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Use termos mais gerais</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Experimente sinônimos</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Busque por autor, título ou categoria</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('loja.catalogo') }}" class="btn btn-primary me-2">
                            <i class="fas fa-th-large me-2"></i>Explorar Catálogo
                        </a>
                        <a href="{{ route('loja.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Voltar ao Início
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Sugestões de Categorias -->
    @if($livros->count() == 0)
        <hr class="my-5">
        <div class="row">
            <div class="col-12">
                <h4 class="text-center mb-4">Que tal explorar nossas categorias?</h4>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    @php
                        $categorias = \App\Models\Categoria::ativo()
                            ->whereHas('livros', function($query) {
                                $query->where('ativo', true);
                            })
                            ->withCount(['livros' => function($query) {
                                $query->where('ativo', true);
                            }])
                            ->orderBy('livros_count', 'desc')
                            ->limit(8)
                            ->get();
                    @endphp
                    
                    @foreach($categorias as $categoria)
                        <a href="{{ route('loja.categoria', $categoria->slug) }}" 
                           class="btn btn-outline-primary btn-sm">
                            {{ $categoria->nome }}
                            <span class="badge bg-primary ms-1">{{ $categoria->livros_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function ordenarResultados(valor) {
    const url = new URL(window.location);
    
    switch(valor) {
        case 'relevancia':
            url.searchParams.delete('ordem');
            url.searchParams.delete('direcao');
            break;
        case 'titulo-asc':
            url.searchParams.set('ordem', 'titulo');
            url.searchParams.set('direcao', 'asc');
            break;
        case 'titulo-desc':
            url.searchParams.set('ordem', 'titulo');
            url.searchParams.set('direcao', 'desc');
            break;
        case 'preco-asc':
            url.searchParams.set('ordem', 'preco');
            url.searchParams.set('direcao', 'asc');
            break;
        case 'preco-desc':
            url.searchParams.set('ordem', 'preco');
            url.searchParams.set('direcao', 'desc');
            break;
    }
    
    // Remove page parameter when changing order
    url.searchParams.delete('page');
    
    window.location = url.toString();
}
</script>
@endpush
@endsection