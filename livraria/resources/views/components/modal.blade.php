{{-- resources/views/components/modal.blade.php --}}
@props([
    'id' => null,
    'title' => '',
    'size' => 'md', // sm, md, lg, xl, fullscreen
    'centered' => false,
    'backdrop' => true, // true, false, 'static'
    'keyboard' => true,
    'focus' => true,
    'show' => false,
    'fade' => true,
    'scrollable' => false,
    'fullscreenResponsive' => null, // sm, md, lg, xl, xxl
    'headerClass' => '',
    'bodyClass' => '',
    'footerClass' => '',
    'closeButton' => true,
    'type' => 'default', // default, confirmation, form, image, video
    'icon' => null,
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
    'confirmClass' => 'btn-primary',
    'cancelClass' => 'btn-secondary',
    'onConfirm' => null,
    'onCancel' => null,
    'loading' => false,
    'persistent' => false, // prevents closing on backdrop click
])

@php
    $modalId = $id ?? 'modal-' . uniqid();
    
    $sizeClasses = [
        'sm' => 'modal-sm',
        'md' => '',
        'lg' => 'modal-lg', 
        'xl' => 'modal-xl',
        'fullscreen' => 'modal-fullscreen'
    ];
    
    $typeConfig = [
        'confirmation' => [
            'icon' => 'fas fa-question-circle text-warning',
            'headerClass' => 'bg-warning text-dark',
        ],
        'form' => [
            'scrollable' => true,
            'backdrop' => 'static',
        ],
        'image' => [
            'size' => 'lg',
            'centered' => true,
            'bodyClass' => 'p-0',
        ],
        'video' => [
            'size' => 'xl', 
            'centered' => true,
            'bodyClass' => 'p-0',
        ]
    ];
    
    if (isset($typeConfig[$type])) {
        $config = $typeConfig[$type];
        foreach ($config as $key => $value) {
            if (!isset($$key) || $$key === null) {
                $$key = $value;
            }
        }
    }
    
    $modalClasses = ['modal'];
    if ($fade) $modalClasses[] = 'fade';
    
    $dialogClasses = ['modal-dialog'];
    if ($sizeClasses[$size]) $dialogClasses[] = $sizeClasses[$size];
    if ($centered) $dialogClasses[] = 'modal-dialog-centered';
    if ($scrollable) $dialogClasses[] = 'modal-dialog-scrollable';
    if ($fullscreenResponsive) $dialogClasses[] = "modal-fullscreen-{$fullscreenResponsive}-down";
@endphp

<div class="{{ implode(' ', $modalClasses) }}"
     id="{{ $modalId }}"
     tabindex="-1"
     aria-labelledby="{{ $modalId }}-title"
     aria-hidden="true"
     data-bs-backdrop="{{ $backdrop === true ? 'true' : ($backdrop === false ? 'false' : $backdrop) }}"
     data-bs-keyboard="{{ $keyboard ? 'true' : 'false' }}"
     data-bs-focus="{{ $focus ? 'true' : 'false' }}"
     data-type="{{ $type }}"
     @if($persistent) data-persistent="true" @endif
     @if($onConfirm) data-on-confirm="{{ $onConfirm }}" @endif
     @if($onCancel) data-on-cancel="{{ $onCancel }}" @endif>

    <div class="{{ implode(' ', $dialogClasses) }}">
        <div class="modal-content {{ $loading ? 'modal-loading' : '' }}">
            
            {{-- Loading Overlay --}}
            @if($loading)
                <div class="modal-loading-overlay">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            @endif
            
            {{-- Header --}}
            @if($title || $closeButton || $icon)
                <div class="modal-header {{ $headerClass }}">
                    @if($icon)
                        <div class="modal-icon me-2">
                            <i class="{{ $icon }}"></i>
                        </div>
                    @endif
                    
                    @if($title)
                        <h5 class="modal-title" id="{{ $modalId }}-title">{{ $title }}</h5>
                    @endif
                    
                    @if($closeButton)
                        <button type="button" 
                                class="btn-close" 
                                data-bs-dismiss="modal" 
                                aria-label="Fechar"
                                @if($type === 'confirmation') data-action="cancel" @endif>
                        </button>
                    @endif
                </div>
            @endif
            
            {{-- Body --}}
            <div class="modal-body {{ $bodyClass }}">
                @if($type === 'confirmation' && !$slot->isEmpty())
                    <div class="confirmation-content">
                        @if($icon && !$title)
                            <div class="text-center mb-3">
                                <i class="{{ $icon }}" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                        <div class="confirmation-text">
                            {{ $slot }}
                        </div>
                    </div>
                @else
                    {{ $slot }}
                @endif
            </div>
            
            {{-- Footer --}}
            @if($type === 'confirmation' || !empty($footerClass) || isset($footer))
                <div class="modal-footer {{ $footerClass }}">
                    @if($type === 'confirmation')
                        <button type="button" 
                                class="btn {{ $cancelClass }}" 
                                data-bs-dismiss="modal"
                                data-action="cancel">
                            {{ $cancelText }}
                        </button>
                        <button type="button" 
                                class="btn {{ $confirmClass }}"
                                data-action="confirm">
                            {{ $confirmText }}
                        </button>
                    @endif
                    
                    @isset($footer)
                        {{ $footer }}
                    @endisset
                </div>
            @endif
        </div>
    </div>
</div>

@once
@push('styles')
<style>
/* Enhanced Modal Styles */
.modal-content {
    border: none;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.modal.show .modal-content {
    transform: scale(1);
    opacity: 1;
}

.modal-content.modal-loading {
    position: relative;
    overflow: hidden;
}

/* Loading Overlay */
.modal-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    backdrop-filter: blur(2px);
}

/* Header Enhancements */
.modal-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    padding: 1.5rem 1.5rem 1rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    position: relative;
}

.modal-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(0,0,0,0.1), transparent);
}

.modal-icon {
    font-size: 1.5rem;
}

.modal-title {
    font-weight: 600;
    font-size: 1.25rem;
    color: #2d3748;
    margin: 0;
    display: flex;
    align-items: center;
}

.btn-close {
    font-size: 1rem;
    opacity: 0.6;
    transition: all 0.2s ease;
}

.btn-close:hover {
    opacity: 1;
    transform: scale(1.1);
}

/* Body Enhancements */
.modal-body {
    padding: 1.5rem;
    color: #4a5568;
    line-height: 1.6;
}

.modal-body:first-child {
    padding-top: 2rem;
}

.modal-body:last-child {
    padding-bottom: 2rem;
}

/* Footer Enhancements */
.modal-footer {
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    padding: 1rem 1.5rem 1.5rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    gap: 0.75rem;
}

.modal-footer .btn {
    min-width: 100px;
    font-weight: 500;
}

/* Type-specific Styles */

/* Confirmation Modal */
.confirmation-content {
    text-align: center;
    padding: 1rem 0;
}

.confirmation-text {
    font-size: 1.1rem;
    color: #2d3748;
    margin-bottom: 0;
}

/* Form Modal */
.modal[data-type="form"] .modal-body {
    padding: 2rem 1.5rem;
}

.modal[data-type="form"] .form-group {
    margin-bottom: 1.5rem;
}

.modal[data-type="form"] .form-label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.modal[data-type="form"] .form-control {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
}

.modal[data-type="form"] .form-control:focus {
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
}

/* Image Modal */
.modal[data-type="image"] .modal-content {
    background: transparent;
    border: none;
    box-shadow: none;
}

