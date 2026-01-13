<div class="flex items-start max-md:flex-col max-w-2xl lg:max-w-6xl mx-auto space-y-10 py-4 gap-6 px-4">
    {{-- Sidebar panel (left) --}}
    <aside class="w-full md:w-72 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 shadow-sm">
        <div class="flex items-center gap-3 p-2 rounded-md bg-gray-50 dark:bg-zinc-800">
            <div class="h-10 w-10 rounded-full bg-lime-400 flex items-center justify-center text-white font-semibold">{{ strtoupper(substr(auth()->user()->name ?? 'U',0,2)) }}</div>
            <div class="flex flex-col">
                <span class="font-semibold text-sm">{{ auth()->user()->name ?? 'Usuario' }}</span>
                <span class="text-xs text-zinc-500">{{ auth()->user()->email ?? 'usuario@example.com' }}</span>
            </div>
        </div>

        <div class="mt-4">
            <div class="relative">
                <input type="search" placeholder="{{ __('Buscar') }}" class="w-full rounded-md border border-zinc-200 dark:border-zinc-700 px-3 py-2 text-sm bg-transparent" />
                <span class="absolute inset-y-0 end-3 flex items-center text-zinc-400"></span>
            </div>
        </div>

        <nav class="mt-5">
            <flux:navlist aria-label="{{ __('Settings') }}">
                <flux:navlist.item :href="route('profile.edit')" :current="request()->routeIs('profile.edit')" wire:navigate icon="user">{{ __('Perfil') }}</flux:navlist.item>
                <flux:navlist.item :href="route('user-password.edit')" :current="request()->routeIs('user-password.edit')" wire:navigate icon="eye-slash">{{ __('Contraseña') }}</flux:navlist.item>
                @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                    <flux:navlist.item :href="route('two-factor.show')" :current="request()->routeIs('two-factor.show')" wire:navigate icon="shield-check">{{ __('Two-Factor Auth') }}</flux:navlist.item>
                @endif
                <flux:navlist.item :href="route('notifications.edit')" :current="request()->routeIs('notifications.edit')" wire:navigate icon="bell">{{ __('Notificaciones') }}</flux:navlist.item>
                <flux:navlist.item :href="route('appearance.edit')" :current="request()->routeIs('appearance.edit')" wire:navigate icon="sparkles">{{ __('Apariencia') }}</flux:navlist.item>
            </flux:navlist>
        </nav>
    </aside>

    {{-- Main content (right) --}}
    <main class="flex-1">
        <flux:heading class="mb-2">{{ $heading ?? '' }}</flux:heading>
        <flux:subheading class="mb-4">{{ $subheading ?? '' }}</flux:subheading>

        <div class="space-y-6">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 shadow-sm">
                {{ $slot }}
            </div>
        </div>
    </main>
</div>
