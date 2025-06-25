<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Livraria Mil Páginas')</title>
    
    <!-- CSS do Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-brown: #8B4513;
            --dark-brown: #654321;
            --light-brown: #D2B48C;
            --cream: #F5F5DC;
            --gold: #FFD700;
            --dark-gold: #DAA520;
            --white: #FFFFFF;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .btn-gold {
            background: linear-gradient(135deg, var(--gold) 0%, var(--dark-gold) 100%);
            border: none;
            color: var(--dark-brown);
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
            color: var(--dark-brown);
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            color: var(--dark-brown);
        }

        .floating-book {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary-brown) 0%, var(--dark-brown) 100%);
        }

        .cart-counter {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .navbar-nav .nav-link {
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        main {
            min-height: calc(100vh - 200px);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary shadow-lg">
        <div class="container">
            <a class="navbar-brand" href="{{ route('loja.index') }}">
                <i class="fas fa-book-open me-2"></i>
                Livraria Mil Páginas
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('loja.index') }}">
                            <i class="fas fa-home me-1"></i>Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('loja.catalogo') }}">
                            <i class="fas fa-book me-1"></i>Catálogo
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoriasDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-tags me-1"></i>Categorias
                        </a>
                        <ul class="dropdown-menu">
                            @php
                                $categorias = \App\Models\Categoria::ativo()->orderBy('nome')->limit(8)->get();
                            @endphp
                            @foreach($categorias as $categoria)
                                <li>
                                    <a class="dropdown-item" href="{{ route('loja.categoria', $categoria->slug) }}">
                                        {{ $categoria->nome }}
                                    </a>
                                </li>
                            @endforeach
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('loja.catalogo') }}">Ver Todas</a></li>
                        </ul>
                    </li>
                    @auth
                        @if(auth()->user()->is_admin)
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog me-1"></i>Admin
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('home') }}">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('livros.index') }}">Gerenciar Livros</a></li>
                                    <li><a class="dropdown-item" href="{{ route('categorias.index') }}">Gerenciar Categorias</a></li>
                                </ul>
                            </li>
                        @endif
                    @endauth
                </ul>

                <!-- Busca -->
                <form class="d-flex me-3" action="{{ route('loja.buscar') }}" method="GET">
                    <div class="input-group">
                        <input class="form-control" type="search" name="q" placeholder="Buscar livros..." value="{{ request('q') }}">
                        <button class="btn btn-outline-light" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <!-- Menu do usuário -->
                <ul class="navbar-nav">
                    @auth
                        <!-- Carrinho -->
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="{{ route('cart.index') }}">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="cart-counter" id="cart-counter" style="display: none;">0</span>
                            </a>
                        </li>
                        
                        <!-- Favoritos -->
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('loja.favoritos') }}">
                                <i class="fas fa-heart"></i>
                            </a>
                        </li>
                        
                        <!-- Menu do usuário -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>{{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('perfil.index') }}">Meu Perfil</a></li>
                                <li><a class="dropdown-item" href="{{ route('orders.index') }}">Meus Pedidos</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-1"></i>Sair
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>Entrar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-1"></i>Cadastrar
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Conteúdo Principal -->
    <main class="py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer (Componente sem parâmetros) -->
    <x-footer />

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Atualizar contador do carrinho
        function updateCartCounter() {
            fetch('/cart/count')
                .then(response => response.json())
                .then(data => {
                    const counter = document.getElementById('cart-counter');
                    if (counter && data.count !== undefined) {
                        counter.textContent = data.count;
                        if (data.count > 0) {
                            counter.style.display = 'flex';
                        } else {
                            counter.style.display = 'none';
                        }
                    }
                })
                .catch(error => console.error('Erro ao atualizar contador:', error));
        }

        // Carregar contador na inicialização
        document.addEventListener('DOMContentLoaded', function() {
            @auth
                updateCartCounter();
            @endauth
        });

        // Sistema de notificações toast
        window.showToast = function(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);';
            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                if (toast && toast.parentElement) {
                    toast.remove();
                }
            }, 4000);
        };

        // Auto-dismiss de alertas após 5 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert:not(.position-fixed)');
            alerts.forEach(alert => {
                if (alert.classList.contains('show')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    </script>
    
    @stack('scripts')
</body>
</html>