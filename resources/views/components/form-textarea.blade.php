@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'help' => null,
    'disabled' => false,
    'readonly' => false,
    'rows' => 3,
    'id' => null,
])

@php
    $textareaId = $id ?? $name;
    $hasError = $errors->has($name);
    $textValue = old($name, $value);

    $ariaAttrs = '';
    if ($hasError) {
        $ariaAttrs = 'aria-invalid="true" aria-describedby="' . $textareaId . '-error"';
    } elseif ($help) {
        $ariaAttrs = 'aria-describedby="' . $textareaId . '-help"';
    }
@endphp

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $textareaId }}" class="block text-xs font-medium text-secondary mb-1.5">
            {{ $label }}
            @if ($required)
                <span class="text-error" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <textarea id="{{ $textareaId }}"
                  name="{{ $name }}"
                  rows="{{ $rows }}"
                  @if ($placeholder) placeholder="{{ $placeholder }}" @endif
                  @if ($required) required aria-required="true" @endif
                  @if ($disabled) disabled @endif
                  @if ($readonly) readonly @endif
                  {!! $ariaAttrs !!}
                  {{ $attributes->except('class')->merge([
                      'class' => 'edudash-textarea ' . ($hasError ? 'border-error focus:border-error focus:ring-error/20 bg-error-soft/10 text-error' : '')
                  ]) }}>{{ $textValue }}</textarea>
    </div>

    @if ($hasError)
        <p id="{{ $textareaId }}-error" class="mt-1.5 text-xs text-error font-medium flex items-center gap-1">
            <x-heroicon-s-exclamation-circle class="size-3.5 shrink-0" />
            <span>{{ $errors->first($name) }}</span>
        </p>
    @elseif ($help)
        <p id="{{ $textareaId }}-help" class="mt-1.5 text-xs text-muted">
            {{ $help }}
        </p>
    @endif
</div>
