@props([
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-zinc-800 rounded-lg shadow border border-zinc-200 dark:border-zinc-700 overflow-hidden ' . ($padding ? 'p-6' : '')]) }}>
    {{ $slot }}
</div>
