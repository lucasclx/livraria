@extends('layouts.app')
@section('title', 'Minhas Avaliações - Livraria Mil Páginas')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('perfil.index') }}">Meu Perfil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Minhas Avaliações</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="page-title mb-0"><i class="fas fa-star me-2"></i>Minhas Avaliações</h2>
        @if($avaliacoes->total() > 0)
            <span class="badge bg-primary rounded-pill fs-6">{{ $avaliacoes->total() }} {{ Str::plural('avaliação', $avaliacoes->total()) }}</span>
        @endif
    </div>

    @forelse($avaliacoes as $avaliacao)
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 text-center">
                        <a href="{{ route('loja.detalhes', $avaliacao->livro) }}">
                            <img src="{{ $avaliacao->livro->imagem_url ?? asset('images/placeholder_livro.png') }}" class="img-fluid rounded" alt="{{ $avaliacao->livro->titulo }}" style="max-height: 120px;">
                        </a>
                    </div>
                    <div class="col-md-10">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title mb-1">{{ $avaliacao->livro->titulo }}</h5>
                            <small class="text-muted">{{ $avaliacao->created_at->format('d/m/Y') }}</small>
                        </div>
                        <div class="text-warning mb-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star{{ $i <= $avaliacao->rating ? '' : '-o' }}"></i>
                            @endfor
                        </div>
                        <p class="card-text">{{ $avaliacao->comment }}</p>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <i class="fas fa-comment-slash fa-4x text-muted mb-4"></i>
            <h3>Você ainda não fez nenhuma avaliação.</h3>
            <p class="text-muted">A sua opinião é importante! Avalie os livros que já leu.</p>
        </div>
    @endforelse

    <div class="d-flex justify-content-center mt-4">
        {{ $avaliacoes->links() }}
    </div>
</div>
@endsection