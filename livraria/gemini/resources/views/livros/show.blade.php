@extends('layouts.app')
@section('title', 'Detalhes do Livro')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-book"></i> Detalhes do Livro</h4>
                <div>
                    <a href="{{ route('livros.edit', $livro) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form action="{{ route('livros.destroy', $livro) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" 
                                onclick="return confirm('Tem certeza que deseja remover este livro?')">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Imagem do Livro -->
                    <div class="col-md-4">
                        @if($livro->imagem)
                            <img src="{{ $livro->imagem_url }}" alt="{{ $livro->titulo }}" 
                                 class="img-fluid rounded shadow-sm" style="max-height: 400px; width: 100%; object-fit: cover;">
                        @else
                            <div class="text-center p-5 bg-light rounded">
                                <i class="fas fa-book fa-5x text-muted mb-3"></i>
                                <p class="text-muted">Sem imagem disponível</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Informações do Livro -->
                    <div class="col-md-8">
                        <h3 class="text-primary mb-3">{{ $livro->titulo }}</h3>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-user text-muted"></i> Autor:</strong>
                                <p class="mb-2">{{ $livro->autor }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-tags text-muted"></i> Categoria:</strong>
                                <p class="mb-2">
                                    @if($livro->categoria)
                                        <span class="badge bg-secondary">{{ $livro->categoria }}</span>
                                    @else
                                        <span class="text-muted">Não informado</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-building text-muted"></i> Editora:</strong>
                                <p class="mb-2">{{ $livro->editora ?: 'Não informado' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-barcode text-muted"></i> ISBN:</strong>
                                <p class="mb-2">{{ $livro->isbn ?: 'Não informado' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-calendar text-muted"></i> Ano de Publicação:</strong>
                                <p class="mb-2">{{ $livro->ano_publicacao ?: 'Não informado' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-file-alt text-muted"></i> Páginas:</strong>
                                <p class="mb-2">{{ $livro->paginas ? $livro->paginas . ' páginas' : 'Não informado' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><i class="fas fa-dollar-sign text-muted"></i> Preço:</strong>
                                <p class="mb-2">
                                    <span class="h5 text-success">{{ $livro->preco_formatado }}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-boxes text-muted"></i> Estoque:</strong>
                                <p class="mb-2">
                                    <span class="badge {{ $livro->estoque > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $livro->estoque }} {{ $livro->estoque == 1 ? 'unidade' : 'unidades' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        @if($livro->estoque > 0)
                            <div class="alert alert-info">
                                <strong><i class="fas fa-info-circle"></i> Valor em Estoque:</strong>
                                R$ {{ number_format($livro->preco * $livro->estoque, 2, ',', '.') }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sinopse -->
                @if($livro->sinopse)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5><i class="fas fa-align-left text-muted"></i> Sinopse</h5>
                            <div class="p-3 bg-light rounded">
                                <p class="mb-0">{{ $livro->sinopse }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Informações Adicionais -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5><i class="fas fa-info-circle text-muted"></i> Informações Adicionais</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
                                <tbody>
                                    <tr>
                                        <td><strong>ID do Livro:</strong></td>
                                        <td>{{ $livro->id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Data de Cadastro:</strong></td>
                                        <td>{{ $livro->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Última Atualização:</strong></td>
                                        <td>{{ $livro->updated_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    @if($livro->created_at != $livro->updated_at)
                                        <tr>
                                            <td><strong>Última Modificação:</strong></td>
                                            <td>{{ $livro->updated_at->diffForHumans() }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer com Ações -->
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('livros.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar ao Catálogo
                    </a>
                    <div>
                        <a href="{{ route('livros.edit', $livro) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Editar Livro
                        </a>
                        @if($livro->estoque <= 5)
                            <a href="{{ route('livros.edit', $livro) }}" class="btn btn-info">
                                <i class="fas fa-plus"></i> Repor Estoque
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas de Status -->
        @if($livro->estoque == 0)
            <div class="alert alert-danger mt-3">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Atenção!</strong> Este livro está sem estoque.
            </div>
        @elseif($livro->estoque <= 5)
            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Aviso!</strong> Estoque baixo. Considere fazer uma reposição.
            </div>
        @endif
    </div>
</div>

<!-- Script para confirmação de exclusão -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confirmar exclusão com mais detalhes
    const deleteForm = document.querySelector('form[method="POST"]');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (confirm('Tem certeza que deseja excluir o livro "{{ $livro->titulo }}"?\n\nEsta ação não pode ser desfeita.')) {
                this.submit();
            }
        });
    }
});
</script>
@endsection