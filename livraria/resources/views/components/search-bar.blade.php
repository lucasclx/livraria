{{-- resources/views/components/search-bar.blade.php --}}
@props([
    'placeholder' => 'Buscar livros, autores, categorias...',
    'value' => '',
    'action' => null,
    'method' => 'GET',
    'showFilters' => false,
    'categories' => collect(),
    'id' => 'search-bar-' . uniqid()
])

<div class="search-bar-container" data-search-id="{{ $id }}">
    <form action="{{ $action ?? route('loja.buscar') }}" method="{{ $method }}" class="search-form" id="form-{{ $id }}">
        @if($method !== 'GET')
            @csrf
        @endif
        
        <div class="search-input-wrapper">
            <div class="input-group search-input-group">
                <span class="input-group-text search-icon">
                    <i class="fas fa-search"></i>
                </span>
                
                <input type="text" 
                       name="q" 
                       class="form-control search-input" 
                       placeholder="{{ $placeholder }}"
                       value="{{ $value }}"
                       autocomplete="off"
                       data-search-input="{{ $id }}">
                
                @if($showFilters)
                <button type="button" 
                        class="btn btn-outline-secondary filters-toggle" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#filters-{{ $id }}"
                        aria-expanded="false">
                    <i class="fas fa-filter"></i>
                    <span class="d-none d-md-inline ms-1">Filtros</span>
                </button>
                @endif
                
                <button type="submit" class="btn btn-primary search-submit">
                    <i class="fas fa-search d-md-none"></i>
                    <span class="d-none d-md-inline">Buscar</span>
                </button>
            </div>
            
            <!-- Suggestions Dropdown -->
            <div class="search-suggestions" id="suggestions-{{ $id }}" style="display: none;">
                <div class="suggestions-content">
                    <!-- Suggestions will be populated via JavaScript -->
                </div>
            </div>
        </div>
        
        @if($showFilters)
        <!-- Advanced Filters -->
        <div class="collapse mt-3" id="filters-{{ $id }}">
            <div class="card card-body bg-light">
                <div class="row g-3">
                    <!-- Category Filter -->
                    @if($categories->count() > 0)
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Categoria</label>
                        <select name="categoria" class="form-select form-select-sm">
                            <option value="">Todas as categorias</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->slug ?? $category->nome }}" 
                                        {{ request('categoria') == ($category->slug ?? $category->nome) ? 'selected' : '' }}>
                                    {{ $category->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <!-- Price Range -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Faixa de Preço</label>
                        <div class="row g-1">
                            <div class="col-6">
                                <input type="number" 
                                       name="preco_min" 
                                       class="form-control form-control-sm" 
                                       placeholder="Min"
                                       value="{{ request('preco_min') }}"
                                       min="0" 
                                       step="0.01">
                            </div>
                            <div class="col-6">
                                <input type="number" 
                                       name="preco_max" 
                                       class="form-control form-control-sm" 
                                       placeholder="Max"
                                       value="{{ request('preco_max') }}"
                                       min="0" 
                                       step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sort -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Ordenar por</label>
                        <select name="ordem" class="form-select form-select-sm">
                            <option value="relevancia" {{ request('ordem') == 'relevancia' ? 'selected' : '' }}>Relevância</option>
                            <option value="titulo" {{ request('ordem') == 'titulo' ? 'selected' : '' }}>Título (A-Z)</option>
                            <option value="preco" {{ request('ordem') == 'preco' ? 'selected' : '' }}>Menor Preço</option>
                            <option value="preco_desc" {{ request('ordem') == 'preco_desc' ? 'selected' : '' }}>Maior Preço</option>
                            <option value="created_at" {{ request('ordem') == 'created_at' ? 'selected' : '' }}>Mais Recentes</option>
                        </select>
                    </div>
                    
                    <!-- Availability -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Disponibilidade</label>
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="disponivel" 
                                   value="1" 
                                   id="disponivel-{{ $id }}"
                                   {{ request('disponivel') ? 'checked' : '' }}>
                            <label class="form-check-label small" for="disponivel-{{ $id }}">
                                Apenas em estoque
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Actions -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" 
                            class="btn btn-outline-secondary btn-sm clear-filters"
                            data-form-id="form-{{ $id }}">
                        <i class="fas fa-times me-1"></i>Limpar Filtros
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search me-1"></i>Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>
        @endif
    </form>
</div>

@push('styles')
<style>
.search-bar-container {
    position: relative;
    width: 100%;
}

.search-input-group {
    border-radius: 50px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.search-input-group:focus-within {
    box-shadow: 0 8px 25px rgba(0,123,255,0.3);
    transform: translateY(-2px);
}

.search-input-group .input-group-text {
    background: white;
    border: none;
    color: #6c757d;
}

.search-input {
    border: none;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    background: white;
}

.search-input:focus {
    box-shadow: none;
    border: none;
    background: white;
}

.search-submit {
    border: none;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.search-submit:hover {
    background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
    transform: scale(1.05);
}

.filters-toggle {
    border-left: none;
    border-right: none;
    background: white;
    color: #6c757d;
    transition: all 0.3s ease;
}

.filters-toggle:hover {
    background: #f8f9fa;
    color: #495057;
}

.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
    margin-top: 5px;
}

.suggestion-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f8f9fa;
    cursor: pointer;
    transition: background 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.suggestion-item:hover {
    background: #f8f9fa;
}

.suggestion-item:last-child {
    border-bottom: none;
}

.suggestion-icon {
    color: #6c757d;
    width: 16px;
    text-align: center;
}

.suggestion-text {
    flex: 1;
}

.suggestion-category {
    font-size: 0.8rem;
    color: #6c757d;
    background: #e9ecef;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
}

.clear-filters:hover {
    background: #dc3545;
    border-color: #dc3545;
    color: white;
}

/* Loading state */
.search-loading {
    opacity: 0.7;
    pointer-events: none;
}

.search-loading .search-submit {
    background: #6c757d;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .search-input-group {
        border-radius: 25px;
    }
    
    .search-input {
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
    }
    
    .search-submit {
        padding: 0.6rem 1rem;
    }
    
    .filters-toggle {
        padding: 0.6rem 0.75rem;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .search-input-group .input-group-text,
    .search-input {
        background: #343a40;
        border-color: #495057;
        color: #fff;
    }
    
    .search-suggestions {
        background: #343a40;
        border-color: #495057;
    }
    
    .suggestion-item {
        border-color: #495057;
        color: #fff;
    }
    
    .suggestion-item:hover {
        background: #495057;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchId = '{{ $id }}';
    const searchInput = document.querySelector(`[data-search-input="${searchId}"]`);
    const suggestionsContainer = document.getElementById(`suggestions-${searchId}`);
    const form = document.getElementById(`form-${searchId}`);
    const clearFiltersBtn = document.querySelector(`[data-form-id="form-${searchId}"]`);
    
    let searchTimeout;
    let isSearching = false;
    
    // Auto-complete functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    fetchSuggestions(query);
                }, 300);
            } else {
                hideSuggestions();
            }
        });
        
        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                hideSuggestions();
            }
        });
        
        // Handle keyboard navigation
        searchInput.addEventListener('keydown', function(e) {
            const suggestions = suggestionsContainer.querySelectorAll('.suggestion-item');
            const activeSuggestion = suggestionsContainer.querySelector('.suggestion-item.active');
            let activeIndex = Array.from(suggestions).indexOf(activeSuggestion);
            
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    activeIndex = activeIndex < suggestions.length - 1 ? activeIndex + 1 : 0;
                    updateActiveSuggestion(suggestions, activeIndex);
                    break;
                    
                case 'ArrowUp':
                    e.preventDefault();
                    activeIndex = activeIndex > 0 ? activeIndex - 1 : suggestions.length - 1;
                    updateActiveSuggestion(suggestions, activeIndex);
                    break;
                    
                case 'Enter':
                    if (activeSuggestion) {
                        e.preventDefault();
                        activeSuggestion.click();
                    }
                    break;
                    
                case 'Escape':
                    hideSuggestions();
                    searchInput.blur();
                    break;
            }
        });
    }
    
    // Form submission handling
    if (form) {
        form.addEventListener('submit', function(e) {
            if (isSearching) {
                e.preventDefault();
                return;
            }
            
            setLoadingState(true);
        });
    }
    
    // Clear filters functionality
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            const form = document.getElementById(this.dataset.formId);
            const inputs = form.querySelectorAll('input[type="text"], input[type="number"], select');
            const checkboxes = form.querySelectorAll('input[type="checkbox"]');
            
            inputs.forEach(input => {
                if (input.name !== 'q') { // Keep search query
                    input.value = '';
                }
            });
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Update URL without filters
            const url = new URL(window.location);
            url.searchParams.delete('categoria');
            url.searchParams.delete('preco_min');
            url.searchParams.delete('preco_max');
            url.searchParams.delete('ordem');
            url.searchParams.delete('disponivel');
            
            window.location.href = url.toString();
        });
    }
    
    async function fetchSuggestions(query) {
        if (isSearching) return;
        
        isSearching = true;
        showSuggestionsLoading();
        
        try {
            const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                displaySuggestions(data);
            } else {
                hideSuggestions();
            }
        } catch (error) {
            console.error('Error fetching suggestions:', error);
            hideSuggestions();
        } finally {
            isSearching = false;
        }
    }
    
    function displaySuggestions(data) {
        const suggestionsContent = suggestionsContainer.querySelector('.suggestions-content');
        
        if (!data.suggestions || data.suggestions.length === 0) {
            hideSuggestions();
            return;
        }
        
        const html = data.suggestions.map(suggestion => `
            <div class="suggestion-item" data-type="${suggestion.type}" data-value="${suggestion.value}">
                <i class="suggestion-icon ${getSuggestionIcon(suggestion.type)}"></i>
                <span class="suggestion-text">${highlightQuery(suggestion.text, data.query)}</span>
                ${suggestion.category ? `<span class="suggestion-category">${suggestion.category}</span>` : ''}
            </div>
        `).join('');
        
        suggestionsContent.innerHTML = html;
        
        // Add click handlers to suggestions
        suggestionsContent.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', function() {
                selectSuggestion(this.dataset.value, this.dataset.type);
            });
        });
        
        showSuggestions();
    }
    
    function getSuggestionIcon(type) {
        const icons = {
            'livro': 'fas fa-book',
            'autor': 'fas fa-user-edit',
            'categoria': 'fas fa-tag',
            'editora': 'fas fa-building'
        };
        return icons[type] || 'fas fa-search';
    }
    
    function highlightQuery(text, query) {
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }
    
    function selectSuggestion(value, type) {
        searchInput.value = value;
        hideSuggestions();
        
        // Auto-submit form or add additional logic based on type
        if (type === 'categoria') {
            const categoriaSelect = form.querySelector('select[name="categoria"]');
            if (categoriaSelect) {
                categoriaSelect.value = value;
            }
        }
        
        form.submit();
    }
    
    function updateActiveSuggestion(suggestions, activeIndex) {
        suggestions.forEach((suggestion, index) => {
            suggestion.classList.toggle('active', index === activeIndex);
        });
    }
    
    function showSuggestions() {
        suggestionsContainer.style.display = 'block';
    }
    
    function hideSuggestions() {
        suggestionsContainer.style.display = 'none';
    }
    
    function showSuggestionsLoading() {
        const suggestionsContent = suggestionsContainer.querySelector('.suggestions-content');
        suggestionsContent.innerHTML = `
            <div class="suggestion-item">
                <i class="suggestion-icon fas fa-spinner fa-spin"></i>
                <span class="suggestion-text">Buscando...</span>
            </div>
        `;
        showSuggestions();
    }
    
    function setLoadingState(loading) {
        const container = document.querySelector(`[data-search-id="${searchId}"]`);
        const submitBtn = container.querySelector('.search-submit');
        
        if (loading) {
            container.classList.add('search-loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        } else {
            container.classList.remove('search-loading');
            submitBtn.innerHTML = '<i class="fas fa-search d-md-none"></i><span class="d-none d-md-inline">Buscar</span>';
        }
    }
});
</script>
@endpush