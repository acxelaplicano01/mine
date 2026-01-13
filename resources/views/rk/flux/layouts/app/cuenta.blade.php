@php
    $footerQuery = rk_navigation()
        ->newQuery()
        ->loadContexts(['rk_footer'])
        ->filterForCurrentUser();

    $footerItems = $footerQuery->get();



    $configNode = $footerQuery->getSubBranch('settings_group')->first();

    $headerQuery = rk_navigation()
        ->newQuery()
        ->loadContexts(['dashboard_navigators'])
        ->filterForCurrentUser();

    $headerItems = $headerQuery->get();

    $fullQuery = rk_navigation()
        ->newQuery()
        ->loadContexts(['dashboard_navigators', 'rk_footer'])
        ->filterForCurrentUser();

    $activeNode = $fullQuery->getActiveNodeParentWithChildren();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>


    @include('rk.flux.partials.head')

    @fluxAppearance
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">

    <!-- HEADER -->
    <flux:header sticky container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <!-- HEADER ITEMS -->
        <flux:navbar class="-mb-px " scrollable>
            @if (!is_null($activeNode))
                @foreach ($activeNode?->items as $item)
                    @if ($item->isGroup())
                        <flux:dropdown>
                            <flux:navbar.item icon="chevron-down">{{ $item->getLabel() }}</flux:navbar.item>
                            <flux:navmenu>
                                @foreach ($item->items as $child)
                                    <x-rk.flux::components.simple-node-nav-item :node="$child" />
                                @endforeach
                            </flux:navmenu>
                        </flux:dropdown>
                    @else
                        <x-rk.flux::components.simple-node-nav :node="$item" />
                    @endif
                @endforeach
            @endif
        </flux:navbar>

        <flux:spacer />

         <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
            <flux:separator vertical class="my-1"/>
            <!-- Aquí puedes agregar ítems extra como buscar o ayuda -->
            <flux:navbar.item icon="magnifying-glass" />
            <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle" aria-label="Toggle dark mode" />
        </flux:navbar>
        
         <!-- Notificaciones -->
         <flux:dropdown position="top" align="end">
            <flux:navbar.item icon="bell" badge badge:color="red" badge:position="top" badge:circle badge:variant="outline"/>

            <flux:navmenu class="max-w-[20rem]">
                <div class="px-2 py-1.5 flex items-center justify-between">
                    <flux:text size="sm">Notificaciones</flux:text>

                    <div class="flex items-center gap-1">
                        <flux:button variant="ghost" size="sm" icon="check-circle" icon:variant="outline" aria-label="Marcar todos leídos" data-action="check" />
                        <flux:button variant="ghost" size="sm" icon="funnel" icon:variant="outline" aria-label="Solo no leídos" data-action="filter-unread" />
                    </div>
                </div>

                <flux:navmenu.separator />
                 {{-- Notifications list --}}
                <div>
                        <x-rk.flux.notification-item
                            id="1"
                            href="#"
                            avatar="https://unavatar.io/x/calebporzio"
                            title="Joseph Mcfall"
                            message='<span class="font-semibold">Joseph Mcfall</span> and <span class="font-medium">5 others</span> started following you.'
                            time="10 minutes ago"
                            badgeColor="bg-green-600"
                            read="false"
                        />

                    <flux:navmenu.separator />

                        <x-rk.flux.notification-item
                            id="2"
                            href="#"
                            avatar="https://unavatar.io/x/calebporzio"
                            title="Bonnie Green"
                            message='<span class="font-semibold">Bonnie Green</span> and <span class="font-medium">141 others</span> love your story.'
                            time="44 minutes ago"
                            badgeColor="bg-red-600"
                            read="false"
                        />
                </div>

                <flux:navmenu.separator />

                <flux:navmenu.item href="#" class="justify-center text-center text-sm text-zinc-500 dark:text-zinc-400 truncate">Ver todas las notificaciones</flux:navmenu.item>
            </flux:navmenu>
        </flux:dropdown>

        <!-- Desktop User Menu -->
        <flux:dropdown position="top" align="end">
            <flux:profile avatar="https://unavatar.io/x/calebporzio" class="cursor-pointer" :initials="auth()->user()->initials()"/>

            <flux:navmenu class="max-w-[20rem]">
                <div class="px-2 py-1.5">
                    <flux:text size="sm">Conectado como</flux:text>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                                <flux:badge color="lime" size="xs">Gratis</flux:badge>
                            </div>
                        </div>
                    </flux:menu.radio.group>
                </div>

                <flux:menu.separator />

                <x-rk.flux::components.simple-node :node="$configNode" />

                <flux:menu.radio.group>

                </flux:menu.radio.group>

                <div class="px-2 py-1.5">
                    <flux:text size="sm" class="pl-7">Tiendas</flux:text>
                </div>

                <flux:navmenu.item href="#" icon="check" class="text-zinc-800 dark:text-white truncate">Mi tienda Admin</flux:navmenu.item>
                <flux:navmenu.item href="#" icon="building-storefront" icon:variant="outline" class="text-zinc-800 dark:text-white truncate">Todas mis tiendas</flux:navmenu.item>

                <flux:navmenu.separator />

                <flux:navmenu.item :href="route('profile.edit')" icon="user-circle" icon:variant="outline" wire:navigate>{{ __('Perfil') }}</flux:menu.item>
                <flux:navmenu.item :href="route('user_cuenta')" icon="cog-6-tooth" icon:variant="outline" wire:navigate>{{ __('Cuenta') }}</flux:navmenu.item>
                <flux:navmenu.item :href="route('user_cuenta')" icon="lock-closed" icon:variant="outline" wire:navigate>{{ __('Privacidad') }}</flux:navmenu.item>
                <flux:navmenu.item :href="route('notifications.edit')" icon="bell" icon:variant="outline" wire:navigate>{{ __('Notificaciones') }}</flux:navmenu.item>
                <flux:navmenu.item :href="route('login')" icon="paint-brush" icon:variant="outline" wire:navigate>{{ __('Preferencias') }}</flux:navmenu.item>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:navmenu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Cerrar sesión') }}
                    </flux:navmenu.item>
                </form>
            </flux:navmenu>
        </flux:dropdown>
    </flux:header>

    <!-- MOBILE HEADER -->
    <flux:header class="hidden z-0" sticky>

    </flux:header>
    {{ $slot }}


    @fluxScripts
</body>

</html>