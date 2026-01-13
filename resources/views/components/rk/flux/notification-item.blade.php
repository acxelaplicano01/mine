@props([
    'href' => '#',
    'avatar' => null,
    'initials' => null,
    'title' => '',
    'message' => '',
    'time' => '',
    'badgeColor' => 'bg-red-600',
    'read' => false,
])

<a href="{{ $href }}" class="flex px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-600 rounded-md">
    <div class="shrink-0 relative">
        @if($avatar)
            <img class="rounded-full w-11 h-11" src="{{ $avatar }}" alt="{{ $title }}">
        @else
            <div class="rounded-full w-11 h-11 bg-neutral-200 flex items-center justify-center text-sm font-semibold text-white">{{ $initials ?? strtoupper(substr($title, 0, 2)) }}</div>
        @endif

        <div class="absolute flex items-center justify-center w-5 h-5 ms-6 -mt-5 {{ $badgeColor }} border-2 border-white rounded-full">
            <svg class="w-3 h-3 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z"/></svg>
        </div>
    </div>

    <div class="w-full ps-3">
        <div class="font-semibold text-sm text-zinc-900 dark:text-white">{{ $title }}</div>
        <div class="text-sm text-zinc-700 dark:text-zinc-300 mt-1">{!! $message !!}</div>
        <div class="text-xs text-fg-brand">{{ $time }}</div>
    </div>
</a>
