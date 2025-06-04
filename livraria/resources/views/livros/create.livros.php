@extends('layouts.app')
@section('title', 'Cadastrar Novo Livro')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-plus"></i> Cadastrar Novo Livro</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('livros.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Título *</label>
                            <input type="text" name="titulo" class="form-control" value="{{ old('titulo') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Categoria</label>
                            <input type="text" name="categoria" class="form-control" value="{{ old('categoria') }}" 
                                   placeholder="Ex: Ficção, Romance, Técnico">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Autor *</label>
                            <input type="text" name="autor" class="form-control" value="{{ old('autor') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Editora</label>
                            <input type="text" name="editora" class="form-control" value="{{ old('editora') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">ISBN</label>
                            <input type="text" name="isbn" class="form-control" value="{{ old('isbn') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ano de Publicação</label>
                            <input type="number" name="ano_publicacao" class="form-control" 
                                   value="{{ old('ano_publicacao') }}" min="1900" max="{{ date('Y') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Páginas</label>
                            <input type="number" name="paginas" class="form-control" value="{{ old('paginas') }}" min="1">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Preço *</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" step="0.01" name="preco" class="form-control" 
                                       value="{{ old('preco') }}" required min="0">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estoque *</label>
                            <input type="number" name="estoque" class="form-control" 
                                   value="{{ old('estoque', 0) }}" required min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sinopse</label>
                        <textarea name="sinopse" class="form-control" rows="4" 
                                  placeholder="Breve descrição do livro...">{{ old('sinopse') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Imagem do Livro</label>
                        <input type="file" name="imagem" class="form-control" accept="image/*">
                        <small class="form-text text-muted">
                            Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB
                        </small>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('livros.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Cadastrar Livro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection