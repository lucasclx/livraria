@props(['title' => null, 'footer' => null])

<div class="card">
    @if ($title)
        <div class="card-header">
            <h3 class="card-title">{{ $title }}</h3>
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
    @if ($footer)
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>