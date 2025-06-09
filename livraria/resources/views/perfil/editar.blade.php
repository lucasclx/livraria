<?php
// resources/views/perfil/editar.blade.php - Página de edição de perfil

@extends('layouts.app')
@section('title', 'Editar Perfil - Biblioteca Literária')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('perfil.index') }}">Meu Perfil</a></li>
                    <li class="breadcrumb-item active">Editar Perfil</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>Editar Perfil
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs" id="perfilTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="dados-tab" data-bs-toggle="tab" data-bs-target="#dados" type="button" role="tab">
                                <i class="fas fa-user me-1"></i>Dados Pessoais
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="senha-tab" data-bs-toggle="tab" data-bs-target="#senha" type="button" role="tab">
                                <i class="fas fa-lock me-1"></i>Alterar Senha
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="preferencias-tab" data-bs-toggle="tab" data-bs-target="#preferencias" type="button" role="tab">
                                <i class="fas fa-cog me-1"></i>Preferências
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-4" id="perfilTabsContent">
                        <!-- Dados Pessoais -->
                        <div class="tab-pane fade show active" id="dados" role="tabpanel">
                            <form method="POST" action="{{ route('perfil.preferencias') }}">
                                @csrf

                                <h5 class="mb-3">Notificações</h5>
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="email_promocoes" name="email_promocoes" 
                                                   {{ old('email_promocoes', $user->preferencias['email_promocoes'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="email_promocoes">
                                                Receber promoções por e-mail
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="email_novidades" name="email_novidades"
                                                   {{ old('email_novidades', $user->preferencias['email_novidades'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="email_novidades">
                                                Receber novidades e lançamentos
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="email_pedidos" name="email_pedidos"
                                                   {{ old('email_pedidos', $user->preferencias['email_pedidos'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="email_pedidos">
                                                Receber atualizações de pedidos
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mb-3">Privacidade</h5>
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="perfil_publico" name="perfil_publico"
                                                   {{ old('perfil_publico', $user->preferencias['perfil_publico'] ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perfil_publico">
                                                Tornar minhas avaliações públicas
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="mostrar_nome" name="mostrar_nome"
                                                   {{ old('mostrar_nome', $user->preferencias['mostrar_nome'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="mostrar_nome">
                                                Mostrar meu nome nas avaliações
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-save me-1"></i>Salvar Preferências
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Password strength indicator
    const passwordInput = document.getElementById('nova_senha');
    const strengthBar = document.getElementById('password-strength');
    const feedback = document.getElementById('password-feedback');

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            
            strengthBar.style.width = strength.percentage + '%';
            strengthBar.className = 'progress-bar ' + strength.class;
            feedback.textContent = strength.text;
        });
    }

    // Phone mask
    const phoneInput = document.getElementById('telefone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            this.value = value;
        });
    }

    function calculatePasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score++;
        if (password.match(/[a-z]/)) score++;
        if (password.match(/[A-Z]/)) score++;
        if (password.match(/[0-9]/)) score++;
        if (password.match(/[^a-zA-Z0-9]/)) score++;
        
        const strength = {
            0: { percentage: 0, class: '', text: '' },
            1: { percentage: 20, class: 'bg-danger', text: 'Muito fraca' },
            2: { percentage: 40, class: 'bg-warning', text: 'Fraca' },
            3: { percentage: 60, class: 'bg-info', text: 'Média' },
            4: { percentage: 80, class: 'bg-primary', text: 'Forte' },
            5: { percentage: 100, class: 'bg-success', text: 'Muito forte' }
        };
        
        return strength[score];
    }
});
</script>

<style>
.nav-tabs .nav-link {
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    color: #495057;
    font-weight: 500;
}

.form-switch .form-check-input:checked {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.toggle-password {
    border-left: 0;
}

.progress {
    background-color: #e9ecef;
}
</style>
@endsection

<?php
// resources/views/perfil/pedidos.blade.php - Página de histórico de pedidos

@extends('layouts.app')
@section('title', 'Meus Pedidos - Biblioteca Literária')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('perfil.index') }}">Meu Perfil</a></li>
                    <li class="breadcrumb-item active">Meus Pedidos</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="page-title">
                    <i class="fas fa-shopping-bag me-2"></i>Meus Pedidos
                </h2>
                <span class="badge bg-primary fs-6">{{ $pedidos->total() }} pedidos</span>
            </div>

            @if($pedidos->count() > 0)
                @foreach($pedidos as $pedido)
                <div class="card mb-4 order-card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0">Pedido #{{ $pedido->order_number ?? $pedido->id }}</h5>
                                <small class="text-muted">{{ $pedido->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <span class="badge bg-{{ 
                                    $pedido->status == 'delivered' ? 'success' : 
                                    ($pedido->status == 'cancelled' ? 'danger' : 
                                    ($pedido->status == 'shipped' ? 'info' : 'warning')) 
                                }} fs-6 me-2">
                                    {{ ucfirst($pedido->status) }}
                                </span>
                                <strong class="text-success h5">R$ {{ number_format($pedido->total, 2, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="mb-3">Itens do Pedido</h6>
                                @foreach($pedido->cart->items as $item)
                                <div class="d-flex align-items-center mb-3 item-pedido">
                                    <div class="item-image me-3">
                                        @if($item->livro->imagem)
                                            <img src="{{ $item->livro->imagem_url }}" 
                                                 class="img-thumbnail" 
                                                 style="width: 60px; height: 80px; object-fit: cover;"
                                                 alt="{{ $item->livro->titulo }}">
                                        @else
                                            <div class="placeholder-image d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 80px; background: #f8f9fa;">
                                                <i class="fas fa-book text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="{{ route('loja.detalhes', $item->livro) }}" class="text-decoration-none">
                                                {{ $item->livro->titulo }}
                                            </a>
                                        </h6>
                                        <p class="text-muted small mb-1">{{ $item->livro->autor }}</p>
                                        <p class="mb-0">
                                            <span class="text-muted">Quantidade:</span> {{ $item->quantity }} × 
                                            <span class="text-success fw-bold">R$ {{ number_format($item->price, 2, ',', '.') }}</span>
                                        </p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-3">Detalhes do Pedido</h6>
                                
                                <!-- Endereço de Entrega -->
                                @if($pedido->shipping_address)
                                <div class="mb-3">
                                    <strong class="small">Endereço de Entrega:</strong>
                                    <p class="small text-muted mb-0">
                                        {{ $pedido->shipping_address['street'] ?? '' }}<br>
                                        {{ $pedido->shipping_address['city'] ?? '' }} - {{ $pedido->shipping_address['state'] ?? '' }}<br>
                                        CEP: {{ $pedido->shipping_address['postal_code'] ?? '' }}
                                    </p>
                                </div>
                                @endif

                                <!-- Forma de Pagamento -->
                                @if($pedido->payment_method)
                                <div class="mb-3">
                                    <strong class="small">Pagamento:</strong>
                                    <p class="small text-muted mb-0">
                                        {{ ucfirst(str_replace('_', ' ', $pedido->payment_method)) }}
                                    </p>
                                </div>
                                @endif

                                <!-- Resumo Financeiro -->
                                <div class="border-top pt-3">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Subtotal:</span>
                                        <span>R$ {{ number_format($pedido->total - ($pedido->shipping_cost ?? 0), 2, ',', '.') }}</span>
                                    </div>
                                    @if($pedido->shipping_cost > 0)
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Frete:</span>
                                        <span>R$ {{ number_format($pedido->shipping_cost, 2, ',', '.') }}</span>
                                    </div>
                                    @endif
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total:</span>
                                        <span class="text-success">R$ {{ number_format($pedido->total, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @if($pedido->tracking_code)
                                    <small class="text-muted">
                                        <i class="fas fa-truck me-1"></i>
                                        Código de rastreamento: <strong>{{ $pedido->tracking_code }}</strong>
                                    </small>
                                @endif
                            </div>
                            <div class="btn-group btn-group-sm">
                                @if($pedido->status == 'pending' && $pedido->canBeCancelled())
                                    <button class="btn btn-outline-danger btn-sm" 
                                            onclick="cancelarPedido({{ $pedido->id }})">
                                        <i class="fas fa-times me-1"></i>Cancelar
                                    </button>
                                @endif
                                
                                <button class="btn btn-outline-primary btn-sm" 
                                        onclick="avaliarPedido({{ $pedido->id }})">
                                    <i class="fas fa-star me-1"></i>Avaliar Produtos
                                </button>
                                
                                <button class="btn btn-outline-secondary btn-sm" 
                                        onclick="comprarNovamente({{ $pedido->id }})">
                                    <i class="fas fa-redo me-1"></i>Comprar Novamente
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Paginação -->
                <div class="d-flex justify-content-center">
                    {{ $pedidos->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-shopping-bag fa-5x text-muted mb-4"></i>
                    <h4>Nenhum pedido encontrado</h4>
                    <p class="text-muted mb-4">Você ainda não fez nenhum pedido. Que tal começar agora?</p>
                    <a href="{{ route('loja.catalogo') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-book me-2"></i>Explorar Livros
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.order-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.order-card:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.item-pedido {
    border-bottom: 1px solid #f8f9fa;
    padding-bottom: 1rem;
}

.item-pedido:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.item-image img, .placeholder-image {
    border-radius: 8px;
}
</style>

<script>
function cancelarPedido(pedidoId) {
    if (confirm('Tem certeza que deseja cancelar este pedido?')) {
        // Implementar cancelamento
        fetch(`/pedidos/${pedidoId}/cancelar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(() => location.reload())
        .catch(() => alert('Erro ao cancelar pedido'));
    }
}

function avaliarPedido(pedidoId) {
    // Redirecionar para página de avaliação
    window.location.href = `/pedidos/${pedidoId}/avaliar`;
}

function comprarNovamente(pedidoId) {
    if (confirm('Deseja adicionar todos os itens deste pedido ao seu carrinho?')) {
        fetch(`/pedidos/${pedidoId}/comprar-novamente`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(() => {
            alert('Itens adicionados ao carrinho!');
            window.location.href = '/carrinho';
        })
        .catch(() => alert('Erro ao adicionar itens ao carrinho'));
    }
}
</script>
@endsectionperfil.atualizar') }}">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Nome Completo *</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">E-mail *</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="telefone" class="form-label">Telefone</label>
                                        <input type="tel" class="form-control @error('telefone') is-invalid @enderror" 
                                               id="telefone" name="telefone" value="{{ old('telefone', $user->telefone) }}"
                                               placeholder="(11) 99999-9999">
                                        @error('telefone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                                        <input type="date" class="form-control @error('data_nascimento') is-invalid @enderror" 
                                               id="data_nascimento" name="data_nascimento" 
                                               value="{{ old('data_nascimento', $user->data_nascimento?->format('Y-m-d')) }}">
                                        @error('data_nascimento')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="genero" class="form-label">Gênero</label>
                                        <select class="form-select @error('genero') is-invalid @enderror" id="genero" name="genero">
                                            <option value="">Selecione</option>
                                            <option value="masculino" {{ old('genero', $user->genero) == 'masculino' ? 'selected' : '' }}>Masculino</option>
                                            <option value="feminino" {{ old('genero', $user->genero) == 'feminino' ? 'selected' : '' }}>Feminino</option>
                                            <option value="outro" {{ old('genero', $user->genero) == 'outro' ? 'selected' : '' }}>Outro</option>
                                            <option value="prefiro_nao_informar" {{ old('genero', $user->genero) == 'prefiro_nao_informar' ? 'selected' : '' }}>Prefiro não informar</option>
                                        </select>
                                        @error('genero')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('perfil.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Voltar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Salvar Alterações
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Alterar Senha -->
                        <div class="tab-pane fade" id="senha" role="tabpanel">
                            <form method="POST" action="{{ route('perfil.alterar-senha') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="senha_atual" class="form-label">Senha Atual *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('senha_atual') is-invalid @enderror" 
                                               id="senha_atual" name="senha_atual" required>
                                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="senha_atual">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('senha_atual')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="nova_senha" class="form-label">Nova Senha *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('nova_senha') is-invalid @enderror" 
                                               id="nova_senha" name="nova_senha" required>
                                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="nova_senha">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Mínimo 8 caracteres</small>
                                    @error('nova_senha')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="nova_senha_confirmation" class="form-label">Confirmar Nova Senha *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" 
                                               id="nova_senha_confirmation" name="nova_senha_confirmation" required>
                                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="nova_senha_confirmation">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Indicador de força da senha -->
                                <div class="mb-3">
                                    <label class="form-label">Força da Senha</label>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar" id="password-strength" style="width: 0%"></div>
                                    </div>
                                    <small id="password-feedback" class="text-muted"></small>
                                </div>

                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-key me-1"></i>Alterar Senha
                                </button>
                            </form>
                        </div>

                        <!-- Preferências -->
                        <div class="tab-pane fade" id="preferencias" role="tabpanel">
                            <form method="POST" action="{{ route('