@extends('layouts.app')
@section('title', 'Biblioteca Literária - Sua Livraria Online')

@section('content')
<style>
    .hero-section {
        background: linear-gradient(135deg, var(--primary-brown) 0%, var(--dark-brown) 100%);
        color: white;
        padding: 4rem 0;
        border-radius: 20px;
        margin-bottom: 3rem;
        position: relative;
        overflow: hidden;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y=".9em" font-size="90" opacity="0.1">📚</text></svg>') repeat;
        background-size: 100px 100px;
    }
    
    .stats-card {
        transition: transform 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .livro-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .livro-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    }
    
    .livro-image {
        height: 250px;
        object-fit: cover;
        width: 100%;
    }
    
    .categoria-card {
        background: linear-gradient(135deg, var(--cream) 0%, var(--light-brown) 100%);
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        border: none;
        color: var(--dark-brown);
    }
    
    .categoria-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(139, 69, 19, 0.3);
        color: var(--dark-brown);
        text-decoration: none;
    }
    
    .section-title {
        font-family: 'Playfair Display', serif;
        color: var(--dark-brown);
        margin-bottom: 2rem;
        position: relative;
        display: inline-block;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 60%;
        height: 3px;
        background: linear-gradient(135deg, var(--gold) 0%, var(--dark-gold) 100%);
        border-radius: 2px;
    }
</style>

<!-- Hero Section -->
<div class="hero-section position-relative">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">
                    📚 Bem-vindo à Biblioteca Literária
                </h1>
                <p class="lead mb-4">
                    Descubra mundos infinitos através das páginas dos nossos livros. 
                    Mais de {{ $estatisticas['total_livros'] }} títulos esperando por você!
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('loja.catalogo') }}" class="btn btn-gold btn-lg">
                        <i class="fas fa-book-open me-2"></i>
                        Explorar Catálogo
                    </a>
                    <a href="#ofertas" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-star me-2"></i>
                        Ver Ofertas
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <div class="floating-book">
                    <i class="fas fa-book-reader fa-8x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estatísticas -->
<div class="row mb-5">
    <div class="col-12">
        <h2 class="section-title text-center w-100">Nossa Biblioteca em Números</h2>
    </div>
    @foreach([
        ['icon' => 'fa-book', 'number' => $estatisticas['total_livros'], 'label' => 'Livros Disponíveis', 'color' => 'primary'],
        ['icon' => 'fa-tags', 'number' => $estatisticas['total_categorias'], 'label' => 'Categorias', 'color' => 'success'],
        ['icon' => 'fa-users', 'number' => $estatisticas['total_autores'], 'label' => 'Autores', 'color' => 'warning'],
        ['icon' => 'fa-warehouse', 'number' => $estatisticas['livros_estoque'], 'label' => 'Livros em Estoque', 'color' => 'info']
    ] as $stat)
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card h-100 border-0 shadow">
            <div class="card-body text-center p-4">
                <div class="text-{{ $stat['color'] }} mb-3">
                    <i class="fas {{ $stat['icon'] }} fa-3x"></i>
                </div>
                <h3 class="stat-number">{{ $stat['number'] }}</h3>
                <p class="text-muted mb-0">{{ $stat['label'] }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Livros em Destaque -->
@if($livrosDestaque->count() > 0)
<section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title">📖 Livros em Destaque</h2>
        <a href="{{ route('loja.catalogo') }}" class="btn btn-outline-primary">
            Ver Todos <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="row">
        @foreach($livrosDestaque as $livro)
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card livro-card h-100">
                <div class="position-relative">
                    @if($livro->imagem)
                        <img src="{{ $livro->imagem_url }}" class="livro-image" alt="{{ $livro->titulo }}">
                    @else
                        <div class="livro-image bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-book fa-3x text-muted"></i>
                        </div>
                    @endif
                    
                    <!-- Badge de novidade -->
                    <div class="position-absolute top-0 start-0 m-2">
                        <span class="badge bg-success">NOVO</span>
                    </div>
                    
                    <!-- Botão de favorito -->
                    @auth
                    <div class="position-absolute top-0 end-0 m-2">
                        <button class="btn btn-sm btn-light rounded-circle favorite-btn" 
                                data-livro-id="{{ $livro->id }}"
                                onclick="toggleFavorite({{ $livro->id }})">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    @endauth
                </div>
                
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title fw-bold mb-2">
                        {{ Str::limit($livro->titulo, 40) }}
                    </h6>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-user-edit me-1"></i>{{ $livro->autor }}
                    </p>
                    @if($livro->categoria)
                    <span class="badge bg-secondary mb-3">{{ $livro->categoria }}</span>
                    @endif
                    
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="h5 text-success mb-0">{{ $livro->preco_formatado }}</span>
                            <small class="text-muted">
                                <i class="fas fa-boxes"></i> {{ $livro->estoque }}
                            </small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            @if($livro->estoque > 0)
                                <form method="POST" action="{{ route('cart.add', $livro) }}">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-cart-plus me-1"></i>
                                        Adicionar
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-secondary btn-sm w-100" disabled>
                                    <i class="fas fa-ban me-1"></i>
                                    Esgotado
                                </button>
                            @endif
                            <a href="{{ route('loja.detalhes', $livro) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-eye me-1"></i>
                                Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

<!-- Categorias Populares -->
@if($livrosPorCategoria->count() > 0)
<section class="mb-5">
    <h2 class="section-title text-center w-100">🏷️ Explore por Categoria</h2>
    <div class="row">
        @foreach($livrosPorCategoria as $categoria)
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('loja.categoria', $categoria->categoria) }}" class="categoria-card d-block text-decoration-none">
                <div class="mb-3">
                    <i class="fas fa-bookmark fa-3x"></i>
                </div>
                <h5 class="fw-bold">{{ $categoria->categoria }}</h5>
                <p class="mb-0">{{ $categoria->total }} {{ $categoria->total == 1 ? 'livro' : 'livros' }}</p>
            </a>
        </div>
        @endforeach
    </div>
