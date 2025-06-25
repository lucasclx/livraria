@extends('layouts.app')

@section('title', 'Meus Favoritos - Livraria Mil Páginas')

@section('content')
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('loja.index') }}">Início</a></li>
            <li class="breadcrumb-item active" aria-current="page">Meus Favoritos</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-12">
            <div class="favoritos-header text-center py-4 bg-gradient-danger text-white rounded">
                <h1 class="display-6 mb-3">
                    <i class="fas fa-heart me-2"></i>
                    Meus Livros Favoritos
                </h1>
                <p class="lead mb-0">
                    @if($favoritos->total() > 0)
                        Você tem {{ $favoritos->total() }} {{ Str::plural('livro favoritado', $favoritos->total()) }}
                    @else
                        Você ainda não favoritou nenhum livro
                    @endif
                </p>
            </div>
        </div>
    </div>

    @if($favoritos->isNotEmpty())
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <span class="text-muted me-3">Ordenar por:</span>
                    <select class="form-select form-select-sm" style="width: auto;" onchange="ordenarFavoritos(this.value)">
                        <option value="recente" @selected(request('ordem', 'recente') == 'recente')>Mais recentes</option>
                        <option value="titulo-asc" @selected(request('ordem') == 'titulo' && request('direcao') == 'asc')>Nome (A-Z)</option>
                        <option value="titulo-desc" @selected(request('ordem') == 'titulo' && request('direcao') == 'desc')>Nome (Z-A)</option>
                        <option value="preco-asc" @selected(request('ordem') == 'preco' && request('direcao') == 'asc')>Menor preço</option>
                        <option value="preco-desc" @selected(request('ordem') == 'preco' && request('direcao') == 'desc')>Maior preço</option>
                        <option value="categoria" @selected(request('ordem') == 'categoria')>Por categoria</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6 text-md-end mt-2 mt-md-0">
                <span class="text-muted">
                    Exibindo {{ $favoritos->firstItem() }}-{{ $favoritos->lastItem() }} de {{ $favoritos->total() }} favoritos
                </span>
            </div>
        </div>

        <div class="row">
            @foreach($favoritos as $livro)
                {{-- CORREÇÃO DE ALINHAMENTO APLICADA AQUI --}}
                @include('components.livro-card', ['livro' => $livro])
            @endforeach
        </div>

        @if($favoritos->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $favoritos->appends(request()->query())->links() }}
            </div>
        @endif
        
    @else
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-heart fa-5x text-muted opacity-50"></i>
            </div>
            <h3 class="text-muted mb-3">Sua lista de favoritos está vazia</h3>
            <p class="text-muted mb-4">
                Clique no ícone <i class="far fa-heart text-danger"></i> nos livros para adicioná-los aqui.
            </p>
            <a href="{{ route('loja.catalogo') }}" class="btn btn-primary">
                <i class="fas fa-search me-2"></i>Explorar Catálogo
            </a>
        </div>
    @endif

    @if($favoritos->isNotEmpty())
        @php
            // Lógica para buscar recomendações
            $categoriasIds = $favoritos->pluck('categoria_id')->filter()->unique();
            $recomendacoes = \App\Models\Livro::ativo()->emEstoque()
                ->whereIn('categoria_id', $categoriasIds)
                ->whereNotIn('id', $favoritos->pluck('id'))
                ->with('categoria')->inRandomOrder()->limit(4)->get();
        @endphp

        @if($recomendacoes->isNotEmpty())
            <hr class="my-5">
            <h3 class="text-center mb-4">
                <i class="fas fa-magic text-primary me-2"></i>
                Recomendações para Você
            </h3>
            <div class="row">
                @foreach($recomendacoes as $livro)
                    {{-- CORREÇÃO DE ALINHAMENTO APLICADA AQUI TAMBÉM --}}
                    @include('components.livro-card', ['livro' => $livro])
                @endforeach
            </div>
        @endif
    @endif
</div>

@push('scripts')
<script>
function ordenarFavoritos(valor) {
    const url = new URL(window.location);
    const params = new URLSearchParams(url.search);

    // Limpa parâmetros antigos de ordenação
    params.delete('ordem');
    params.delete('direcao');

    // Adiciona os novos parâmetros
    switch(valor) {
        case 'recente':
            // Não precisa adicionar nada, é o padrão
            break;
        case 'titulo-asc':
            params.set('ordem', 'titulo');
            params.set('direcao', 'asc');
            break;
        case 'titulo-desc':
            params.set('ordem', 'titulo');
            params.set('direcao', 'desc');
            break;
        case 'preco-asc':
            params.set('ordem', 'preco');
            params.set('direcao', 'asc');
            break;
        case 'preco-desc':
            params.set('ordem', 'preco');
            params.set('direcao', 'desc');
            break;
        case 'categoria':
            params.set('ordem', 'categoria');
            break;
    }
    
    // Reseta a paginação ao reordenar
    params.delete('page');
    url.search = params.toString();
    
    // Redireciona para a nova URL
    window.location.href = url.toString();
}
</script>
@endpush

@push('styles')
<style>
.bg-gradient-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}
</style>
@endpush
@endsection