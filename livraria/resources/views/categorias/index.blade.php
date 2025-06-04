@extends('layouts.app')
@section('title', 'Categorias')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h1 class="page-title">Categorias de Livros</h1>
    </div>
    <div class="col-auto">
        <a href="{{ route('categorias.create') }}" class="btn btn-gold">
            <i class="fas fa-plus-circle me-1"></i> Nova Categoria
        </a>
    </div>
</div>

@if($categorias->count() > 0)
    <div class="row">
        @foreach($categorias as $categoria)
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="position-relative">
                        <img src="{{ $categoria->imagem_url }}" class="card-img-top" alt="{{ $categoria->nome }}" style="height: 200px; object-fit: cover;">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $categoria->nome }}</h5>
                        @if($categoria->descricao)
                            <p class="card-text small text-muted">{{ Str::limit($categoria->descricao, 100) }}</p>
                        @endif
                        <span class="badge {{ $categoria->ativo ? 'bg-success' : 'bg-danger' }}">
                            {{ $categoria->ativo ? 'Ativa' : 'Inativa' }}
                        </span>
                        <div class="mt-auto d-flex justify-content-between gap-1">
                            <a href="{{ route('categorias.show', $categoria) }}" class="btn btn-outline-info btn-sm flex-fill" data-bs-toggle="tooltip" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('categorias.edit', $categoria) }}" class="btn btn-outline-warning btn-sm flex-fill" data-bs-toggle="tooltip" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('categorias.delete', $categoria) }}" class="btn btn-outline-danger btn-sm flex-fill" data-bs-toggle="tooltip" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="d-flex justify-content-center">
        {{ $categorias->links() }}
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-tags fa-5x text-muted mb-3"></i>
        <h4>Nenhuma categoria cadastrada</h4>
        <a href="{{ route('categorias.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Adicionar Categoria
        </a>
    </div>
@endif
@endsection
