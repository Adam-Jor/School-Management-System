@props([
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'bg-surface rounded-[20px] p-6 shadow-card ' . $class]) }}>
    {{ $slot }}
</div>
