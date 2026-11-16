@props([
    'name' => 'ED',
    'src' => null,
    'size' => 'md',
    'tone' => 'primary',
])
@php
    $words = preg_split('/\s+/', trim(preg_replace('/[,.].*$/', '', $name) ?: $name)) ?: [];
    $initials = '';
    foreach (array_slice($words, 0, 2) as $w) {
        $initials .= mb_strtoupper(mb_substr($w, 0, 1));
    }
    $initials = $initials ?: 'ED';

    $sizeClasses = match ($size) {
        'sm' => 'size-8 text-xs',
        'lg' => 'size-16 text-xl',
        default => 'size-9 text-xs',
    };

    $toneClasses = match ($tone) {
        'success' => 'bg-success-soft text-[#15803D]',
        'error' => 'bg-error-soft text-[#B91C1C]',
        'warning' => 'bg-warning-soft text-[#B45309]',
        default => 'bg-primary-soft text-primary',
    };
@endphp

@if (!empty($src) && file_exists(public_path($src)))
    <img src="{{ asset($src) }}"
         alt="Foto {{ $name }}"
         loading="lazy"
         class="{{ $sizeClasses }} rounded-full object-cover shrink-0 border border-line" />
@else
    <span class="{{ $sizeClasses }} {{ $toneClasses }} rounded-full font-bold flex items-center justify-center shrink-0 select-none">
        {{ $initials }}
    </span>
@endif
