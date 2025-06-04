@extends('layouts.app')
@section('title', 'Excluir Categoria')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Atenção!</h5>
                    Você está prestes a excluir a categoria <strong>"{{ $categoria->nome }}"</strong>.
                    Esta ação não pode ser desfeita.
                </div>
                <div class="mb-3 text-center">
                    <img src="{{ $categoria->imagem_url }}" alt="{{ $categoria->nome }}" class="img-fluid img-thumbnail" style="max-width: 200px;">
                </div>
                @if($categoria->livros()->count() > 0)
                    <div class="alert alert-info">
                        <strong>Informação:</strong><br>
                        Esta categoria possui {{ $categoria->livros()->count() }} livro(s) vinculado(s).
                    </div>
                @endif
            </div>
            <div class="card-footer">
                <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta categoria?')">
                        <i class="fas fa-trash"></i> Sim, excluir categoria
                    </button>
                </form>
                <a href="{{ route('categorias.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
