{{-- resources/views/components/price-display.blade.php --}}
@props([
    'price' => 0,
    'originalPrice' => null,
    'currency' => 'BRL',
    'size' => 'md', // xs, sm, md, lg, xl
    'showCurrency' => true,
    'showDiscount' => true,
    'discountType' => 'percentage', // percentage, amount
    'theme' => 'default', // default, success, primary, gradient
    'alignment' => 'left', // left, center, right
    'animate' => false,
    'showSavings' => true,
    'installments' => null // array: ['count' => 12, 'value' => 10.50]
])

@php
    $hasDiscount = $originalPrice && $originalPrice > $price;
    $discountAmount = $hasDiscount ? $originalPrice - $price : 0;
    $discountPercentage = $hasDiscount ? round(($discountAmount / $originalPrice) * 100) : 0;
    
    $sizeClasses = [
        'xs' => 'price-xs',
        'sm' => 'price-sm', 
        'md' => 'price-md',
        'lg' => 'price-lg',
        'xl' => 'price-xl'
    ];
    
    $themeClasses = [
        'default' => 'price-default',
        'success' => 'price-success',
        'primary' => 'price-primary', 
        'gradient' => 'price-gradient'
    ];
    
    $alignmentClasses = [
        'left' => 'text-start',
        'center' => 'text-center',
        'right' => 'text-end'
    ];
@endphp

