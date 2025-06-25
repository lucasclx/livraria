@extends('layouts.app')

@section('title', 'Editar Livro: ' . $livro->titulo)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Editar Livro: {{ $livro->titulo }}</h5>
                    <a href="{{ route('livros.index') }}" class="btn btn-light btn-sm">Voltar para Livros</a>
                </div>

                <div class="card-body">
                    {{-- 
                        !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                        A CORREÇÃO CRÍTICA ESTÁ AQUI: enctype="multipart/form-data"
                        Sem isto, o formulário não envia ficheiros.
                        !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                    --}}
                    <form action="{{ route('livros.update', $livro->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- O resto do seu formulário já estava perfeito --}}
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                                <input type="text" name="titulo" id="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo', $livro->titulo) }}" required>
                                @error('titulo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="isbn" class="form-label">ISBN</label>
                                <input type="text" name="isbn" id="isbn" class="form-control @error('isbn') is-invalid @enderror" value="{{ old('isbn', $livro->isbn) }}">
                                @error('isbn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="autor" class="form-label">Autor <span class="text-danger">*</span></label>
                                <input type="text" name="autor" id="autor" class="form-control @error('autor') is-invalid @enderror" value="{{ old('autor', $livro->autor) }}" required>
                                @error('autor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Editora</label>
                                <input type="text" name="editora" class="form-control @error('editora') is-invalid @enderror"
                                        value="{{ old('editora', $livro->editora) }}" list="editoras-list">
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
                                <label for="ano_publicacao" class="form-label">Ano de Publicação</label>
                                <input type="number" name="ano_publicacao" id="ano_publicacao" class="form-control @error('ano_publicacao') is-invalid @enderror" value="{{ old('ano_publicacao', $livro->ano_publicacao) }}" min="1000" max="{{ date('Y') }}">
                                @error('ano_publicacao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="preco" class="form-label">Preço (R$) <span class="text-danger">*</span></label>
                                <input type="number" name="preco" id="preco" class="form-control @error('preco') is-invalid @enderror" value="{{ old('preco', $livro->preco) }}" step="0.01" min="0.01" required>
                                @error('preco')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="preco_promocional" class="form-label">Preço Promocional (R$)</label>
                                <input type="number" name="preco_promocional" id="preco_promocional" class="form-control @error('preco_promocional') is-invalid @enderror" value="{{ old('preco_promocional', $livro->preco_promocional) }}" step="0.01" min="0.01">
                                @error('preco_promocional')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="paginas" class="form-label">Páginas</label>
                                <input type="number" name="paginas" id="paginas" class="form-control @error('paginas') is-invalid @enderror" value="{{ old('paginas', $livro->paginas) }}" min="1">
                                @error('paginas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="idioma" class="form-label">Idioma</label>
                                <input type="text" name="idioma" id="idioma" class="form-control @error('idioma') is-invalid @enderror" value="{{ old('idioma', $livro->idioma) }}">
                                @error('idioma')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edicao" class="form-label">Edição</label>
                                <input type="text" name="edicao" id="edicao" class="form-control @error('edicao') is-invalid @enderror" value="{{ old('edicao', $livro->edicao) }}">
                                @error('edicao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="encadernacao" class="form-label">Encadernação</label>
                                <input type="text" name="encadernacao" id="encadernacao" class="form-control @error('encadernacao') is-invalid @enderror" value="{{ old('encadernacao', $livro->encadernacao) }}">
                                @error('encadernacao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="peso" class="form-label">Peso (kg)</label>
                                <input type="number" name="peso" id="peso" class="form-control @error('peso') is-invalid @enderror" value="{{ old('peso', $livro->peso) }}" step="0.01" min="0.01">
                                @error('peso')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="categoria_id" class="form-label">Categoria <span class="text-danger">*</span></label>
                                <select name="categoria_id" id="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror" required>
                                    <option value="">Selecione uma categoria</option>
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" {{ old('categoria_id', $livro->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                            {{ $categoria->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categoria_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="estoque" class="form-label">Estoque <span class="text-danger">*</span></label>
                                <input type="number" name="estoque" id="estoque" class="form-control @error('estoque') is-invalid @enderror" value="{{ old('estoque', $livro->estoque) }}" min="0" required>
                                @error('estoque')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="estoque_minimo" class="form-label">Estoque Mínimo</label>
                                <input type="number" name="estoque_minimo" id="estoque_minimo" class="form-control @error('estoque_minimo') is-invalid @enderror" value="{{ old('estoque_minimo', $livro->estoque_minimo) }}" min="0">
                                @error('estoque_minimo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sinopse" class="form-label">Sinopse</label>
                            <textarea name="sinopse" id="sinopse" class="form-control @error('sinopse') is-invalid @enderror" rows="5">{{ old('sinopse', $livro->sinopse) }}</textarea>
                            @error('sinopse')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="imagem" class="form-label">Imagem da Capa</label>
                            @if($livro->imagem)
                                <div class="mb-2">
                                    <img src="{{ $livro->imagem_url }}" alt="Capa Atual" class="img-thumbnail" style="max-width: 150px;">
                                    <small class="text-muted d-block mt-1">Imagem atual</small>
                                </div>
                            @endif
                            <input type="file" name="imagem" id="imagem" class="form-control @error('imagem') is-invalid @enderror">
                            @error('imagem')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 form-check form-switch">
                                <input type="hidden" name="ativo" value="0">
                                <input type="checkbox" class="form-check-input" id="ativo" name="ativo" value="1" {{ old('ativo', $livro->ativo) ? 'checked' : '' }}>
                                <label class="form-check-label" for="ativo">Ativo</label>
                            </div>
                            <div class="col-md-6 form-check form-switch">
                                <input type="hidden" name="destaque" value="0">
                                <input type="checkbox" class="form-check-input" id="destaque" name="destaque" value="1" {{ old('destaque', $livro->destaque) ? 'checked' : '' }}>
                                <label class="form-check-label" for="destaque">Destaque</label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">Atualizar Livro</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection