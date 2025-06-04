@extends('layouts.app')
@section('title', 'Editar Categoria')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-edit"></i> Editar Categoria</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('categorias.update', $categoria) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $categoria->nome) }}" required>
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control @error('descricao') is-invalid @enderror" rows="3">{{ old('descricao', $categoria->descricao) }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Imagem</label>
                        @if($categoria->imagem)
                            <div class="mb-2">
                                <img src="{{ $categoria->imagem_url }}" alt="{{ $categoria->nome }}" class="img-thumbnail" style="max-width: 200px;">
                                <p class="text-muted small">Imagem atual</p>
                            </div>
                        @endif
                        <input type="file" name="imagem" class="form-control @error('imagem') is-invalid @enderror" accept="image/*">
                        @error('imagem')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="ativo" id="ativo" class="form-check-input" {{ old('ativo', $categoria->ativo) ? 'checked' : '' }}>
                        <label for="ativo" class="form-check-label">Ativa</label>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('categorias.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Atualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
