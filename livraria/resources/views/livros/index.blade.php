@extends('layouts.app')
@section('title', 'Catálogo da Biblioteca')

@section('content')
<!-- Hero Section -->
<div class="row mb-5">
    <div class="col-12">
        <div class="text-center py-5 position-relative">
            <h1 class="page-title floating-book">📚 Nosso Acervo Literário</h1>
            <p class="lead text-muted mt-3">
                Descubra mundos infinitos através das páginas dos nossos livros
            </p>
        </div>
    </div>
</div>

<!-- Actions Bar -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <h2 class="mb-0">
            <i class="fas fa-book-open text-primary me-2"></i>
            Catálogo Completo
        </h2>
        <span class="badge badge-category">
            {{ $livros->total() ?? 0 }} livros encontrados
        </span>
    </div>
    <a href="{{ route('livros.create') }}" class="btn btn-gold">
        <i class="fas fa-plus-circle me-2"></i>
        Adicionar Novo Livro
    </a>
</div>

<!-- Search and Filters -->
<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('livros.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-1"></i> Buscar Livros
                    </label>
                    <input type="text" name="busca" class="form-control" 
                           value="{{ request('busca') }}" 
                           placeholder="Digite o título, autor, ISBN ou editora...">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-tags me-1"></i> Categoria
                    </label>
                    <select name="categoria" class="form-select">
                        <option value="">Todas</option>
                        @if(isset($categorias) && $categorias->count() > 0)
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria }}" 
                                        {{ request('categoria') == $categoria ? 'selected' : '' }}>
                                    {{ $categoria }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-warehouse me-1"></i> Estoque
                    </label>
                    <select name="estoque" class="form-select">
                        <option value="">Todos</option>
                        <option value="disponivel" {{ request('estoque') == 'disponivel' ? 'selected' : '' }}>
                            ✅ Disponível
                        </option>
                        <option value="baixo" {{ request('estoque') == 'baixo' ? 'selected' : '' }}>
                            ⚠️ Estoque Baixo
                        </option>
                        <option value="sem_estoque" {{ request('estoque') == 'sem_estoque' ? 'selected' : '' }}>
                            ❌ Sem Estoque
                        </option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-sort me-1"></i> Ordenar
                    </label>
                    <select name="ordem" class="form-select">
                        <option value="titulo" {{ request('ordem') == 'titulo' ? 'selected' : '' }}>
                            📖 Título
                        </option>
                        <option value="autor" {{ request('ordem') == 'autor' ? 'selected' : '' }}>
                            ✍️ Autor
                        </option>
                        <option value="preco" {{ request('ordem') == 'preco' ? 'selected' : '' }}>
                            💰 Preço
                        </option>
                        <option value="created_at" {{ request('ordem') == 'created_at' ? 'selected' : '' }}>
                            🆕 Mais Recentes
                        </option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-grid w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                    </div>
                </div>
            </div>
            
            @if(request()->hasAny(['busca', 'categoria', 'estoque', 'ordem']))
                <div class="row mt-3">
                    <div class="col-12">
                        <a href="{{ route('livros.index') }}" class="btn btn-outline-elegant btn-sm">
                            <i class="fas fa-times me-1"></i> Limpar Filtros
                        </a>
                        <span class="text-muted ms-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Filtros aplicados - {{ $livros->total() ?? 0 }} resultado(s)
                        </span>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>

@if(isset($livros) && $livros->count() > 0)
    <!-- Books Grid -->
    <div class="row">
        @foreach($livros as $livro)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card book-card h-100">
                <!-- Book Cover -->
                <div class="position-relative book-cover">
                    @if($livro->imagem)
                        <img src="{{ $livro->imagem_url }}" class="card-img-top" alt="{{ $livro->titulo }}" 
                             style="height: 250px; object-fit: cover;">
                    @else
                        <div class="card-img-top d-flex align-items-center justify-content-center" 
                             style="height: 250px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <div class="text-center">
                                <i class="fas fa-book fa-4x text-muted mb-2"></i>
                                <p class="text-muted small mb-0">Sem Capa</p>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Stock Status Badge -->
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
                    
                    <!-- Favorite Icon -->
                    <div class="position-absolute top-0 start-0 m-2">
                        @php
                            $isFav = auth()->check() && auth()->user()->favorites()->where('livro_id', $livro->id)->exists();
                        @endphp
                        <button class="btn btn-sm btn-light rounded-circle" data-favorite-button="{{ $livro->id }}" onclick="toggleFavorite({{ $livro->id }})"
                                data-bs-toggle="tooltip" title="Adicionar aos favoritos">
                            <i class="{{ $isFav ? 'fas' : 'far' }} fa-heart" @if($isFav) style="color:#dc3545" @endif></i>
                        </button>
                    </div>
                </div>
                
                <!-- Book Info -->
                <div class="card-body d-flex flex-column">
                    <div class="mb-auto">
                        <h6 class="card-title fw-bold mb-2" style="min-height: 3rem; line-height: 1.5;">
                            {{ Str::limit($livro->titulo, 50) }}
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
                                    {{ $livro->categoria }}
                                </span>
                            </div>
                        @endif
                        
                        @if($livro->sinopse)
                            <p class="card-text small text-muted mb-3">
                                {{ Str::limit($livro->sinopse, 100) }}
                            </p>
                        @endif
                    </div>
                    
                    <!-- Price -->
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="price fw-bold text-success">{{ $livro->preco_formatado }}</span>
                            <small class="text-muted">
                                <i class="fas fa-boxes"></i> {{ $livro->estoque }}
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Card Footer -->
                <div class="card-footer bg-transparent border-0 pt-0">
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
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $livros->links() }}
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-search fa-5x text-muted mb-3"></i>
        <h4>Nenhum livro encontrado</h4>
        @if(request()->hasAny(['busca', 'categoria', 'estoque']))
            <p class="text-muted">Tente ajustar os filtros de busca.</p>
            <a href="{{ route('livros.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list"></i> Ver Todos os Livros
            </a>
        @else
            <p class="text-muted">Comece adicionando o primeiro livro ao seu catálogo.</p>
            <a href="{{ route('livros.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Cadastrar Primeiro Livro
            </a>
        @endif
    </div>
@endif

<!-- Statistics Summary -->
@if(isset($livros) && $livros->total() > 0)
<div class="card mt-4 stats-card">
    <div class="card-header">
        <h5><i class="fas fa-chart-bar"></i> Resumo do Catálogo</h5>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-3">
                <div class="border-end">
                    <div class="stat-number">{{ $livros->total() }}</div>
                    <small class="text-muted">Total de Livros</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border-end">
                    <div class="stat-number">{{ \App\Models\Livro::sum('estoque') }}</div>
                    <small class="text-muted">Livros em Estoque</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border-end">
                    <div class="stat-number">{{ \App\Models\Livro::estoqueBaixo()->count() }}</div>
                    <small class="text-muted">Estoque Baixo</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-number">
                    R$ {{ number_format(\App\Models\Livro::sum(\DB::raw('preco * estoque')), 2, ',', '.') }}
                </div>
                <small class="text-muted">Valor Total</small>
            </div>
        </div>
    </div>
</div>
@endif

<!-- JavaScript for favorites -->
<script>
function toggleFavorite(livroId) {
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
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            icon.style.color = '';
        }
    });
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
