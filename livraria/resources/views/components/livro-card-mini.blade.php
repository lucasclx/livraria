{{-- resources/views/components/livro-card-mini.blade.php --}}
<div class="card livro-card-mini h-100 shadow-sm">
    <a href="{{ route('loja.detalhes', $livro) }}" class="text-decoration-none">
        <div class="position-relative">
            <!-- Imagem do Livro -->
            <div class="livro-image-mini-container">
                @if($livro->imagem)
                    <img src="{{ $livro->imagem_url }}" class="card-img-top livro-image-mini" alt="{{ $livro->titulo }}">
                @else
                    <div class="livro-image-mini placeholder-image-mini d-flex align-items-center justify-content-center">
                        <i class="fas fa-book fa-2x text-muted"></i>
                    </div>
                @endif
            </div>

            <!-- Badge de Status -->
            <div class="position-absolute top-0 end-0 m-1">
                @if($livro->estoque > 5)
                    <span class="badge bg-success badge-sm">✓</span>
                @elseif($livro->estoque > 0)
                    <span class="badge bg-warning badge-sm">!</span>
                @else
                    <span class="badge bg-danger badge-sm">✗</span>
                @endif
            </div>
        </div>

        <div class="card-body p-2">
            <!-- Título -->
            <h6 class="card-title mb-1 small fw-bold text-dark livro-titulo-mini">
                {{ Str::limit($livro->titulo, 30) }}
            </h6>

            <!-- Autor -->
            <p class="text-muted mb-1" style="font-size: 0.7rem;">
                {{ Str::limit($livro->autor, 20) }}
            </p>

            <!-- Preço -->
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-success fw-bold small">{{ $livro->preco_formatado }}</span>
                @if($livro->categoria)
                    <span class="badge bg-light text-dark badge-sm" style="font-size: 0.6rem;">
                        {{ Str::limit($livro->categoria, 8) }}
                    </span>
                @endif
            </div>
        </div>
    </a>

    <!-- Ação rápida -->
    <div class="card-footer p-2 bg-transparent border-0">
        @if($livro->estoque > 0)
            <form method="POST" action="{{ route('cart.add', $livro) }}" class="quick-add-form">
                @csrf
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-primary btn-sm w-100 quick-add-btn" 
                        data-bs-toggle="tooltip" title="Adicionar ao carrinho">
                    <i class="fas fa-plus"></i>
                </button>
            </form>
        @else
            <button class="btn btn-secondary btn-sm w-100" disabled>
                <i class="fas fa-ban"></i>
            </button>
        @endif
    </div>
</div>

<style>
.livro-card-mini {
    transition: all 0.3s ease;
    border: none;
    overflow: hidden;
}

.livro-card-mini:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
}

.livro-image-mini-container {
    height: 150px;
    overflow: hidden;
    position: relative;
}

.livro-image-mini {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.livro-card-mini:hover .livro-image-mini {
    transform: scale(1.05);
}

.placeholder-image-mini {
    height: 100%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
}

.livro-titulo-mini:hover {
    color: var(--bs-primary) !important;
}

.badge-sm {
    font-size: 0.6rem;
    padding: 0.2rem 0.4rem;
}

.quick-add-btn {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.livro-card-mini:hover .quick-add-btn {
    opacity: 1;
}

.quick-add-form {
    position: relative;
}

.card-footer {
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar ao carrinho com feedback
    document.querySelectorAll('.quick-add-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const button = this.querySelector('.quick-add-btn');
            const originalContent = button.innerHTML;
            
            // Feedback visual
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
            
            // Submeter form
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                if (response.ok) {
                    button.innerHTML = '<i class="fas fa-check"></i>';
                    button.classList.remove('btn-primary');
                    button.classList.add('btn-success');
                    
                    setTimeout(() => {
                        button.innerHTML = originalContent;
                        button.classList.remove('btn-success');
                        button.classList.add('btn-primary');
                        button.disabled = false;
                    }, 2000);
                }
            })
            .catch(error => {
                button.innerHTML = originalContent;
                button.disabled = false;
                console.error('Erro:', error);
            });
        });
    });
});
</script>