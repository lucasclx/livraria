{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lojinha Virtual')</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-blue: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-gray: #6b7280;
            --success-green: #10b981;
            --warning-yellow: #f59e0b;
            --danger-red: #ef4444;
            --light-bg: #f8fafc;
            --dark-text: #1f2937;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
            line-height: 1.6;
        }

        /* Header Styles */
        .navbar {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-dark) 100%);
            box-shadow: var(--shadow-md);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand::before {
            content: '🛍️';
            font-size: 1.5em;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            position: relative;
        }

        .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        /* Card Styles */
        .card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-dark) 100%);
            color: white;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            border: none;
            padding: 1.25rem;
        }

        /* Product Card Styles */
        .product-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.1), transparent);
            transition: left 0.6s ease;
            z-index: 1;
        }

        .product-card:hover::before {
            left: 100%;
        }

        .product-card:hover {
            transform: translateY(-8px) scale(1.01);
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.15);
        }

        .product-cover {
            position: relative;
            overflow: hidden;
        }

        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-gold {
            background: linear-gradient(135deg, var(--warning-yellow) 0%, #d97706 100%);
            border: none;
            color: white;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .btn-gold:hover {
            background: linear-gradient(135deg, #d97706 0%, var(--warning-yellow) 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-outline-elegant {
            border: 2px solid var(--primary-blue);
            color: var(--primary-blue);
            background: transparent;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-outline-elegant:hover {
            background: var(--primary-blue);
            color: white;
            transform: translateY(-2px);
        }

        /* Alert Styles */
        .alert {
            border: none;
            border-radius: 12px;
            border-left: 4px solid;
            padding: 1rem 1.5rem;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            border-left-color: var(--success-green);
            color: #065f46;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
            border-left-color: var(--danger-red);
            color: #991b1b;
        }

        .alert-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
            border-left-color: var(--warning-yellow);
            color: #92400e;
        }

        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            color: var(--dark-text);
            font-weight: 600;
        }

        .page-title {
            font-size: 2.5rem;
            color: var(--dark-text);
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60%;
            height: 3px;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-dark) 100%);
            border-radius: 2px;
        }

        /* Price styling */
        .price {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1.25rem;
            color: var(--success-green);
        }

        /* Badge Styles */
        .badge {
            border-radius: 20px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-category {
            background: linear-gradient(135deg, var(--secondary-gray) 0%, #4b5563 100%);
            color: white;
        }

        .badge-stock-ok {
            background: linear-gradient(135deg, var(--success-green) 0%, #059669 100%);
            color: white;
        }

        .badge-stock-low {
            background: linear-gradient(135deg, var(--warning-yellow) 0%, #d97706 100%);
            color: white;
        }

        .badge-stock-out {
            background: linear-gradient(135deg, var(--danger-red) 0%, #dc2626 100%);
            color: white;
        }

        /* Filter Card */
        .filter-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
        }

        .form-control, .form-select {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: white;
            color: var(--dark-text);
            transition: all 0.3s ease;
            padding: 0.75rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
            background: white;
        }

        /* Stats Card */
        .stats-card {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.05) 0%, rgba(29, 78, 216, 0.05) 100%);
            border: 1px solid rgba(37, 99, 235, 0.1);
        }

        .stat-number {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 2rem;
            color: var(--primary-blue);
        }

        /* Cart Badge */
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--danger-red);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--dark-text) 0%, #374151 100%);
            color: white;
            padding: 2rem 0;
            margin-top: 4rem;
        }

        /* Animation */
        @keyframes shopFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }

        .floating-shop {
            animation: shopFloat 4s ease-in-out infinite;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.25rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .card {
                margin-bottom: 1rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
        }

        /* Loading animation */
        .loading-shop::before {
            content: '🛍️ 📦 🛒';
            font-size: 1.5rem;
            animation: shopFloat 2s ease-in-out infinite;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-dark) 100%);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('produtos.index') }}">
                Lojinha Virtual
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav me-auto">
                    <a class="nav-link" href="{{ route('produtos.index') }}">
                        <i class="fas fa-home me-1"></i> Início
                    </a>
                    <a class="nav-link" href="{{ route('produtos.destaques') }}">
                        <i class="fas fa-star me-1"></i> Destaques
                    </a>
                    <a class="nav-link" href="{{ route('produtos.promocoes') }}">
                        <i class="fas fa-fire me-1"></i> Promoções
                    </a>
                    
                    <!-- Dropdown Categorias -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-th-large me-1"></i> Categorias
                        </a>
                        <ul class="dropdown-menu">
                            @php
                                $categorias = \App\Models\Produto::select('categoria')->whereNotNull('categoria')->distinct()->orderBy('categoria')->pluck('categoria');
                            @endphp
                            @foreach($categorias as $categoria)
                                <li><a class="dropdown-item" href="{{ route('produtos.categoria', $categoria) }}">{{ $categoria }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                
                <div class="navbar-nav">
                    <!-- Carrinho -->
                    <a class="nav-link position-relative" href="{{ route('cart.index') }}">
                        <i class="fas fa-shopping-cart"></i>
                        @php
                            $cartCount = session('cart_id') ? \App\Models\Cart::withCount('items')->find(session('cart_id'))?->items_count : 0;
                        @endphp
                        @if($cartCount > 0)
                            <span class="cart-badge">{{ $cartCount }}</span>
                        @endif
                    </a>
                    
                    @auth
                        <!-- Dropdown do Usuário -->
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('orders.index') }}">
                                    <i class="fas fa-list me-2"></i> Meus Pedidos
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('favorites.index') }}">
                                    <i class="fas fa-heart me-2"></i> Favoritos
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('produtos.create') }}">
                                    <i class="fas fa-plus me-2"></i> Adicionar Produto
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i> Sair
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-1"></i> Entrar
                        </a>
                        <a class="nav-link" href="{{ route('register') }}">
                            <i class="fas fa-user-plus me-1"></i> Cadastrar
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-4">
        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Atenção!</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>🛍️ Lojinha Virtual</h5>
                    <p class="mb-0">Sua loja online completa com os melhores produtos.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <i class="fas fa-heart text-danger"></i>
                        Feito com amor para o seu trabalho da faculdade
                    </p>
                    <small>© {{ date('Y') }} - Sistema de E-commerce em Laravel</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Enhanced tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (!alert.classList.contains('no-auto-hide')) {
                    setTimeout(() => {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }, 5000);
                }
            });
        });

        // Loading states for forms
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processando...';
                        submitBtn.disabled = true;
                    }
                });
            });
        });

        // Smooth scrolling para links anchor
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
    
    @stack('scripts')
</body>
</html>