@props([
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'bg-surface rounded-2xl p-4 sm:p-6 shadow-card border border-line/80 ' . $class]) }}>
    {{ $slot }}
</div>
