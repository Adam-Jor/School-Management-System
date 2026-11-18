@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'help' => null,
    'disabled' => false,
    'readonly' => false,
    'autofocus' => false,
    'autocomplete' => null,
    'id' => null,
])

@php
    $inputId = $id ?? $name;
    $hasError = $errors->has($name);
    $inputValue = old($name, $value);

    $ariaAttrs = '';
    if ($hasError) {
        $ariaAttrs = 'aria-invalid="true" aria-describedby="' . $inputId . '-error"';
    } elseif ($help) {
        $ariaAttrs = 'aria-describedby="' . $inputId . '-help"';
    }
@endphp

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $inputId }}" class="block text-xs font-medium text-secondary mb-1.5">
            {{ $label }}
            @if ($required)
                <span class="text-error" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <input type="{{ $type }}"
               id="{{ $inputId }}"
               name="{{ $name }}"
               value="{{ $inputValue }}"
               @if ($placeholder) placeholder="{{ $placeholder }}" @endif
               @if ($required) required aria-required="true" @endif
               @if ($disabled) disabled @endif
               @if ($readonly) readonly @endif
               @if ($autofocus) autofocus @endif
               @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
               {!! $ariaAttrs !!}
               {{ $attributes->except('class')->merge([
                   'class' => 'edudash-input ' . ($hasError ? 'border-error focus:border-error focus:ring-error/20 bg-error-soft/10 text-error' : '')
               ]) }} />
    </div>

    @if ($hasError)
        <p id="{{ $inputId }}-error" class="mt-1.5 text-xs text-error font-medium flex items-center gap-1">
            <x-heroicon-s-exclamation-circle class="size-3.5 shrink-0" />
            <span>{{ $errors->first($name) }}</span>
        </p>
    @elseif ($help)
        <p id="{{ $inputId }}-help" class="mt-1.5 text-xs text-muted">
            {{ $help }}
        </p>
    @endif
</div>
