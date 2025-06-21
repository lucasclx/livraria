{{-- resources/views/components/pagination-custom.blade.php --}}
@props([
    'paginator',
    'showNumbers' => true,
    'showInfo' => true,
    'showJumper' => false,
    'theme' => 'default', // default, minimal, rounded
    'size' => 'md' // sm, md, lg
])

@if ($paginator->hasPages())
<div class="pagination-wrapper pagination-{{ $theme }} pagination-{{ $size }}">
    @if($showInfo)
    <div class="pagination-info">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="pagination-summary">
                <span class="text-muted">
                    Mostrando 
                    <strong>{{ $paginator->firstItem() ?? 0 }}</strong> 
                    a 
                    <strong>{{ $paginator->lastItem() ?? 0 }}</strong> 
                    de 
                    <strong>{{ $paginator->total() }}</strong> 
                    resultados
                </span>
            </div>
            
            @if($showJumper && $paginator->lastPage() > 10)
            <div class="pagination-jumper">
                <div class="input-group input-group-sm" style="width: 120px;">
                    <input type="number" 
                           class="form-control text-center" 
                           placeholder="Página"
                           min="1" 
                           max="{{ $paginator->lastPage() }}"
                           value="{{ $paginator->currentPage() }}"
                           data-pagination-jumper>
                    <button class="btn btn-outline-secondary" 
                            type="button"
                            data-pagination-go>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <nav aria-label="Navegação de páginas" class="pagination-nav">
        <ul class="pagination pagination-custom justify-content-center mb-0">
            {{-- First Page Link --}}
            @if ($paginator->currentPage() > 3 && $paginator->lastPage() > 7)
                <li class="page-item">
                    <a class="page-link page-link-first" 
                       href="{{ $paginator->url(1) }}"
                       aria-label="Primeira página"
                       data-page="1">
                        <i class="fas fa-angle-double-left"></i>
                        @if($size !== 'sm')
                            <span class="d-none d-md-inline ms-1">Primeira</span>
                        @endif
                    </a>
                </li>
            @endif

            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link page-link-prev">
                        <i class="fas fa-angle-left"></i>
                        @if($size !== 'sm')
                            <span class="d-none d-md-inline ms-1">Anterior</span>
                        @endif
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link page-link-prev" 
                       href="{{ $paginator->previousPageUrl() }}"
                       aria-label="Página anterior"
                       data-page="{{ $paginator->currentPage() - 1 }}">
                        <i class="fas fa-angle-left"></i>
                        @if($size !== 'sm')
                            <span class="d-none d-md-inline ms-1">Anterior</span>
                        @endif
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @if($showNumbers)
                @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active">
                            <span class="page-link page-link-number current">
                                {{ $page }}
                                <span class="sr-only">(atual)</span>
                            </span>
                        </li>
                    @elseif ($page == 1 || $page == $paginator->lastPage() || abs($page - $paginator->currentPage()) <= 2)
                        <li class="page-item">
                            <a class="page-link page-link-number" 
                               href="{{ $url }}"
                               data-page="{{ $page }}">
                                {{ $page }}
                            </a>
                        </li>
                    @elseif ($page == 2 || $page == $paginator->lastPage() - 1)
                        @if (abs($page - $paginator->currentPage()) > 2)
                            <li class="page-item disabled">
                                <span class="page-link dots">
                                    <i class="fas fa-ellipsis-h"></i>
                                </span>
                            </li>
                        @endif
                    @endif
                @endforeach
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link page-link-next" 
                       href="{{ $paginator->nextPageUrl() }}"
                       aria-label="Próxima página"
                       data-page="{{ $paginator->currentPage() + 1 }}">
                        @if($size !== 'sm')
                            <span class="d-none d-md-inline me-1">Próxima</span>
                        @endif
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link page-link-next">
                        @if($size !== 'sm')
                            <span class="d-none d-md-inline me-1">Próxima</span>
                        @endif
                        <i class="fas fa-angle-right"></i>
                    </span>
                </li>
            @endif

            {{-- Last Page Link --}}
            @if ($paginator->currentPage() < $paginator->lastPage() - 2 && $paginator->lastPage() > 7)
                <li class="page-item">
                    <a class="page-link page-link-last" 
                       href="{{ $paginator->url($paginator->lastPage()) }}"
                       aria-label="Última página"
                       data-page="{{ $paginator->lastPage() }}">
                        @if($size !== 'sm')
                            <span class="d-none d-md-inline me-1">Última</span>
                        @endif
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </li>
            @endif
        </ul>
    </nav>

    {{-- Per Page Selector --}}
    @if($showInfo && $paginator->total() > 10)
    <div class="pagination-per-page mt-3 text-center">
        <div class="d-inline-flex align-items-center">
            <label class="form-label me-2 mb-0 small text-muted">Itens por página:</label>
            <select class="form-select form-select-sm" 
                    style="width: auto;"
                    data-pagination-per-page>
                @foreach([10, 25, 50, 100] as $perPage)
                    <option value="{{ $perPage }}" 
                            {{ request('per_page', 25) == $perPage ? 'selected' : '' }}>
                        {{ $perPage }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    @endif

    {{-- Loading Overlay --}}
    <div class="pagination-loading" style="display: none;">
        <div class="d-flex justify-content-center align-items-center py-3">
            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <span class="text-muted">Carregando...</span>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Base Pagination Styles */
.pagination-wrapper {
    margin: 2rem 0;
}

.pagination-custom {
    --pagination-color: #6c757d;
    --pagination-bg: #fff;
    --pagination-border-color: #dee2e6;
    --pagination-hover-color: #495057;
    --pagination-hover-bg: #e9ecef;
    --pagination-active-color: #fff;
    --pagination-active-bg: #007bff;
    --pagination-disabled-color: #6c757d;
    --pagination-disabled-bg: #fff;
    
    gap: 0.25rem;
}

.pagination-custom .page-link {
    color: var(--pagination-color);
    background-color: var(--pagination-bg);
    border: 1px solid var(--pagination-border-color);
    padding: 0.5rem 0.75rem;
    margin: 0 2px;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s ease-in-out;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    height: 44px;
    font-weight: 500;
}

.pagination-custom .page-link:hover {
    color: var(--pagination-hover-color);
    background-color: var(--pagination-hover-bg);
    border-color: var(--pagination-border-color);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pagination-custom .page-item.active .page-link {
    color: var(--pagination-active-color);
    background-color: var(--pagination-active-bg);
    border-color: var(--pagination-active-bg);
    box-shadow: 0 2px 8px rgba(0,123,255,0.3);
    transform: scale(1.05);
}

.pagination-custom .page-item.disabled .page-link {
    color: var(--pagination-disabled-color);
    background-color: var(--pagination-disabled-bg);
    border-color: var(--pagination-border-color);
    opacity: 0.5;
    cursor: not-allowed;
}

/* Theme Variations */
.pagination-rounded .page-link {
    border-radius: 50%;
    width: 44px;
    height: 44px;
    padding: 0;
}

.pagination-minimal .page-link {
    border: none;
    background: transparent;
    color: var(--pagination-color);
}

.pagination-minimal .page-link:hover {
    background: var(--pagination-hover-bg);
    border-radius: 8px;
}

.pagination-minimal .page-item.active .page-link {
    background: var(--pagination-active-bg);
    color: var(--pagination-active-color);
    border-radius: 8px;
}

/* Size Variations */
.pagination-sm .page-link {
    padding: 0.25rem 0.5rem;
    min-width: 32px;
    height: 32px;
    font-size: 0.875rem;
}

.pagination-lg .page-link {
    padding: 0.75rem 1rem;
    min-width: 52px;
    height: 52px;
    font-size: 1.125rem;
}

/* Special Elements */
.page-link.dots {
    border: none;
    background: transparent;
    color: var(--pagination-disabled-color);
    cursor: default;
}

.page-link.dots:hover {
    background: transparent;
    transform: none;
    box-shadow: none;
}

/* Info and Controls */
.pagination-summary {
    font-size: 0.9rem;
}

.pagination-jumper .form-control {
    border-radius: 6px 0 0 6px;
}

.pagination-jumper .btn {
    border-radius: 0 6px 6px 0;
}

.pagination-per-page select {
    border-radius: 6px;
    border: 1px solid var(--pagination-border-color);
    padding: 0.25rem 0.5rem;
}

/* Loading State */
.pagination-wrapper.loading {
    opacity: 0.6;
    pointer-events: none;
}

.pagination-loading {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 8px;
    z-index: 10;
}

/* Mobile Optimizations */
@media (max-width: 576px) {
    .pagination-custom {
        gap: 0.125rem;
    }
    
    .pagination-custom .page-link {
        margin: 0 1px;
        min-width: 36px;
        height: 36px;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .pagination-info {
        text-align: center;
    }
    
    .pagination-summary {
        font-size: 0.8rem;
    }
    
    .pagination-jumper {
        margin-top: 0.5rem;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .pagination-custom {
        --pagination-color: #e9ecef;
        --pagination-bg: #343a40;
        --pagination-border-color: #495057;
        --pagination-hover-color: #fff;
        --pagination-hover-bg: #495057;
        --pagination-active-color: #fff;
        --pagination-active-bg: #007bff;
        --pagination-disabled-color: #6c757d;
        --pagination-disabled-bg: #343a40;
    }
    
    .pagination-loading {
        background: rgba(52, 58, 64, 0.8);
    }
}

/* Animation for page transitions */
@keyframes pageLoad {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.pagination-wrapper.page-loading {
    animation: pageLoad 0.3s ease-out;
}

/* Accessibility improvements */
.page-link:focus {
    outline: 2px solid #007bff;
    outline-offset: 2px;
    box-shadow: none;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paginationWrapper = document.querySelector('.pagination-wrapper');
    const jumperInput = document.querySelector('[data-pagination-jumper]');
    const jumperButton = document.querySelector('[data-pagination-go]');
    const perPageSelect = document.querySelector('[data-pagination-per-page]');
    const pageLinks = document.querySelectorAll('.page-link[data-page]');
    
    // Page jumper functionality
    if (jumperInput && jumperButton) {
        jumperButton.addEventListener('click', function() {
            const page = parseInt(jumperInput.value);
            const maxPage = parseInt(jumperInput.getAttribute('max'));
            
            if (page >= 1 && page <= maxPage) {
                navigateToPage(page);
            } else {
                showInvalidPageMessage();
            }
        });
        
        jumperInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                jumperButton.click();
            }
        });
    }
    
    // Per page selector
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('per_page', this.value);
            url.searchParams.delete('page'); // Reset to first page
            
            setLoadingState(true);
            window.location.href = url.toString();
        });
    }
    
    // Enhanced page link behavior
    pageLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.dataset.page;
            navigateToPage(page);
        });
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName.toLowerCase() === 'input') return;
        
        const currentPage = {{ $paginator->currentPage() }};
        const lastPage = {{ $paginator->lastPage() }};
        
        switch(e.key) {
            case 'ArrowLeft':
                if (e.ctrlKey && currentPage > 1) {
                    e.preventDefault();
                    navigateToPage(currentPage - 1);
                }
                break;
                
            case 'ArrowRight':
                if (e.ctrlKey && currentPage < lastPage) {
                    e.preventDefault();
                    navigateToPage(currentPage + 1);
                }
                break;
                
            case 'Home':
                if (e.ctrlKey && currentPage > 1) {
                    e.preventDefault();
                    navigateToPage(1);
                }
                break;
                
            case 'End':
                if (e.ctrlKey && currentPage < lastPage) {
                    e.preventDefault();
                    navigateToPage(lastPage);
                }
                break;
        }
    });
    
    function navigateToPage(page) {
        const url = new URL(window.location);
        
        if (page == 1) {
            url.searchParams.delete('page');
        } else {
            url.searchParams.set('page', page);
        }
        
        setLoadingState(true);
        
        // Use History API for smoother navigation
        if (window.history && window.history.pushState) {
            window.history.pushState({page: page}, '', url.toString());
            loadPageContent(url.toString());
        } else {
            window.location.href = url.toString();
        }
    }
    
    async function loadPageContent(url) {
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });
            
            if (response.ok) {
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Update content area
                const newContent = doc.querySelector('#main-content');
                const currentContent = document.querySelector('#main-content');
                
                if (newContent && currentContent) {
                    currentContent.innerHTML = newContent.innerHTML;
                    
                    // Scroll to top smoothly
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                    
                    // Reinitialize components if needed
                    reinitializeComponents();
                } else {
                    window.location.href = url;
                }
            } else {
                window.location.href = url;
            }
        } catch (error) {
            console.error('Error loading page:', error);
            window.location.href = url;
        } finally {
            setLoadingState(false);
        }
    }
    
    function setLoadingState(loading) {
        const loadingOverlay = document.querySelector('.pagination-loading');
        
        if (loading) {
            paginationWrapper?.classList.add('loading');
            if (loadingOverlay) {
                loadingOverlay.style.display = 'block';
            }
        } else {
            paginationWrapper?.classList.remove('loading');
            paginationWrapper?.classList.add('page-loading');
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }
            
            // Remove loading animation after it completes
            setTimeout(() => {
                paginationWrapper?.classList.remove('page-loading');
            }, 300);
        }
    }
    
    function showInvalidPageMessage() {
        const message = document.createElement('div');
        message.className = 'alert alert-warning alert-dismissible fade show mt-2';
        message.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            Número de página inválido. Por favor, insira um valor entre 1 e {{ $paginator->lastPage() }}.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const jumperContainer = jumperInput.closest('.pagination-jumper');
        jumperContainer.appendChild(message);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (message.parentNode) {
                message.remove();
            }
        }, 3000);
    }
    
    function reinitializeComponents() {
        // Reinitialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Reinitialize other components as needed
        if (window.initializeBookCards) {
            window.initializeBookCards();
        }
        
        // Trigger custom event for other scripts
        window.dispatchEvent(new CustomEvent('pageContentUpdated'));
    }
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.page) {
            setLoadingState(true);
            loadPageContent(window.location.href);
        }
    });
    
    // Preload next/previous pages for better UX
    const nextPageLink = document.querySelector('.page-link-next');
    const prevPageLink = document.querySelector('.page-link-prev');
    
    if (nextPageLink && 'requestIdleCallback' in window) {
        requestIdleCallback(() => {
            preloadPage(nextPageLink.href);
        });
    }
    
    if (prevPageLink && 'requestIdleCallback' in window) {
        requestIdleCallback(() => {
            preloadPage(prevPageLink.href);
        });
    }
    
    function preloadPage(url) {
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        document.head.appendChild(link);
    }
});
</script>
@endpush
@endif