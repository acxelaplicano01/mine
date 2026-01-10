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

            <!-- Aquí puedes agregar ítems extra como buscar o ayuda -->
        </flux:navbar>

        <!-- Desktop User Menu -->
        <flux:dropdown position="top" align="end">
            <flux:profile class="cursor-pointer" :initials="auth()->user()->initials()" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->firts_name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <x-rk.flux::components.simple-node :node="$configNode" />

                <flux:menu.radio.group>

                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
                <section class="w-full">
                    <flux:menu.separator />
                    <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                        <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                        <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                        <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                    </flux:radio.group>
                </section>

            </flux:menu>
        </flux:dropdown>
    </flux:header>


     <flux:sidebar sticky collapsible class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.header>
            <flux:sidebar.brand
                href="#"
                logo="https://fluxui.dev/img/demo/logo.png"
                logo:dark="https://fluxui.dev/img/demo/dark-mode-logo.png"
                name="Acme Inc."
            />

            <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            @foreach ($headerItems as $item)
                @if ($item->getItems()->isNotEmpty())
                    <flux:sidebar.group
                    expandable
                     :expanded="$item->isActive()"

                    icon="{{ $item->getHeroIcon() }}" heading="{{ $item->getLabel() }}">
                        @foreach ($item->items as $child)
                            <flux:sidebar.item href="{{ $child->getUrl() }}" icon="{{ $child->getHeroIcon() }}" badge="{{ $child->getFinalBage() }}" :current="$child->isActive()">
                                {{ $child->getLabel() }}
                            </flux:sidebar.item>
                        @endforeach
                    </flux:sidebar.group>
                @else
                      <flux:sidebar.item icon="{{ $item->getHeroIcon() }}" href="{{ $item->getUrl() }}" :current="$item->isActive()" badge="{{ $item->getFinalBage() }}">
                          {{ $item->getLabel() }}
                      </flux:sidebar.item>
                @endif
            @endforeach
           
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <flux:sidebar.nav>
            <flux:sidebar.item icon="cog-6-tooth" href="#">Settings</flux:sidebar.item>
            <flux:sidebar.item icon="information-circle" href="#">Help</flux:sidebar.item>
        </flux:sidebar.nav>

        <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:sidebar.profile avatar="https://fluxui.dev/img/demo/user.png" name="Olivia Martin" />

            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio checked>Olivia Martin</flux:menu.radio>
                    <flux:menu.radio>Truly Delta</flux:menu.radio>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.item icon="arrow-right-start-on-rectangle">Logout</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>


    <!-- MOBILE HEADER -->
    <flux:header class="hidden z-0" sticky>

    </flux:header>
    {{ $slot }}


    @fluxScripts
</body>

</html>