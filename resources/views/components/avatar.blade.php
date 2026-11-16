@props([
    'name' => 'ED',
    'src' => null,
    'size' => 'md',
])
@php
    $words = preg_split('/\s+/', trim(preg_replace('/[,.].*$/', '', $name) ?: $name)) ?: [];
    $initials = '';
    foreach (array_slice($words, 0, 2) as $w) {
        $initials .= mb_strtoupper(mb_substr($w, 0, 1));
    }
    $initials = $initials ?: 'ED';

    $sizeClasses = match ($size) {
        'sm' => 'size-7 text-[11px] rounded-md',
        'lg' => 'size-14 text-base rounded-xl',
        default => 'size-8 text-xs rounded-lg',
    };
@endphp

@if (!empty($src) && file_exists(public_path($src)))
    <img src="{{ asset($src) }}"
         alt="Foto {{ $name }}"
         loading="lazy"
         class="{{ $sizeClasses }} object-cover shrink-0 border border-line" />
@else
    <span class="{{ $sizeClasses }} bg-raised text-secondary border border-line font-medium flex items-center justify-center shrink-0 select-none">
        {{ $initials }}
    </span>
@endif
