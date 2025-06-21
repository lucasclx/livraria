{{-- resources/views/components/rating-stars.blade.php --}}
@props([
    'rating' => 0,
    'maxRating' => 5,
    'readonly' => true,
    'size' => 'md', // xs, sm, md, lg, xl
    'showValue' => false,
    'showCount' => false,
    'totalReviews' => 0,
    'precision' => 0.5, // 0.1, 0.5, 1
    'color' => 'warning', // warning, primary, success, danger, secondary
    'emptyColor' => 'muted',
    'name' => null, // for form input
    'id' => null,
    'animated' => false,
    'interactive' => false, // makes it clickable
    'showTooltip' => false,
    'labels' => [], // custom labels for each star
])

@php
    $componentId = $id ?? 'rating-' . uniqid();
    $inputName = $name ?? 'rating';
    $normalizedRating = max(0, min($rating, $maxRating));
    
    $sizeClasses = [
        'xs' => 'rating-xs',
        'sm' => 'rating-sm',
        'md' => 'rating-md', 
        'lg' => 'rating-lg',
        'xl' => 'rating-xl'
    ];
    
    $colorClasses = [
        'warning' => 'text-warning',
        'primary' => 'text-primary',
        'success' => 'text-success', 
        'danger' => 'text-danger',
        'secondary' => 'text-secondary'
    ];
    
    $defaultLabels = [
        1 => 'Muito ruim',
        2 => 'Ruim', 
        3 => 'Regular',
        4 => 'Bom',
        5 => 'Excelente'
    ];
    
    $starLabels = !empty($labels) ? $labels : $defaultLabels;
@endphp

<div class="rating-stars {{ $sizeClasses[$size] }} {{ $animated ? 'rating-animated' : '' }} {{ $interactive ? 'rating-interactive' : '' }}"
     data-rating="{{ $normalizedRating }}"
     data-max-rating="{{ $maxRating }}"
     data-precision="{{ $precision }}"
     data-readonly="{{ $readonly ? 'true' : 'false' }}"
     id="{{ $componentId }}">

    <div class="stars-container" role="img" aria-label="Avaliação: {{ $normalizedRating }} de {{ $maxRating }} estrelas">
        @for($i = 1; $i <= $maxRating; $i++)
            @php
                $starValue = $i;
                $fillPercentage = 0;
                
                if ($normalizedRating >= $i) {
                    $fillPercentage = 100;
                } elseif ($normalizedRating > $i - 1) {
                    $fillPercentage = ($normalizedRating - ($i - 1)) * 100;
                }
                
                $isActive = $fillPercentage > 0;
                $isHalf = $fillPercentage > 0 && $fillPercentage < 100;
            @endphp
            
            <div class="star-wrapper" 
                 data-star="{{ $i }}"
                 data-value="{{ $starValue }}"
                 @if($interactive && !$readonly)
                     role="button"
                     tabindex="0"
                     aria-label="Avaliar com {{ $i }} estrela{{ $i > 1 ? 's' : '' }}: {{ $starLabels[$i] ?? '' }}"
                 @endif
                 @if($showTooltip && isset($starLabels[$i]))
                     title="{{ $starLabels[$i] }}"
                     data-bs-toggle="tooltip"
                 @endif>
                 
                <div class="star {{ $isActive ? $colorClasses[$color] : 'text-' . $emptyColor }}">
                    @if($isHalf)
                        <div class="star-fill" style="width: {{ $fillPercentage }}%">
                            <i class="fas fa-star star-filled {{ $colorClasses[$color] }}"></i>
                        </div>
                        <i class="fas fa-star star-empty text-{{ $emptyColor }}"></i>
                    @else
                        <i class="fas fa-star"></i>
                    @endif
                </div>
                
                @if($interactive && !$readonly)
                    <div class="star-hover-effect"></div>
                @endif
            </div>
        @endfor
        
        {{-- Hidden input for form submission --}}
        @if(!$readonly && $name)
            <input type="hidden" 
                   name="{{ $inputName }}" 
                   value="{{ $normalizedRating }}"
                   data-rating-input>
        @endif
    </div>
    
    {{-- Rating information --}}
    @if($showValue || $showCount || $totalReviews > 0)
        <div class="rating-info">
            @if($showValue)
                <span class="rating-value">
                    {{ number_format($normalizedRating, 1) }}
                </span>
            @endif
            
            @if($showCount && $totalReviews > 0)
                <span class="rating-count">
                    ({{ number_format($totalReviews) }} {{ $totalReviews == 1 ? 'avaliação' : 'avaliações' }})
                </span>
            @endif
        </div>
    @endif
    
    {{-- Rating breakdown (optional) --}}
    @if($interactive && !$readonly)
        <div class="rating-feedback" style="display: none;">
            <small class="feedback-text text-muted"></small>
        </div>
    @endif
