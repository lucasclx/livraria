{{-- resources/views/components/notification.blade.php --}}
@props([
    'type' => 'info', // success, error, warning, info
    'title' => null,
    'message' => '',
    'duration' => 5000, // milliseconds, 0 for permanent
    'dismissible' => true,
    'icon' => null, // custom icon, auto-generated if null
    'position' => 'top-right', // top-right, top-left, bottom-right, bottom-left, top-center, bottom-center
    'size' => 'md', // sm, md, lg
    'showProgress' => true,
    'persistent' => false, // saves to localStorage
    'id' => null,
    'actions' => [], // array of action buttons
    'link' => null, // makes entire notification clickable
])

@php
    $notificationId = $id ?? 'notification-' . uniqid();
    
    $typeConfig = [
        'success' => [
            'class' => 'notification-success',
            'icon' => 'fas fa-check-circle',
            'color' => '#28a745'
        ],
        'error' => [
            'class' => 'notification-error', 
            'icon' => 'fas fa-exclamation-triangle',
            'color' => '#dc3545'
        ],
        'warning' => [
            'class' => 'notification-warning',
            'icon' => 'fas fa-exclamation-circle', 
            'color' => '#ffc107'
        ],
        'info' => [
            'class' => 'notification-info',
            'icon' => 'fas fa-info-circle',
            'color' => '#17a2b8'
        ]
    ];
    
    $config = $typeConfig[$type] ?? $typeConfig['info'];
    $iconClass = $icon ?? $config['icon'];
    
    $positionClasses = [
        'top-right' => 'notification-top-right',
        'top-left' => 'notification-top-left',
        'bottom-right' => 'notification-bottom-right', 
        'bottom-left' => 'notification-bottom-left',
        'top-center' => 'notification-top-center',
        'bottom-center' => 'notification-bottom-center'
    ];
    
    $sizeClasses = [
        'sm' => 'notification-sm',
        'md' => 'notification-md',
        'lg' => 'notification-lg'
    ];
@endphp

<div class="notification {{ $config['class'] }} {{ $positionClasses[$position] }} {{ $sizeClasses[$size] }} {{ $link ? 'notification-clickable' : '' }}"
     id="{{ $notificationId }}"
     data-type="{{ $type }}"
     data-duration="{{ $duration }}"
     data-persistent="{{ $persistent ? 'true' : 'false' }}"
     data-position="{{ $position }}"
     role="alert"
     aria-live="polite"
     @if($link) data-link="{{ $link }}" @endif>

    {{-- Progress bar --}}
    @if($showProgress && $duration > 0)
        <div class="notification-progress">
            <div class="progress-bar" style="--duration: {{ $duration }}ms; --color: {{ $config['color'] }}"></div>
        </div>
    @endif

    {{-- Main content --}}
    <div class="notification-content">
        {{-- Icon --}}
        <div class="notification-icon">
            <i class="{{ $iconClass }}" style="color: {{ $config['color'] }}"></i>
        </div>

        {{-- Text content --}}
        <div class="notification-text">
            @if($title)
                <div class="notification-title">{{ $title }}</div>
            @endif
            @if($message)
                <div class="notification-message">{{ $message }}</div>
            @endif
            {{ $slot }}
        </div>

        {{-- Actions --}}
        @if(!empty($actions))
            <div class="notification-actions">
                @foreach($actions as $action)
                    <button type="button" 
                            class="notification-action {{ $action['class'] ?? 'btn-link' }}"
                            @if(isset($action['onclick']))
                                onclick="{{ $action['onclick'] }}"
                            @endif
                            @if(isset($action['data']))
                                @foreach($action['data'] as $key => $value)
                                    data-{{ $key }}="{{ $value }}"
                                @endforeach
                            @endif>
                        @if(isset($action['icon']))
                            <i class="{{ $action['icon'] }} me-1"></i>
                        @endif
                        {{ $action['text'] }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Close button --}}
        @if($dismissible)
            <button type="button" 
                    class="notification-close" 
                    aria-label="Fechar notificação"
                    data-dismiss="notification">
                <i class="fas fa-times"></i>
            </button>
        @endif
    </div>
</div>

@once
@push('styles')
<style>
/* Notification Container */
.notification {
    position: fixed;
    z-index: 9999;
    max-width: 400px;
    min-width: 320px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: default;
    border-left: 4px solid;
    backdrop-filter: blur(10px);
    transform: translateX(100%);
    opacity: 0;
    animation: slideIn 0.4s ease-out forwards;
}

/* Position Classes */
.notification-top-right {
    top: 20px;
    right: 20px;
}

.notification-top-left {
    top: 20px;
    left: 20px;
    transform: translateX(-100%);
    animation: slideInLeft 0.4s ease-out forwards;
}

