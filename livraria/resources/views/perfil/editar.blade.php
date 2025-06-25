@extends('layouts.app')
@section('title', 'Editar Perfil - Livraria Mil Páginas')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('perfil.index') }}">Meu Perfil</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Perfil</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>Editar Perfil
                    </h4>
                </div>
                <div class="card-body">
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
                        <div class="tab-pane fade show active" id="dados" role="tabpanel">
                            <form method="POST" action="{{ route('perfil.atualizar') }}">
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
                                               value="{{ old('data_nascimento', optional($user->data_nascimento)->format('Y-m-d')) }}">
                                        @error('data_nascimento')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Salvar Alterações
                                </button>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="senha" role="tabpanel">
                            <form method="POST" action="{{ route('perfil.alterarSenha') }}">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label for="senha_atual" class="form-label">Senha Atual *</label>
                                    <div class="input-group"><input type="password" class="form-control @error('senha_atual') is-invalid @enderror" id="senha_atual" name="senha_atual" required><button type="button" class="btn btn-outline-secondary toggle-password" data-target="senha_atual"><i class="fas fa-eye"></i></button></div>
                                    @error('senha_atual')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="nova_senha" class="form-label">Nova Senha *</label>
                                    <div class="input-group"><input type="password" class="form-control @error('nova_senha') is-invalid @enderror" id="nova_senha" name="nova_senha" required><button type="button" class="btn btn-outline-secondary toggle-password" data-target="nova_senha"><i class="fas fa-eye"></i></button></div>
                                    <small class="text-muted">Mínimo 8 caracteres</small>
                                    @error('nova_senha')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="nova_senha_confirmation" class="form-label">Confirmar Nova Senha *</label>
                                    <input type="password" class="form-control" id="nova_senha_confirmation" name="nova_senha_confirmation" required>
                                </div>
                                <button type="submit" class="btn btn-warning"><i class="fas fa-key me-1"></i>Alterar Senha</button>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="preferencias" role="tabpanel">
                            <form method="POST" action="{{ route('perfil.preferencias') }}">
                                @csrf
                                <h5 class="mb-3">Notificações</h5>
                                <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" id="email_promocoes" name="email_promocoes" @checked(old('email_promocoes', $user->preferencias['email_promocoes'] ?? true))><label class="form-check-label" for="email_promocoes">Receber promoções por e-mail</label></div>
                                <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" id="email_novidades" name="email_novidades" @checked(old('email_novidades', $user->preferencias['email_novidades'] ?? true))><label class="form-check-label" for="email_novidades">Receber novidades e lançamentos</label></div>
                                <h5 class="mt-4 mb-3">Privacidade</h5>
                                <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" id="perfil_publico" name="perfil_publico" @checked(old('perfil_publico', $user->preferencias['perfil_publico'] ?? false))><label class="form-check-label" for="perfil_publico">Tornar minhas avaliações públicas</label></div>
                                <button type="submit" class="btn btn-info mt-3"><i class="fas fa-save me-1"></i>Salvar Preferências</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.nav-tabs .nav-link { color: #6c757d; }
.nav-tabs .nav-link.active { color: #000; font-weight: bold; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manter a aba ativa após o reload da página
    const activeTab = localStorage.getItem('activePerfilTab');
    if (activeTab) {
        const tabEl = document.querySelector(`button[data-bs-target="${activeTab}"]`);
        if (tabEl) {
            new bootstrap.Tab(tabEl).show();
        }
    }
    // Salvar a aba clicada
    document.querySelectorAll('#perfilTabs button').forEach(tab => {
        tab.addEventListener('shown.bs.tab', event => {
            localStorage.setItem('activePerfilTab', event.target.getAttribute('data-bs-target'));
        });
    });

    // Mostrar/ocultar senha
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
});
</script>
@endpush