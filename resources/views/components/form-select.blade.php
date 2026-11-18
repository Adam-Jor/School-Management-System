@props([
    'name',
    'label' => null,
    'required' => false,
    'help' => null,
    'disabled' => false,
    'id' => null,
    'placeholder' => null,
])

@php
    $selectId = $id ?? $name;
    $hasError = $errors->has($name);

    $ariaAttrs = '';
    if ($hasError) {
        $ariaAttrs = 'aria-invalid="true" aria-describedby="' . $selectId . '-error"';
    } elseif ($help) {
        $ariaAttrs = 'aria-describedby="' . $selectId . '-help"';
    }
@endphp

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $selectId }}" class="block text-xs font-medium text-secondary mb-1.5">
            {{ $label }}
            @if ($required)
                <span class="text-error" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <select id="{{ $selectId }}"
                name="{{ $name }}"
                @if ($required) required aria-required="true" @endif
                @if ($disabled) disabled @endif
                {!! $ariaAttrs !!}
                {{ $attributes->except('class')->merge([
                    'class' => 'edudash-select ' . ($hasError ? 'border-error focus:border-error focus:ring-error/20 bg-error-soft/10 text-error' : '')
                ]) }}>
            @if ($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>
    </div>

    @if ($hasError)
        <p id="{{ $selectId }}-error" class="mt-1.5 text-xs text-error font-medium flex items-center gap-1">
            <x-heroicon-s-exclamation-circle class="size-3.5 shrink-0" />
            <span>{{ $errors->first($name) }}</span>
        </p>
    @elseif ($help)
        <p id="{{ $selectId }}-help" class="mt-1.5 text-xs text-muted">
            {{ $help }}
        </p>
    @endif
</div>
