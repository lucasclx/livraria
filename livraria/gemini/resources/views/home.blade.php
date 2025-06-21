@extends('layouts.app')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Dashboard</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </div>
    </div>
@stop

@section('main_content')
<div class="row">
    <!-- Cards de Estatísticas -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ App\Models\Livro::count() }}</h3>
                <p>Total de Livros</p>
            </div>
            <div class="icon">
                <i class="fas fa-book"></i>
            </div>
            <a href="{{ route('livros.index') }}" class="small-box-footer">
                Ver todos <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ App\Models\Categoria::count() }}</h3>
                <p>Categorias</p>
            </div>
            <div class="icon">
                <i class="fas fa-tags"></i>
            </div>
            <a href="{{ route('categorias.index') }}" class="small-box-footer">
                Ver todas <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ App\Models\Livro::sum('estoque') }}</h3>
                <p>Livros em Estoque</p>
            </div>
            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
            <a href="{{ route('livros.index') }}" class="small-box-footer">
                Gerenciar <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>R$ {{ number_format(App\Models\Livro::sum(\DB::raw('preco * estoque')), 2, ',', '.') }}</h3>
                <p>Valor Total Estoque</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <a href="{{ route('livros.index') }}" class="small-box-footer">
                Relatório <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Livros com Estoque Baixo -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Livros com Estoque Baixo</h3>
            </div>
            <div class="card-body">
                @php
                    $livrosEstoqueBaixo = App\Models\Livro::where('estoque', '<=', 5)->where('ativo', true)->limit(5)->get();
                @endphp
                
                @if($livrosEstoqueBaixo->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Livro</th>
                                    <th>Estoque</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($livrosEstoqueBaixo as $livro)
                                <tr>
                                    <td>{{ Str::limit($livro->titulo, 30) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $livro->estoque > 0 ? 'warning' : 'danger' }}">
                                            {{ $livro->estoque }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('livros.edit', $livro) }}" class="btn btn-xs btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Nenhum livro com estoque baixo.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Últimos Livros Cadastrados -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Últimos Livros Cadastrados</h3>
            </div>
            <div class="card-body">
                @php
                    $ultimosLivros = App\Models\Livro::latest()->limit(5)->get();
                @endphp
                
                @if($ultimosLivros->count() > 0)
                    @foreach($ultimosLivros as $livro)
                        <div class="media mb-2">
                            <img src="{{ $livro->imagem_url }}" alt="{{ $livro->titulo }}" 
                                 class="mr-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <div class="media-body">
                                <h6 class="mt-0">{{ Str::limit($livro->titulo, 25) }}</h6>
                                <small class="text-muted">{{ $livro->autor }} - {{ $livro->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">Nenhum livro cadastrado ainda.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection