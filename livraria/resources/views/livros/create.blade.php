{{-- resources/views/livros/create.blade.php --}}
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
                            <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror" 
                                   value="{{ old('titulo') }}" required>
                            @error('titulo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Categoria</label>
                            <select name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror">
                                <option value="">Selecionar categoria</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                        {{ $categoria->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('categoria_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <a href="{{ route('categorias.create') }}" target="_blank">Criar nova categoria</a>
                            </small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Autor *</label>
                            <input type="text" name="autor" class="form-control @error('autor') is-invalid @enderror" 
                                   value="{{ old('autor') }}" required>
                            @error('autor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Editora</label>
                            <input type="text" name="editora" class="form-control @error('editora') is-invalid @enderror" 
                                   value="{{ old('editora') }}" list="editoras-list">
                            <datalist id="editoras-list">
                                @foreach($editoras as $editora)
                                    <option value="{{ $editora }}">
                                @endforeach
                            </datalist>
                            @error('editora')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">ISBN</label>
                            <input type="text" name="isbn" class="form-control @error('isbn') is-invalid @enderror" 
                                   value="{{ old('isbn') }}" placeholder="978-XX-XXXX-XXX-X">
                            @error('isbn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ano de Publicação</label>
                            <input type="number" name="ano_publicacao" class="form-control @error('ano_publicacao') is-invalid @enderror" 
                                   value="{{ old('ano_publicacao') }}" min="1900" max="{{ date('Y') + 1 }}">
                            @error('ano_publicacao')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Páginas</label>
                            <input type="number" name="paginas" class="form-control @error('paginas') is-invalid @enderror" 
                                   value="{{ old('paginas') }}" min="1">
                            @error('paginas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Preço *</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" step="0.01" name="preco" class="form-control @error('preco') is-invalid @enderror" 
                                       value="{{ old('preco') }}" required min="0">
                                @error('preco')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Preço Promocional</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" step="0.01" name="preco_promocional" class="form-control @error('preco_promocional') is-invalid @enderror" 
                                       value="{{ old('preco_promocional') }}" min="0">
                                @error('preco_promocional')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estoque *</label>
                            <input type="number" name="estoque" class="form-control @error('estoque') is-invalid @enderror" 
                                   value="{{ old('estoque', 0) }}" required min="0">
                            @error('estoque')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estoque Mínimo</label>
                            <input type="number" name="estoque_minimo" class="form-control @error('estoque_minimo') is-invalid @enderror" 
                                   value="{{ old('estoque_minimo', 5) }}" min="0">
                            @error('estoque_minimo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Peso (kg)</label>
                            <input type="number" step="0.001" name="peso" class="form-control @error('peso') is-invalid @enderror" 
                                   value="{{ old('peso', 0.5) }}" min="0">
                            @error('peso')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sinopse</label>
                        <textarea name="sinopse" class="form-control @error('sinopse') is-invalid @enderror" rows="4" 
                                  placeholder="Breve descrição do livro...">{{ old('sinopse') }}</textarea>
                        @error('sinopse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Imagem do Livro</label>
                        <input type="file" name="imagem" class="form-control @error('imagem') is-invalid @enderror" 
                               accept="image/*" onchange="previewImage(this, 'preview-image')">
                        @error('imagem')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Formatos aceitos: JPG, PNG, GIF, WEBP. Tamanho máximo: 5MB
                        </small>
                        
                        <div class="mt-2">
                            <img id="preview-image" src="#" alt="Preview" 
                                 style="display: none; max-width: 200px; max-height: 200px; border-radius: 5px; border: 1px solid #ddd;">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="ativo" id="ativo" class="form-check-input" {{ old('ativo', true) ? 'checked' : '' }}>
                                <label for="ativo" class="form-check-label">Livro Ativo</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="destaque" id="destaque" class="form-check-input" {{ old('destaque') ? 'checked' : '' }}>
                                <label for="destaque" class="form-check-label">Livro em Destaque</label>
                            </div>
                        </div>
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

<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
            document.getElementById(previewId).style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection