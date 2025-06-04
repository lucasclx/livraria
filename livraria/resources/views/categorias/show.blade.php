@extends('layouts.app')
@section('title', 'Detalhes da Categoria')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-tags"></i> Detalhes da Categoria</h4>
                <div>
                    <a href="{{ route('categorias.edit', $categoria) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="{{ route('categorias.delete', $categoria) }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i> Excluir
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <img src="{{ $categoria->imagem_url }}" alt="{{ $categoria->nome }}" class="img-fluid rounded shadow-sm" style="max-height: 300px; width: 100%; object-fit: cover;">
                    </div>
                    <div class="col-md-8">
                        <h3 class="text-primary mb-3">{{ $categoria->nome }}</h3>
                        @if($categoria->descricao)
                            <p>{{ $categoria->descricao }}</p>
                        @endif
                        <p>
                            <span class="badge {{ $categoria->ativo ? 'bg-success' : 'bg-danger' }}">
                                {{ $categoria->ativo ? 'Ativa' : 'Inativa' }}
                            </span>
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
                                <tbody>
                                    <tr>
                                        <td><strong>ID:</strong></td>
                                        <td>{{ $categoria->id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Criada em:</strong></td>
                                        <td>{{ $categoria->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Atualizada em:</strong></td>
                                        <td>{{ $categoria->updated_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light">
                <a href="{{ route('categorias.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
