{{-- Componente de Loading Spinner sem parâmetros --}}
<div class="loading-spinner-container d-flex justify-content-center align-items-center">
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <div class="loading-text mt-2">
            <small class="text-muted">Carregando...</small>
        </div>
    </div>
</div>

<style>
.loading-spinner-container {
    min-height: 200px;
    width: 100%;
}

.loading-spinner {
    text-align: center;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    color: #6c757d;
    font-size: 0.9rem;
}

/* Variações de cores para diferentes contextos */
.loading-spinner .spinner-border.text-success {
    border-color: #198754;
    border-right-color: transparent;
}

.loading-spinner .spinner-border.text-warning {
    border-color: #ffc107;
    border-right-color: transparent;
}

.loading-spinner .spinner-border.text-danger {
    border-color: #dc3545;
    border-right-color: transparent;
}

/* Responsivo */
@media (max-width: 576px) {
    .spinner-border {
        width: 2rem;
        height: 2rem;
    }
    
    .loading-text {
        font-size: 0.8rem;
    }
}
</style>