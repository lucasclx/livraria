@extends('layouts.app')
@section('title', 'Meus Favoritos - Livraria Mil Páginas')

@section('content')
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('perfil.index') }}">Meu Perfil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Meus Favoritos</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="display-6"><i class="fas fa-heart text-danger me-2"></i>Meus Favoritos</h1>
            <p class="lead text-muted">A sua lista pessoal de desejos e leituras preferidas.</p>
        </div>
    </div>

    @if($favoritos->isNotEmpty())
        <div class="row">
            @foreach($favoritos as $livro)
                {{-- A correção de alinhamento é aplicada aqui, chamando o componente diretamente --}}
                @include('components.livro-card', ['livro' => $livro])
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $favoritos->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-heart-broken fa-4x text-muted mb-4"></i>
            <h3>A sua lista de favoritos está vazia.</h3>
            <p class="text-muted">Clique no ícone de coração nos livros para os adicionar aqui.</p>
            <a href="{{ route('loja.catalogo') }}" class="btn btn-primary mt-2">Explorar livros</a>
        </div>
    @endif
</div>
@endsection