<div class="price-display {{ $sizeClasses[$size] }} {{ $themeClasses[$theme] }} {{ $alignmentClasses[$alignment] }} {{ $animate ? 'price-animate' : '' }}"
     data-price="{{ $price }}"
     data-original-price="{{ $originalPrice }}"
     data-currency="{{ $currency }}">
     
    {{-- Current Price --}}
    <div class="price-current-wrapper">
        @if($hasDiscount)
            <div class="price-current price-discounted">
        @else
            <div class="price-current">
        @endif
            @if($showCurrency)
                <span class="price-currency">R$</span>
            @endif
            <span class="price-value" data-price-value="{{ $price }}">
                {{ number_format($price, 2, ',', '.') }}
            </span>
        </div>
    </div>
    
    {{-- Original Price & Discount --}}
    @if($hasDiscount)
        <div class="price-discount-info">
            <div class="price-original">
                @if($showCurrency)
                    <span class="price-currency-small">R$</span>
                @endif
                <span class="price-value-original">
                    {{ number_format($originalPrice, 2, ',', '.') }}
                </span>
            </div>
            
            @if($showDiscount)
                <div class="price-discount-badge">
                    @if($discountType === 'percentage')
                        <span class="discount-percentage">
                            -{{ $discountPercentage }}%
                        </span>
                    @else
                        <span class="discount-amount">
                            -R$ {{ number_format($discountAmount, 2, ',', '.') }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
        
        @if($showSavings)
            <div class="price-savings">
                <small class="savings-text">
                    <i class="fas fa-tag me-1"></i>
                    Você economiza R$ {{ number_format($discountAmount, 2, ',', '.') }}
                </small>
            </div>
        @endif
    @endif
    
    {{-- Installments --}}
    @if($installments && isset($installments['count']) && isset($installments['value']))
        <div class="price-installments">
            <small class="installments-text">
                <i class="fas fa-credit-card me-1"></i>
                ou {{ $installments['count'] }}x de 
                <strong>R$ {{ number_format($installments['value'], 2, ',', '.') }}</strong>
                @if(isset($installments['interest']) && !$installments['interest'])
                    <span class="no-interest">sem juros</span>
                @endif
            </small>
        </div>
    @endif
    
    {{-- PIX Discount (Brazilian market feature) --}}
    @if($hasDiscount || $price >= 50)
        @php
            $pixDiscount = 0.05; // 5% discount
            $pixPrice = $price * (1 - $pixDiscount);
        @endphp
        <div class="price-pix">
            <small class="pix-text">
                <i class="fas fa-mobile-alt me-1 text-success"></i>
                <strong class="text-success">R$ {{ number_format($pixPrice, 2, ',', '.') }}</strong>
                no PIX (5% desconto)
            </small>
        </div>
    @endif
</div>

@push('styles')
<style>
/* Base Price Display Styles */
.price-display {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.2;
}

/* Size Variations */
.price-xs {
    --price-font-size: 0.875rem;
    --price-currency-size: 0.75rem;
    --price-original-size: 0.75rem;
    --price-badge-size: 0.625rem;
    --price-spacing: 0.25rem;
}

.price-sm {
    --price-font-size: 1rem;
    --price-currency-size: 0.875rem;
    --price-original-size: 0.875rem;
    --price-badge-size: 0.75rem;
    --price-spacing: 0.375rem;
}

.price-md {
    --price-font-size: 1.25rem;
    --price-currency-size: 1rem;
    --price-original-size: 1rem;
    --price-badge-size: 0.875rem;
    --price-spacing: 0.5rem;
}

.price-lg {
    --price-font-size: 1.5rem;
    --price-currency-size: 1.125rem;
    --price-original-size: 1.125rem;
    --price-badge-size: 1rem;
    --price-spacing: 0.625rem;
}

.price-xl {
    --price-font-size: 2rem;
    --price-currency-size: 1.5rem;
    --price-original-size: 1.25rem;
    --price-badge-size: 1.125rem;
    --price-spacing: 0.75rem;
}

/* Theme Variations */
.price-default {
    --price-color: #28a745;
    --price-currency-color: #6c757d;
    --price-original-color: #6c757d;
    --price-discount-bg: #dc3545;
    --price-discount-color: #fff;
}

.price-success {
    --price-color: #20c997;
    --price-currency-color: #17a2b8;
    --price-original-color: #6c757d;
    --price-discount-bg: #fd7e14;
    --price-discount-color: #fff;
}

.price-primary {
    --price-color: #007bff;
    --price-currency-color: #495057;
    --price-original-color: #6c757d;
    --price-discount-bg: #dc3545;
    --price-discount-color: #fff;
}

.price-gradient {
    --price-color: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --price-currency-color: #6c757d;
    --price-original-color: #6c757d;
    --price-discount-bg: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    --price-discount-color: #fff;
}

/* Current Price Styles */
.price-current {
    display: flex;
    align-items: baseline;
    gap: 0.25rem;
    margin-bottom: var(--price-spacing);
}

.price-currency {
    font-size: var(--price-currency-size);
    color: var(--price-currency-color);
    font-weight: 500;
}

.price-value {
    font-size: var(--price-font-size);
    font-weight: 700;
    color: var(--price-color);
}

.price-gradient .price-value {
    background: var(--price-color);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.price-discounted .price-value {
    font-weight: 800;
}

/* Discount Information */
.price-discount-info {
    display: flex;
    align-items: center;
    gap: var(--price-spacing);
    margin-bottom: calc(var(--price-spacing) * 0.75);
    flex-wrap: wrap;
}

.price-original {
    display: flex;
    align-items: baseline;
    gap: 0.125rem;
}

.price-currency-small {
    font-size: calc(var(--price-original-size) * 0.8);
    color: var(--price-original-color);
}

.price-value-original {
    font-size: var(--price-original-size);
    color: var(--price-original-color);
    text-decoration: line-through;
    font-weight: 400;
}

.price-discount-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.5rem;
    background: var(--price-discount-bg);
    color: var(--price-discount-color);
    border-radius: 4px;
    font-size: var(--price-badge-size);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.price-gradient .price-discount-badge {
    background: var(--price-discount-bg);
}

/* Savings Text */
.price-savings {
    margin-bottom: calc(var(--price-spacing) * 0.5);
}

.savings-text {
    color: #28a745;
    font-weight: 500;
    font-size: calc(var(--price-font-size) * 0.75);
}

/* Installments */
.price-installments {
    margin-bottom: calc(var(--price-spacing) * 0.5);
}

.installments-text {
    color: #495057;
    font-size: calc(var(--price-font-size) * 0.75);
}

.installments-text strong {
    color: #007bff;
}

.no-interest {
    color: #28a745;
    font-weight: 500;
}

/* PIX Payment */
.price-pix {
    padding: 0.5rem;
    background: rgba(40, 167, 69, 0.1);
    border: 1px solid rgba(40, 167, 69, 0.2);
    border-radius: 6px;
    margin-top: calc(var(--price-spacing) * 0.5);
}

.pix-text {
    font-size: calc(var(--price-font-size) * 0.8);
    color: #495057;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Animation */
.price-animate {
    animation: priceReveal 0.6s ease-out;
}

@keyframes priceReveal {
    from {
        opacity: 0;
        transform: translateY(-10px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.price-animate .price-discount-badge {
    animation: badgePulse 0.8s ease-out 0.3s;
}

@keyframes badgePulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

/* Interactive States */
.price-display:hover .price-value {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}

.price-display:hover .price-discount-badge {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

/* Responsive Adjustments */
@media (max-width: 576px) {
    .price-discount-info {
        flex-direction: column;
        align-items: flex-start;
        gap: calc(var(--price-spacing) * 0.5);
    }
    
    .price-xl {
        --price-font-size: 1.5rem;
        --price-currency-size: 1.125rem;
    }
    
    .price-lg {
        --price-font-size: 1.25rem;
        --price-currency-size: 1rem;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .price-display {
        --price-color: #000;
        --price-original-color: #666;
        --price-discount-bg: #000;
        --price-discount-color: #fff;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .price-animate {
        animation: none;
    }
    
    .price-display:hover .price-value,
    .price-display:hover .price-discount-badge {
        transform: none;
    }
}

/* Print styles */
@media print {
    .price-display {
        color: #000 !important;
    }
    
    .price-discount-badge {
        background: #000 !important;
        color: #fff !important;
    }
    
    .price-pix {
        display: none;
    }
}

/* Currency specific adjustments */
.price-display[data-currency="USD"] .price-currency::before {
    content: "$";
}

.price-display[data-currency="EUR"] .price-currency::before {
    content: "€";
}

.price-display[data-currency="BRL"] .price-currency::before {
    content: "R$";
}

/* Loading state */
.price-loading {
    opacity: 0.6;
    pointer-events: none;
}

.price-loading .price-value {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
    color: transparent;
}

@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Price update functionality
    const priceDisplays = document.querySelectorAll('.price-display');
    
    priceDisplays.forEach(display => {
        // Add price comparison functionality
        const currentPrice = parseFloat(display.dataset.price);
        const originalPrice = parseFloat(display.dataset.originalPrice);
        
        // Create price update method
        display.updatePrice = function(newPrice, newOriginalPrice = null) {
            const priceValue = this.querySelector('.price-value');
            const originalPriceValue = this.querySelector('.price-value-original');
            const discountBadge = this.querySelector('.discount-percentage, .discount-amount');
            
            // Add loading state
            this.classList.add('price-loading');
            
            setTimeout(() => {
                // Update current price
                priceValue.textContent = formatPrice(newPrice);
                priceValue.dataset.priceValue = newPrice;
                this.dataset.price = newPrice;
                
                // Update original price if provided
                if (newOriginalPrice && originalPriceValue) {
                    originalPriceValue.textContent = formatPrice(newOriginalPrice);
                    this.dataset.originalPrice = newOriginalPrice;
                    
                    // Update discount badge
                    if (discountBadge) {
                        const discountAmount = newOriginalPrice - newPrice;
                        const discountPercentage = Math.round((discountAmount / newOriginalPrice) * 100);
                        
                        if (discountBadge.classList.contains('discount-percentage')) {
                            discountBadge.textContent = `-${discountPercentage}%`;
                        } else {
                            discountBadge.textContent = `-R$ ${formatPrice(discountAmount)}`;
                        }
                    }
                }
                
                // Remove loading state and add animation
                this.classList.remove('price-loading');
                if (!this.classList.contains('price-animate')) {
                    this.classList.add('price-animate');
                    
                    setTimeout(() => {
                        this.classList.remove('price-animate');
                    }, 600);
                }
                
                // Trigger custom event
                this.dispatchEvent(new CustomEvent('priceUpdated', {
                    detail: { newPrice, newOriginalPrice }
                }));
            }, 300);
        };
        
        // Add price animation on scroll (Intersection Observer)
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries