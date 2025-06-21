@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Criar Nova Categoria</h5>
                    <a href="{{ route('categorias.index') }}" class="btn btn-light btn-sm">Voltar para Categorias</a>
                </div>

                <div class="card-body">
                    <form action="{{ route('categorias.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome da Categoria <span class="text-danger">*</span></label>
                            <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome') }}" required>
                            @error('nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea name="descricao" id="descricao" class="form-control @error('descricao') is-invalid @enderror" rows="3">{{ old('descricao') }}</textarea>
                            @error('descricao')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check form-switch">
                            <!-- Input hidden para garantir que 'ativo' seja enviado como 0 se o checkbox for desmarcado -->
                            <input type="hidden" name="ativo" value="0">
                            <input type="checkbox" class="form-check-input" id="ativo" name="ativo" value="1" {{ old('ativo', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="ativo">Ativa</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">Cadastrar Categoria</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection