@props([
    'type' => 'submit', // submit, button, reset
    'style' => 'primary', // primary, secondary, success, danger, warning, info, light, dark, link
    'href' => null,
    'icon' => null,
    'text' => 'Botão',
    'class' => '',
])

@if ($href)
    <a href="{{ $href }}" class="btn btn-{{ $style }} {{ $class }}" {{ $attributes }}>
        @if ($icon)
            <i class="fas fa-{{ $icon }} mr-1"></i>
        @endif
        {{ $text }}
    </a>
@else
    <button type="{{ $type }}" class="btn btn-{{ $style }} {{ $class }}" {{ $attributes }}>
        @if ($icon)
            <i class="fas fa-{{ $icon }} mr-1"></i>
        @endif
        {{ $text }}
    </button>
@endif