.notification-bottom-right {
    bottom: 20px;
    right: 20px;
}

.notification-bottom-left {
    bottom: 20px;
    left: 20px;
    transform: translateX(-100%);
    animation: slideInLeft 0.4s ease-out forwards;
}

.notification-top-center {
    top: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(-100%);
    animation: slideInTop 0.4s ease-out forwards;
}

.notification-bottom-center {
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(100%);
    animation: slideInBottom 0.4s ease-out forwards;
}

/* Size Variations */
.notification-sm {
    max-width: 300px;
    min-width: 280px;
}

.notification-md {
    max-width: 400px;
    min-width: 320px;
}

.notification-lg {
    max-width: 500px;
    min-width: 400px;
}

/* Type-specific styles */
.notification-success {
    border-left-color: #28a745;
    background: linear-gradient(135deg, #ffffff 0%, #f8fff9 100%);
}

.notification-error {
    border-left-color: #dc3545;
    background: linear-gradient(135deg, #ffffff 0%, #fff8f8 100%);
}

.notification-warning {
    border-left-color: #ffc107;
    background: linear-gradient(135deg, #ffffff 0%, #fffdf8 100%);
}

.notification-info {
    border-left-color: #17a2b8;
    background: linear-gradient(135deg, #ffffff 0%, #f8fcfd 100%);
}

/* Progress Bar */
.notification-progress {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    width: 100%;
    background: var(--color);
    transform: translateX(-100%);
    animation: progressAnimation var(--duration) linear forwards;
}

/* Content Layout */
.notification-content {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px 20px;
    position: relative;
}

.notification-icon {
    flex-shrink: 0;
    font-size: 20px;
    margin-top: 2px;
}

.notification-text {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-weight: 600;
    font-size: 14px;
    color: #1a1a1a;
    margin-bottom: 4px;
    line-height: 1.4;
}

.notification-message {
    font-size: 13px;
    color: #666;
    line-height: 1.5;
    word-wrap: break-word;
}

/* Close Button */
.notification-close {
    position: absolute;
    top: 12px;
    right: 12px;
    background: none;
    border: none;
    color: #999;
    font-size: 12px;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s ease;
    z-index: 1;
}

.notification-close:hover {
    background: rgba(0, 0, 0, 0.1);
    color: #666;
}

/* Actions */
.notification-actions {
    display: flex;
    gap: 8px;
    margin-top: 8px;
    flex-wrap: wrap;
}

.notification-action {
    background: none;
    border: none;
    color: #007bff;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.notification-action:hover {
    background: rgba(0, 123, 255, 0.1);
    text-decoration: none;
    color: #0056b3;
}

/* Clickable Notification */
.notification-clickable {
    cursor: pointer;
}

.notification-clickable:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.2);
}

/* Animations */
@keyframes slideIn {
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideInLeft {
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideInTop {
    to {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
}

@keyframes slideInBottom {
    to {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

@keyframes slideOutLeft {
    to {
        transform: translateX(-100%);
        opacity: 0;
    }
}

@keyframes slideOutTop {
    to {
        transform: translateX(-50%) translateY(-100%);
        opacity: 0;
    }
}

@keyframes slideOutBottom {
    to {
        transform: translateX(-50%) translateY(100%);
        opacity: 0;
    }
}

@keyframes progressAnimation {
    to {
        transform: translateX(0);
    }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* State Classes */
.notification.dismissing {
    animation: slideOut 0.3s ease-in forwards;
}

.notification-top-left.dismissing,
.notification-bottom-left.dismissing {
    animation: slideOutLeft 0.3s ease-in forwards;
}

.notification-top-center.dismissing {
    animation: slideOutTop 0.3s ease-in forwards;
}

.notification-bottom-center.dismissing {
    animation: slideOutBottom 0.3s ease-in forwards;
}

.notification.shake {
    animation: shake 0.5s ease-in-out;
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .notification {
        background: #2d3748;
        color: #e2e8f0;
    }
    
    .notification-title {
        color: #f7fafc;
    }
    
    .notification-message {
        color: #cbd5e0;
    }
    
    .notification-success {
        background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
    }
    
    .notification-error {
        background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
    }
    
    .notification-warning {
        background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
    }
    
    .notification-info {
        background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
    }
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .notification {
        max-width: calc(100vw - 40px);
        min-width: calc(100vw - 40px);
        left: 20px !important;
        right: 20px !important;
        transform: translateY(-100%) !important;
    }
    
    .notification-top-right,
    .notification-top-left,
    .notification-top-center {
        animation: slideInTop 0.4s ease-out forwards;
    }
    
    .notification-bottom-right,
    .notification-bottom-left, 
    .notification-bottom-center {
        transform: translateY(100%) !important;
        animation: slideInBottom 0.4s ease-out forwards;
    }
    
    .notification.dismissing {
        animation: slideOutTop 0.3s ease-in forwards;
    }
    
    .notification-bottom-right.dismissing,
    .notification-bottom-left.dismissing,
    .notification-bottom-center.dismissing {
        animation: slideOutBottom 0.3s ease-in forwards;
    }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    .notification {
        animation: none;
        transform: translateX(0);
        opacity: 1;
    }
    
    .notification.dismissing {
        animation: none;
        opacity: 0;
    }
    
    .progress-bar {
        animation: none;
    }
}

/* High Contrast */
@media (prefers-contrast: high) {
    .notification {
        border: 2px solid;
        box-shadow: none;
    }
    
    .notification-success {
        border-color: #28a745;
    }
    
    .notification-error {
        border-color: #dc3545;
    }
    
    .notification-warning {
        border-color: #ffc107;
    }
    
    .notification-info {
        border-color: #17a2b8;
    }
}

/* Print Styles */
@media print {
    .notification {
        display: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize notification system
    window.NotificationSystem = {
        notifications: new Map(),
        container: null,
        
        init() {
            this.container = document.getElementById('notification-container');
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.id = 'notification-container';
                this.container.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; pointer-events: none; z-index: 9999;';
                document.body.appendChild(this.container);
            }
            
            this.loadPersistentNotifications();
            this.bindEvents();
        },
        
        show(options = {}) {
            const defaults = {
                type: 'info',
                title: null,
                message: '',
                duration: 5000,
                dismissible: true,
                position: 'top-right',
                size: 'md',
                showProgress: true,
                persistent: false,
                actions: [],
                link: null,
                onShow: null,
                onHide: null,
                onClick: null
            };
            
            const config = { ...defaults, ...options };
            const id = 'notification-' + Date.now() + Math.random().toString(36).substr(2, 9);
            
            const notification = this.createNotification(id, config);
            this.container.appendChild(notification);
            this.notifications.set(id, { element: notification, config });
            
            // Auto-dismiss
            if (config.duration > 0) {
                setTimeout(() => {
                    this.hide(id);
                }, config.duration);
            }
            
            // Save persistent notifications
            if (config.persistent) {
                this.savePersistentNotification(id, config);
            }
            
            // Callbacks
            if (config.onShow) config.onShow(notification);
            
            return id;
        },
        
        createNotification(id, config) {
            const notification = document.createElement('div');
            notification.id = id;
            notification.className = `notification notification-${config.type} notification-${config.position} notification-${config.size} ${config.link ? 'notification-clickable' : ''}`;
            notification.setAttribute('role', 'alert');
            notification.setAttribute('aria-live', 'polite');
            notification.style.pointerEvents = 'auto';
            
            if (config.link) {
                notification.dataset.link = config.link;
            }
            
            let html = '';
            
            // Progress bar
            if (config.showProgress && config.duration > 0) {
                const color = this.getTypeColor(config.type);
                html += `
                    <div class="notification-progress">
                        <div class="progress-bar" style="--duration: ${config.duration}ms; --color: ${color}"></div>
                    </div>
                `;
            }
            
            // Content
            html += '<div class="notification-content">';
            
            // Icon
            const icon = this.getTypeIcon(config.type);
            const color = this.getTypeColor(config.type);
            html += `
                <div class="notification-icon">
                    <i class="${icon}" style="color: ${color}"></i>
                </div>
            `;
            
            // Text
            html += '<div class="notification-text">';
            if (config.title) {
                html += `<div class="notification-title">${config.title}</div>`;
            }
            if (config.message) {
                html += `<div class="notification-message">${config.message}</div>`;
            }
            html += '</div>';
            
            // Actions
            if (config.actions.length > 0) {
                html += '<div class="notification-actions">';
                config.actions.forEach(action => {
                    html += `
                        <button type="button" class="notification-action ${action.class || 'btn-link'}" 
                                data-action="${action.action || ''}" 
                                ${action.onclick ? `onclick="${action.onclick}"` : ''}>
                            ${action.icon ? `<i class="${action.icon} me-1"></i>` : ''}
                            ${action.text}
                        </button>
                    `;
                });
                html += '</div>';
            }
            
            // Close button
            if (config.dismissible) {
                html += `
                    <button type="button" class="notification-close" aria-label="Fechar notificação" data-dismiss="notification">
                        <i class="fas fa-times"></i>
                    </button>
                `;
            }
            
            html += '</div>';
            notification.innerHTML = html;
            
            return notification;
        },
        
        hide(id) {
            const notification = this.notifications.get(id);
            if (!notification) return;
            
            const element = notification.element;
            const config = notification.config;
            
            element.classList.add('dismissing');
            
            setTimeout(() => {
                if (element.parentNode) {
                    element.parentNode.removeChild(element);
                }
                this.notifications.delete(id);
                
                if (config.persistent) {
                    this.removePersistentNotification(id);
                }
                
                if (config.onHide) config.onHide(element);
            }, 300);
        },
        
        hideAll() {
            this.notifications.forEach((notification, id) => {
                this.hide(id);
            });
        },
        
        bindEvents() {
            // Close button clicks
            this.container.addEventListener('click', (e) => {
                if (e.target.matches('[data-dismiss="notification"]') || e.target.closest('[data-dismiss="notification"]')) {
                    const notification = e.target.closest('.notification');
                    if (notification) {
                        this.hide(notification.id);
                    }
                }
                
                // Action button clicks
                if (e.target.matches('.notification-action')) {
                    const action = e.target.dataset.action;
                    const notification = e.target.closest('.notification');
                    
                    if (action && notification) {
                        this.handleAction(action, notification.id);
                    }
                }
                
                // Clickable notification
                if (e.target.closest('.notification-clickable')) {
                    const notification = e.target.closest('.notification');
                    const link = notification.dataset.link;
                    
                    if (link) {
                        if (link.startsWith('http')) {
                            window.open(link, '_blank');
                        } else {
                            window.location.href = link;
                        }
                    }
                    
                    const config = this.notifications.get(notification.id)?.config;
                    if (config?.onClick) {
                        config.onClick(notification);
                    }
                }
            });
            
            // Keyboard accessibility
            this.container.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    const notifications = Array.from(this.notifications.keys());
                    if (notifications.length > 0) {
                        this.hide(notifications[notifications.length - 1]);
                    }
                }
            });
        },
        
        handleAction(action, notificationId) {
            switch (action) {
                case 'dismiss':
                    this.hide(notificationId);
                    break;
                case 'retry':
                    // Custom retry logic
                    break;
                case 'undo':
                    // Custom undo logic
                    break;
                default:
                    // Custom action
                    console.log('Custom action:', action);
            }
        },
        
        getTypeIcon(type) {
            const icons = {
                success: 'fas fa-check-circle',
                error: 'fas fa-exclamation-triangle',
                warning: 'fas fa-exclamation-circle',
                info: 'fas fa-info-circle'
            };
            return icons[type] || icons.info;
        },
        
        getTypeColor(type) {
            const colors = {
                success: '#28a745',
                error: '#dc3545',
                warning: '#ffc107',
                info: '#17a2b8'
            };
            return colors[type] || colors.info;
        },
        
        // Persistent notifications (localStorage)
        savePersistentNotification(id, config) {
            const persistent = JSON.parse(localStorage.getItem('notifications') || '[]');
            persistent.push({ id, config, timestamp: Date.now() });
            localStorage.setItem('notifications', JSON.stringify(persistent));
        },
        
        loadPersistentNotifications() {
            const persistent = JSON.parse(localStorage.getItem('notifications') || '[]');
            const now = Date.now();
            const maxAge = 24 * 60 * 60 * 1000; // 24 hours
            
            const valid = persistent.filter(item => {
                return (now - item.timestamp) < maxAge;
            });
            
            valid.forEach(item => {
                this.show({ ...item.config, persistent: false });
            });
            
            localStorage.setItem('notifications', JSON.stringify(valid));
        },
        
        removePersistentNotification(id) {
            const persistent = JSON.parse(localStorage.getItem('notifications') || '[]');
            const filtered = persistent.filter(item => item.id !== id);
            localStorage.setItem('notifications', JSON.stringify(filtered));
        },
        
        // Convenience methods
        success(message, options = {}) {
            return this.show({ ...options, type: 'success', message });
        },
        
        error(message, options = {}) {
            return this.show({ ...options, type: 'error', message });
        },
        
        warning(message, options = {}) {
            return this.show({ ...options, type: 'warning', message });
        },
        
        info(message, options = {}) {
            return this.show({ ...options, type: 'info', message });
        }
    };
    
    // Initialize the system
    window.NotificationSystem.init();
    
    // Handle existing notifications on page
    document.querySelectorAll('.notification').forEach(notification => {
        const duration = parseInt(notification.dataset.duration) || 0;
        const id = notification.id;
        
        if (duration > 0) {
            setTimeout(() => {
                window.NotificationSystem.hide(id);
            }, duration);
        }
        
        // Bind close button
        const closeBtn = notification.querySelector('[data-dismiss="notification"]');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                window.NotificationSystem.hide(id);
            });
        }
        
        // Bind clickable notification
        if (notification.classList.contains('notification-clickable')) {
            const link = notification.dataset.link;
            if (link) {
                notification.addEventListener('click', () => {
                    if (link.startsWith('http')) {
                        window.open(link, '_blank');
                    } else {
                        window.location.href = link;
                    }
                });
            }
        }
    });
});
</script>
@endpush
@endonce