.modal[data-type="image"] .modal-body {
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal[data-type="image"] .modal-body img {
    max-width: 100%;
    max-height: 80vh;
    border-radius: 8px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
}

/* Video Modal */
.modal[data-type="video"] .modal-content {
    background: #000;
}

.modal[data-type="video"] .modal-body {
    padding: 0;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal[data-type="video"] .modal-body video,
.modal[data-type="video"] .modal-body iframe {
    width: 100%;
    height: auto;
    max-height: 80vh;
}

/* Size Variations */
.modal-sm .modal-content {
    border-radius: 12px;
}

.modal-lg .modal-content,
.modal-xl .modal-content {
    border-radius: 20px;
}

.modal-fullscreen .modal-content {
    border-radius: 0;
    height: 100%;
}

/* Animation Enhancements */
.modal.fade .modal-dialog {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
    transform: scale(0.8) translateY(-50px);
    opacity: 0;
}

.modal.show .modal-dialog {
    transform: scale(1) translateY(0);
    opacity: 1;
}

/* Backdrop Enhancements */
.modal-backdrop {
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
}

.modal-backdrop.show {
    opacity: 1;
}

/* Persistent Modal */
.modal[data-persistent="true"] .modal-content {
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}

/* Loading States */
.modal-loading .modal-header,
.modal-loading .modal-body,
.modal-loading .modal-footer {
    opacity: 0.6;
    pointer-events: none;
}

/* Button Enhancements */
.modal-footer .btn-primary {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(66, 153, 225, 0.3);
}

.modal-footer .btn-primary:hover {
    background: linear-gradient(135deg, #3182ce 0%, #2c5282 100%);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(66, 153, 225, 0.4);
}

.modal-footer .btn-secondary {
    background: #e2e8f0;
    border: 1px solid #cbd5e0;
    color: #4a5568;
}

.modal-footer .btn-secondary:hover {
    background: #cbd5e0;
    border-color: #a0aec0;
    color: #2d3748;
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .modal-content {
        background: #2d3748;
        color: #e2e8f0;
    }
    
    .modal-header {
        background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }
    
    .modal-title {
        color: #f7fafc;
    }
    
    .modal-body {
        color: #cbd5e0;
    }
    
    .modal-footer {
        background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
        border-top-color: rgba(255, 255, 255, 0.1);
    }
    
    .confirmation-text {
        color: #e2e8f0;
    }
    
    .modal[data-type="form"] .form-control {
        background: #4a5568;
        border-color: #718096;
        color: #e2e8f0;
    }
    
    .modal[data-type="form"] .form-control:focus {
        border-color: #63b3ed;
        background: #2d3748;
    }
    
    .modal-loading-overlay {
        background: rgba(45, 55, 72, 0.9);
    }
}

/* Mobile Optimizations */
@media (max-width: 576px) {
    .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    .modal-content {
        border-radius: 12px;
    }
    
    .modal-header {
        padding: 1rem 1rem 0.75rem;
    }
    
    .modal-body {
        padding: 1rem;
    }
    
    .modal-footer {
        padding: 0.75rem 1rem 1rem;
        flex-direction: column-reverse;
    }
    
    .modal-footer .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .modal-footer .btn:last-child {
        margin-bottom: 0;
    }
    
    .modal-title {
        font-size: 1.1rem;
    }
    
    /* Fullscreen on mobile for large modals */
    .modal-lg,
    .modal-xl {
        .modal-dialog {
            margin: 0;
            max-width: 100%;
            height: 100%;
        }
        
        .modal-content {
            border-radius: 0;
            height: 100%;
        }
    }
}

/* Accessibility Enhancements */
.modal:focus {
    outline: none;
}

.modal-content:focus-within {
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2), 0 0 0 3px rgba(66, 153, 225, 0.3);
}

.btn-close:focus {
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.3);
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .modal-content {
        border: 2px solid;
    }
    
    .modal-header,
    .modal-footer {
        border-width: 2px;
    }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    .modal.fade .modal-dialog {
        transition: none;
    }
    
    .modal[data-persistent="true"] .modal-content {
        animation: none;
    }
    
    .btn-close:hover,
    .modal-footer .btn-primary:hover {
        transform: none;
    }
}

/* Print Styles */
@media print {
    .modal {
        display: none !important;
    }
}

/* Custom Scrollbar for Scrollable Modals */
.modal-dialog-scrollable .modal-body::-webkit-scrollbar {
    width: 6px;
}

.modal-dialog-scrollable .modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced Modal System
    window.ModalSystem = {
        modals: new Map(),
        
        init() {
            this.bindEvents();
            this.initializeExistingModals();
        },
        
        show(id, options = {}) {
            const modal = document.getElementById(id);
            if (!modal) {
                console.error(`Modal with id "${id}" not found`);
                return;
            }
            
            const bsModal = bootstrap.Modal.getOrCreateInstance(modal, options);
            bsModal.show();
            
            this.modals.set(id, bsModal);
            return bsModal;
        },
        
        hide(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        },
        
        toggle(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            
            const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
            bsModal.toggle();
        },
        
        confirm(options = {}) {
            const defaults = {
                title: 'Confirmação',
                message: 'Tem certeza?',
                confirmText: 'Confirmar',
                cancelText: 'Cancelar',
                confirmClass: 'btn-danger',
                cancelClass: 'btn-secondary',
                icon: 'fas fa-question-circle text-warning',
                onConfirm: null,
                onCancel: null
            };
            
            const config = { ...defaults, ...options };
            const modalId = 'confirm-modal-' + Date.now();
            
            const modalHtml = `
                <div class="modal fade" id="${modalId}" tabindex="-1" data-type="confirmation" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div class="modal-icon me-2">
                                    <i class="${config.icon}"></i>
                                </div>
                                <h5 class="modal-title">${config.title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" data-action="cancel"></button>
                            </div>
                            <div class="modal-body">
                                <div class="confirmation-content">
                                    <div class="confirmation-text">${config.message}</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn ${config.cancelClass}" data-bs-dismiss="modal" data-action="cancel">
                                    ${config.cancelText}
                                </button>
                                <button type="button" class="btn ${config.confirmClass}" data-action="confirm">
                                    ${config.confirmText}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = document.getElementById(modalId);
            
            // Bind events
            modal.addEventListener('click', (e) => {
                const action = e.target.dataset.action;
                if (action === 'confirm') {
                    if (config.onConfirm) {
                        const result = config.onConfirm();
                        if (result !== false) {
                            this.hide(modalId);
                        }
                    } else {
                        this.hide(modalId);
                    }
                } else if (action === 'cancel') {
                    if (config.onCancel) {
                        config.onCancel();
                    }
                }
            });
            
            // Clean up after hide
            modal.addEventListener('hidden.bs.modal', () => {
                modal.remove();
            });
            
            this.show(modalId);
            return modalId;
        },
        
        alert(message, options = {}) {
            const config = {
                title: options.title || 'Aviso',
                message: message,
                confirmText: 'OK',
                icon: options.type === 'error' ? 'fas fa-exclamation-triangle text-danger' : 
                      options.type === 'success' ? 'fas fa-check-circle text-success' :
                      'fas fa-info-circle text-info',
                onConfirm: options.onConfirm
            };
            
            return this.confirm(config);
        },
        
        prompt(message, options = {}) {
            const defaults = {
                title: 'Entrada',
                message: message,
                placeholder: '',
                inputType: 'text',
                confirmText: 'OK',
                cancelText: 'Cancelar',
                onConfirm: null,
                onCancel: null
            };
            
            const config = { ...defaults, ...options };
            const modalId = 'prompt-modal-' + Date.now();
            
            const modalHtml = `
                <div class="modal fade" id="${modalId}" tabindex="-1" data-type="form" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${config.title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">${config.message}</label>
                                    <input type="${config.inputType}" class="form-control" id="${modalId}-input" 
                                           placeholder="${config.placeholder}" autofocus>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-action="cancel">
                                    ${config.cancelText}
                                </button>
                                <button type="button" class="btn btn-primary" data-action="confirm">
                                    ${config.confirmText}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = document.getElementById(modalId);
            const input = document.getElementById(modalId + '-input');
            
            // Bind events
            modal.addEventListener('click', (e) => {
                const action = e.target.dataset.action;
                if (action === 'confirm') {
                    const value = input.value;
                    if (config.onConfirm) {
                        const result = config.onConfirm(value);
                        if (result !== false) {
                            this.hide(modalId);
                        }
                    } else {
                        this.hide(modalId);
                    }
                } else if (action === 'cancel') {
                    if (config.onCancel) {
                        config.onCancel();
                    }
                }
            });
            
            // Enter key submit
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    modal.querySelector('[data-action="confirm"]').click();
                }
            });
            
            // Clean up
            modal.addEventListener('hidden.bs.modal', () => {
                modal.remove();
            });
            
            this.show(modalId);
            return modalId;
        },
        
        setLoading(id, loading = true) {
            const modal = document.getElementById(id);
            if (!modal) return;
            
            const content = modal.querySelector('.modal-content');
            if (loading) {
                content.classList.add('modal-loading');
            } else {
                content.classList.remove('modal-loading');
            }
        },
        
        bindEvents() {
            // Global event delegation
            document.addEventListener('click', (e) => {
                // Handle confirmation actions
                const modal = e.target.closest('.modal');
                if (modal && modal.dataset.type === 'confirmation') {
                    const action = e.target.dataset.action;
                    if (action) {
                        const onConfirm = modal.dataset.onConfirm;
                        const onCancel = modal.dataset.onCancel;
                        
                        if (action === 'confirm' && onConfirm) {
                            if (window[onConfirm]) {
                                const result = window[onConfirm]();
                                if (result !== false) {
                                    this.hide(modal.id);
                                }
                            }
                        } else if (action === 'cancel' && onCancel) {
                            if (window[onCancel]) {
                                window[onCancel]();
                            }
                        }
                    }
                }
            });
            
            // Persistent modal handling
            document.addEventListener('click', (e) => {
                const modal = e.target.closest('.modal');
                if (modal && modal.dataset.persistent === 'true') {
                    if (e.target === modal) {
                        // Clicked on backdrop
                        e.stopPropagation();
                        modal.classList.add('shake');
                        setTimeout(() => {
                            modal.classList.remove('shake');
                        }, 500);
                    }
                }
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    const openModals = document.querySelectorAll('.modal.show');
                    if (openModals.length > 0) {
                        const topModal = openModals[openModals.length - 1];
                        if (topModal.dataset.persistent !== 'true') {
                            this.hide(topModal.id);
                        }
                    }
                }
            });
        },
        
        initializeExistingModals() {
            document.querySelectorAll('.modal').forEach(modal => {
                const id = modal.id;
                if (id) {
                    // Set up event listeners for existing modals
                    modal.addEventListener('shown.bs.modal', () => {
                        // Focus management
                        const autofocus = modal.querySelector('[autofocus]');
                        if (autofocus) {
                            autofocus.focus();
                        }
                    });
                    
                    modal.addEventListener('hidden.bs.modal', () => {
                        this.modals.delete(id);
                    });
                }
            });
        }
    };
    
    // Initialize the modal system
    window.ModalSystem.init();
    
    // Convenience functions
    window.showModal = (id, options) => window.ModalSystem.show(id, options);
    window.hideModal = (id) => window.ModalSystem.hide(id);
    window.confirmDialog = (options) => window.ModalSystem.confirm(options);
    window.alertDialog = (message, options) => window.ModalSystem.alert(message, options);
    window.promptDialog = (message, options) => window.ModalSystem.prompt(message, options);
});
</script>
@endpush
@endonce