</div>

@push('styles')
<style>
/* Base Rating Styles */
.rating-stars {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    user-select: none;
}

.stars-container {
    display: flex;
    align-items: center;
    gap: 0.125rem;
}

.star-wrapper {
    position: relative;
    display: inline-block;
    cursor: default;
    transition: transform 0.2s ease;
}

.star {
    position: relative;
    display: inline-block;
    transition: all 0.2s ease;
}

.star-fill {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    overflow: hidden;
    z-index: 2;
}

.star-filled {
    position: relative;
    z-index: 1;
}

.star-empty {
    position: relative;
    z-index: 0;
}

/* Size Variations */
.rating-xs {
    --star-size: 0.75rem;
    --star-gap: 0.0625rem;
    --info-font-size: 0.75rem;
}

.rating-sm {
    --star-size: 1rem;
    --star-gap: 0.125rem;
    --info-font-size: 0.875rem;
}

.rating-md {
    --star-size: 1.25rem;
    --star-gap: 0.1875rem;
    --info-font-size: 1rem;
}

.rating-lg {
    --star-size: 1.5rem;
    --star-gap: 0.25rem;
    --info-font-size: 1.125rem;
}

.rating-xl {
    --star-size: 2rem;
    --star-gap: 0.375rem;
    --info-font-size: 1.25rem;
}

.rating-stars .stars-container {
    gap: var(--star-gap);
}

.rating-stars .star i {
    font-size: var(--star-size);
}

.rating-info {
    margin-left: 0.5rem;
    font-size: var(--info-font-size);
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Interactive States */
.rating-interactive .star-wrapper {
    cursor: pointer;
    position: relative;
}

.rating-interactive .star-wrapper:hover {
    transform: scale(1.1);
}

.rating-interactive .star-wrapper:active {
    transform: scale(0.95);
}

.rating-interactive .star-wrapper::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    right: -50%;
    bottom: -50%;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.1);
    transform: scale(0);
    transition: transform 0.2s ease;
    z-index: -1;
}

.rating-interactive .star-wrapper:hover::after {
    transform: scale(1);
}

/* Hover Effects for Interactive Rating */
.rating-interactive .star-wrapper:hover ~ .star-wrapper .star {
    opacity: 0.3;
}

.rating-interactive .star-wrapper:hover .star,
.rating-interactive .star-wrapper:hover ~ .star-wrapper.hovered .star {
    opacity: 1;
    color: var(--bs-warning) !important;
}

/* Animation */
.rating-animated .star-wrapper {
    animation: starAppear 0.3s ease-out forwards;
    animation-delay: calc(var(--star-index, 0) * 0.1s);
    opacity: 0;
    transform: scale(0);
}

@keyframes starAppear {
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.rating-animated .star:hover {
    animation: starPulse 0.4s ease-in-out;
}

@keyframes starPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
}

/* Focus States for Accessibility */
.rating-interactive .star-wrapper:focus {
    outline: 2px solid var(--bs-primary);
    outline-offset: 2px;
    border-radius: 2px;
}

.rating-interactive .star-wrapper:focus-visible {
    outline: 2px solid var(--bs-primary);
    outline-offset: 2px;
}

/* Rating Value and Count */
.rating-value {
    font-weight: 600;
    color: var(--bs-dark);
}

.rating-count {
    color: var(--bs-secondary);
    font-size: 0.9em;
}

.rating-count:hover {
    color: var(--bs-primary);
    text-decoration: underline;
    cursor: pointer;
}

/* Feedback Text */
.rating-feedback {
    margin-top: 0.25rem;
}

.feedback-text {
    font-style: italic;
    transition: opacity 0.2s ease;
}

/* Loading State */
.rating-loading .star {
    opacity: 0.3;
    animation: starShimmer 1.5s infinite;
}

@keyframes starShimmer {
    0%, 100% {
        opacity: 0.3;
    }
    50% {
        opacity: 0.8;
    }
}

/* Error State */
.rating-error .star {
    color: var(--bs-danger) !important;
}

/* Success State */
.rating-success .star {
    color: var(--bs-success) !important;
    animation: starSuccess 0.5s ease-out;
}

@keyframes starSuccess {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .rating-value {
        color: var(--bs-light);
    }
    
    .rating-interactive .star-wrapper::after {
        background: rgba(255, 255, 255, 0.1);
    }
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .star {
        filter: contrast(2);
    }
    
    .rating-interactive .star-wrapper:focus {
        outline: 3px solid;
    }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    .rating-animated .star-wrapper {
        animation: none;
        opacity: 1;
        transform: scale(1);
    }
    
    .star,
    .star-wrapper,
    .star-wrapper::after {
        transition: none;
    }
}

/* Print Styles */
@media print {
    .rating-stars {
        color: #000 !important;
    }
    
    .rating-interactive .star-wrapper::after {
        display: none;
    }
}

/* Mobile Optimizations */
@media (max-width: 576px) {
    .rating-interactive .star-wrapper {
        padding: 0.25rem;
        margin: -0.25rem;
    }
    
    .rating-lg {
        --star-size: 1.25rem;
    }
    
    .rating-xl {
        --star-size: 1.5rem;
    }
}

/* RTL Support */
[dir="rtl"] .rating-info {
    margin-left: 0;
    margin-right: 0.5rem;
}

[dir="rtl"] .stars-container {
    flex-direction: row-reverse;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ratingComponents = document.querySelectorAll('.rating-stars');
    
    ratingComponents.forEach(component => {
        initializeRating(component);
    });
    
    function initializeRating(component) {
        const isReadonly = component.dataset.readonly === 'true';
        const isInteractive = component.classList.contains('rating-interactive');
        const precision = parseFloat(component.dataset.precision) || 1;
        const maxRating = parseInt(component.dataset.maxRating) || 5;
        const currentRating = parseFloat(component.dataset.rating) || 0;
        
        const starWrappers = component.querySelectorAll('.star-wrapper');
        const hiddenInput = component.querySelector('[data-rating-input]');
        const feedbackElement = component.querySelector('.feedback-text');
        
        let selectedRating = currentRating;
        let hoveredRating = 0;
        
        // Feedback messages
        const feedbackMessages = {
            1: 'Muito insatisfeito com o produto',
            2: 'Insatisfeito, mas teve alguns pontos positivos',
            3: 'Neutro, atendeu as expectativas básicas',
            4: 'Satisfeito, recomendo o produto',
            5: 'Muito satisfeito, excelente produto!'
        };
        
        if (isInteractive && !isReadonly) {
            setupInteractiveRating();
        }
        
        // Add animation delays for animated ratings
        if (component.classList.contains('rating-animated')) {
            starWrappers.forEach((wrapper, index) => {
                wrapper.style.setProperty('--star-index', index);
            });
        }
        
        function setupInteractiveRating() {
            starWrappers.forEach((wrapper, index) => {
                const starValue = index + 1;
                
                // Mouse events
                wrapper.addEventListener('mouseenter', () => handleStarHover(starValue));
                wrapper.addEventListener('mouseleave', () => handleStarLeave());
                wrapper.addEventListener('click', () => handleStarClick(starValue));
                
                // Keyboard events
                wrapper.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        handleStarClick(starValue);
                    } else if (e.key === 'ArrowRight' || e.key === 'ArrowUp') {
                        e.preventDefault();
                        const nextStar = wrapper.nextElementSibling;
                        if (nextStar) nextStar.focus();
                    } else if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') {
                        e.preventDefault();
                        const prevStar = wrapper.previousElementSibling;
                        if (prevStar) prevStar.focus();
                    }
                });
                
                // Touch events for mobile
                wrapper.addEventListener('touchend', (e) => {
                    e.preventDefault();
                    handleStarClick(starValue);
                });
            });
            
            // Container events
            component.addEventListener('mouseleave', () => {
                hoveredRating = 0;
                updateStarDisplay(selectedRating);
                hideFeedback();
            });
        }
        
        function handleStarHover(rating) {
            hoveredRating = rating;
            updateStarDisplay(rating);
            showFeedback(rating);
        }
        
        function handleStarLeave() {
            if (hoveredRating === 0) {
                updateStarDisplay(selectedRating);
                hideFeedback();
            }
        }
        
        function handleStarClick(rating) {
            selectedRating = rating;
            component.dataset.rating = rating;
            
            if (hiddenInput) {
                hiddenInput.value = rating;
            }
            
            updateStarDisplay(rating);
            showFeedback(rating);
            
            // Add success animation
            component.classList.add('rating-success');
            setTimeout(() => {
                component.classList.remove('rating-success');
            }, 500);
            
            // Trigger custom event
            component.dispatchEvent(new CustomEvent('ratingChanged', {
                detail: { rating, maxRating }
            }));
            
            // Haptic feedback on mobile
            if ('vibrate' in navigator) {
                navigator.vibrate(50);
            }
        }
        
        function updateStarDisplay(rating) {
            starWrappers.forEach((wrapper, index) => {
                const starValue = index + 1;
                const star = wrapper.querySelector('.star');
                const starIcon = star.querySelector('i');
                
                if (rating >= starValue) {
                    // Full star
                    starIcon.className = 'fas fa-star';
                    star.className = 'star text-warning';
                } else if (rating > starValue - 1 && precision < 1) {
                    // Partial star (only for decimal precision)
                    const fillPercentage = ((rating - (starValue - 1)) * 100);
                    updatePartialStar(star, fillPercentage);
                } else {
                    // Empty star
                    starIcon.className = 'far fa-star';
                    star.className = 'star text-muted';
                }
            });
        }
        
        function updatePartialStar(star, fillPercentage) {
            const existingFill = star.querySelector('.star-fill');
            if (existingFill) {
                existingFill.style.width = fillPercentage + '%';
            } else {
                star.innerHTML = `
                    <div class="star-fill" style="width: ${fillPercentage}%">
                        <i class="fas fa-star text-warning"></i>
                    </div>
                    <i class="far fa-star text-muted"></i>
                `;
            }
        }
        
        function showFeedback(rating) {
            if (feedbackElement && feedbackMessages[rating]) {
                feedbackElement.textContent = feedbackMessages[rating];
                feedbackElement.parentElement.style.display = 'block';
                feedbackElement.style.opacity = '1';
            }
        }
        
        function hideFeedback() {
            if (feedbackElement) {
                feedbackElement.style.opacity = '0';
                setTimeout(() => {
                    feedbackElement.parentElement.style.display = 'none';
                }, 200);
            }
        }
        
        // Public methods
        component.setRating = function(rating) {
            selectedRating = Math.max(0, Math.min(rating, maxRating));
            this.dataset.rating = selectedRating;
            if (hiddenInput) {
                hiddenInput.value = selectedRating;
            }
            updateStarDisplay(selectedRating);
        };
        
        component.getRating = function() {
            return selectedRating;
        };
        
        component.setReadonly = function(readonly) {
            this.dataset.readonly = readonly;
            if (readonly) {
                this.classList.remove('rating-interactive');
            } else {
                this.classList.add('rating-interactive');
                setupInteractiveRating();
            }
        };
        
        component.setLoading = function(loading) {
            if (loading) {
                this.classList.add('rating-loading');
            } else {
                this.classList.remove('rating-loading');
            }
        };
        
        // Initialize tooltips if enabled
        const tooltipElements = component.querySelectorAll('[data-bs-toggle="tooltip"]');
        if (tooltipElements.length > 0 && typeof bootstrap !== 'undefined') {
            tooltipElements.forEach(element => {
                new bootstrap.Tooltip(element);
            });
        }
    }
    
    // Global rating utilities
    window.RatingStars = {
        create: function(containerId, options = {}) {
            const container = document.getElementById(containerId);
            if (!container) return null;
            
            const defaults = {
                rating: 0,
                maxRating: 5,
                readonly: false,
                size: 'md',
                color: 'warning',
                animated: false,
                interactive: true
            };
            
            const config = { ...defaults, ...options };
            
            container.className = `rating-stars rating-${config.size} ${config.animated ? 'rating-animated' : ''} ${config.interactive ? 'rating-interactive' : ''}`;
            container.dataset.rating = config.rating;
            container.dataset.maxRating = config.maxRating;
            container.dataset.readonly = config.readonly;
            
            // Generate stars HTML
            let starsHTML = '<div class="stars-container">';
            for (let i = 1; i <= config.maxRating; i++) {
                starsHTML += `
                    <div class="star-wrapper" data-star="${i}" data-value="${i}" role="button" tabindex="0">
                        <div class="star text-muted">
                            <i class="far fa-star"></i>
                        </div>
                    </div>
                `;
            }
            starsHTML += '</div>';
            
            container.innerHTML = starsHTML;
            
            initializeRating(container);
            return container;
        }
    };
});
</script>
@endpush