</section>
@endif

<!-- Ofertas Especiais -->
@if($ofertas->count() > 0)
<section id="ofertas" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title">🔥 Ofertas Especiais</h2>
        <span class="badge bg-danger fs-6">Preços Imperdíveis!</span>
    </div>
    <div class="row">
        @foreach($ofertas as $livro)
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card livro-card h-100">
                <div class="position-relative">
                    @if($livro->imagem)
                        <img src="{{ $livro->imagem_url }}" class="livro-image" alt="{{ $livro->titulo }}">
                    @else
                        <div class="livro-image bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-book fa-3x text-muted"></i>
                        </div>
                    @endif
                    
                    <!-- Badge de oferta -->
                    <div class="position-absolute top-0 start-0 m-2">
                        <span class="badge bg-danger">OFERTA</span>
                    </div>
                </div>
                
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title fw-bold mb-2">
                        {{ Str::limit($livro->titulo, 40) }}
                    </h6>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-user-edit me-1"></i>{{ $livro->autor }}
                    </p>
                    
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="h5 text-success mb-0">{{ $livro->preco_formatado }}</span>
                                <small class="text-muted text-decoration-line-through ms-1">
                                    R$ {{ number_format($livro->preco * 1.2, 2, ',', '.') }}
                                </small>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            @if($livro->estoque > 0)
                                <form method="POST" action="{{ route('cart.add', $livro) }}">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-cart-plus me-1"></i>
                                        Aproveitar Oferta
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-secondary btn-sm" disabled>
                                    Esgotado
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

@if($livros->count() > 0)
<section class="mb-5">
    <h2 class="section-title text-center w-100">📚 Nosso Catálogo</h2>
    <div class="row">
        @foreach($livros as $livro)
            <div class="col-lg-3 col-md-6 mb-4">
                @include('components.livro-card', ['livro' => $livro])
            </div>
        @endforeach
    </div>
    <div class="d-flex justify-content-center mt-4">
        {{ $livros->links() }}
    </div>
</section>
@endif

<!-- Call to Action -->
<section class="text-center py-5 bg-light rounded-3">
    <div class="container">
        <h2 class="section-title">Pronto para começar sua jornada literária?</h2>
        <p class="lead text-muted mb-4">
            Junte-se a milhares de leitores que já descobriram seus livros favoritos conosco.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="{{ route('loja.catalogo') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-search me-2"></i>
                Explorar Catálogo
            </a>
            @guest
            <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-user-plus me-2"></i>
                Criar Conta
            </a>
            @endguest
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
// Função para favoritar livros
function toggleFavorite(livroId) {
    @auth
    fetch(`/livros/${livroId}/favorite`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const button = document.querySelector(`[data-livro-id="${livroId}"]`);
        const icon = button.querySelector('i');
        if (data.favorited) {
            icon.classList.remove('far');
            icon.classList.add('fas', 'text-danger');
        } else {
            icon.classList.remove('fas', 'text-danger');
            icon.classList.add('far');
        }
    })
    .catch(error => console.error('Erro:', error));
    @else
    window.location.href = '{{ route("login") }}';
    @endauth
}

// Smooth scroll para âncoras
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>
@endpush