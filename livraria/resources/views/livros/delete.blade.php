@extends('layouts.app')

@section('title', 'Excluir Livro')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Excluir Livro</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('livros.index') }}">Livros</a></li>
                <li class="breadcrumb-item active">Excluir</li>
            </ol>
        </div>
    </div>
@stop

@section('main_content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirmação de Exclusão
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Atenção!</h5>
                    Você está prestes a excluir o livro <strong>"{{ $livro->titulo }}"</strong>.
                    Esta ação não pode ser desfeita.
                </div>

                <div class="row">
                    <div class="col-md-4">
                        @if($livro->imagem)
                            <img src="{{ $livro->imagem_url }}" alt="{{ $livro->titulo }}" 
                                 class="img-fluid img-thumbnail">
                        @else
                            <div class="text-center p-4 border rounded">
                                <i class="fas fa-book fa-3x text-muted"></i>
                                <p class="text-muted mt-2">Sem imagem</p>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h5>Detalhes do Livro:</h5>
                        <ul class="list-unstyled">
                            <li><strong>ID:</strong> {{ $livro->id }}</li>
                            <li><strong>Título:</strong> {{ $livro->titulo }}</li>
                            <li><strong>Autor:</strong> {{ $livro->autor }}</li>
                            <li><strong>ISBN:</strong> {{ $livro->isbn ?: 'Não informado' }}</li>
                            <li><strong>Preço:</strong> {{ $livro->preco_formatado }}</li>
                            <li><strong>Estoque:</strong> {{ $livro->estoque }} unidades</li>
                            <li><strong>Categoria:</strong> 
                                @if($livro->categoria)
                                    {{ $livro->categoria->nome }}
                                @else
                                    Sem categoria
                                @endif
                            </li>
                            <li><strong>Status:</strong> 
                                <span class="badge badge-{{ $livro->ativo ? 'success' : 'danger' }}">
                                    {{ $livro->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </li>
                        </ul>

                        @if($livro->estoque > 0)
                            <div class="alert alert-info">
                                <strong>Informação:</strong><br>
                                Este livro possui {{ $livro->estoque }} unidades em estoque, 
                                totalizando R$ {{ number_format($livro->preco * $livro->estoque, 2, ',', '.') }} 
                                em valor de inventário.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <form action="{{ route('livros.destroy', $livro) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" 
                            onclick="return confirm('Tem certeza que deseja excluir este livro?')">
                        <i class="fas fa-trash"></i> Sim, excluir livro
                    </button>
                </form>
                <a href="{{ route('livros.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </div>
    </div>
</div>
@endsection