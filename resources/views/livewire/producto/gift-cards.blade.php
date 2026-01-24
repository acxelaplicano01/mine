<div class="min-h-screen">
    {{-- Mensaje de éxito --}}
    @if (session()->has('message'))
        <div class="px-4 sm:px-6 lg:px-8 py-4">
            <flux:callout dismissible variant="success" icon="check-circle" heading="{{ session('message') }}" />
        </div>
    @endif

    {{-- Header principal --}}
    <div>
        <div class="px-2 sm:px-4 lg:px-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Tarjetas de regalo</h1>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Administra las tarjetas de regalo de tu tienda</p>
                </div>
                <div class="flex items-center gap-3">
                    <flux:button variant="filled" size="sm" wire:click="openExportModal" icon="arrow-up-tray">
                        Exportar
                    </flux:button>
                    <flux:dropdown>
                        <flux:button variant="filled" size="sm" icon-trailing="chevron-down">
                            Más acciones
                        </flux:button>
                        <flux:menu>
                            <flux:menu.item>Exportar seleccionados</flux:menu.item>
                            <flux:menu.item>Imprimir códigos</flux:menu.item>
                            <flux:menu.item>Marcar como usado</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                    <flux:button wire:click="create" icon="plus" variant="primary" size="sm">
                        Crear tarjeta
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="px-2">
        {{-- Tabla de tarjetas de regalo --}}
        @php
            $columns = [
                ['key' => 'select', 'label' => '', 'sortable' => false],
                ['key' => 'id', 'label' => 'ID', 'sortable' => true],
                ['key' => 'fecha', 'label' => 'Fecha', 'sortable' => true],
                ['key' => 'codigo', 'label' => 'Código', 'sortable' => true],
                ['key' => 'valor', 'label' => 'Valor', 'sortable' => true],
                ['key' => 'cliente', 'label' => 'Cliente', 'sortable' => true],
                ['key' => 'expiracion', 'label' => 'Expiración', 'sortable' => true],
                ['key' => 'estado', 'label' => 'Estado', 'sortable' => true],
                ['key' => 'acciones', 'label' => 'Acciones', 'sortable' => false],
            ];
        @endphp

        <x-saved-views-table 
            view-name="tarjetas-regalo" 
            search-placeholder="Buscar tarjetas de regalo"
            save-button-text="Guardar vista de tabla"
            :columns="$columns"
            :sort-field="$sortField"
            :sort-direction="$sortDirection"
            :show-mobile="true"
            :select-all="$selectAll"
            :selected="$selected"
        >
            {{-- Tabs predefinidos --}}
            <x-slot name="predefinedTabs">
                <button 
                    wire:click="setFilter('todos')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'todos' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Todos
                </button>
                <button 
                    wire:click="setFilter('activos')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'activos' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Activos
                </button>
                <button 
                    wire:click="setFilter('expirados')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'expirados' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Expirados
                </button>
                <button 
                    wire:click="setFilter('usados')"
                    class="px-3 py-1.5 text-sm font-medium rounded transition-colors whitespace-nowrap {{ $activeFilter === 'usados' ? 'bg-zinc-900 text-white dark:bg-zinc-700' : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                >
                    Usados
                </button>
            </x-slot>
            
            {{-- Dropdown de filtros --}}
            <x-slot name="filtersDropdown">
                <flux:dropdown class="flex-shrink-0">
                    <flux:button size="sm" class="px-3 py-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded transition-colors whitespace-nowrap flex items-center gap-1">
                        Agregar filtro
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </flux:button>
                    
                    <flux:menu class="w-80">
                        <div class="p-3">
                            <flux:input 
                                wire:model.live.debounce.300ms="filterSearch"
                                placeholder="Buscar..."
                                icon="magnifying-glass"
                                class="mb-2"
                            />
                            
                            <div class="max-h-96 overflow-y-auto">
                                {{-- Estado --}}
                                <div class="mb-2">
                                    <flux:separator text="Estado" />
                                    <flux:menu.item wire:click="addFilter('estado_activo', null, 'Estado: Activo')">
                                        Activo
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('estado_expirado', null, 'Estado: Expirado')">
                                        Expirado
                                    </flux:menu.item>
                                    <flux:menu.item wire:click="addFilter('estado_usado', null, 'Estado: Usado')">
                                        Usado
                                    </flux:menu.item>
                                </div>
                                
                                {{-- Clientes --}}
                                <div class="mb-2">
                                    <flux:separator text="Cliente" />
                                    @foreach($customers->take(10) as $customer)
                                        <flux:menu.item wire:click="addFilter('customer', {{ $customer->id }}, 'Cliente: {{ $customer->name }}')">
                                            {{ $customer->name }}
                                        </flux:menu.item>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </flux:menu>
                </flux:dropdown>
            </x-slot>

            {{-- Acciones masivas --}}
            <x-slot name="bulkActions">
                <flux:button size="xs" icon="printer">
                    Imprimir códigos
                </flux:button>
                
                <flux:dropdown>
                    <flux:button icon:trailing="chevron-down" size="xs">
                        Marcar como
                    </flux:button>
                    
                    <flux:menu class="min-w-40">
                        <flux:menu.item wire:click="markAsStatus(1)">
                            Activo
                        </flux:menu.item>
                        <flux:menu.item wire:click="markAsStatus(2)">
                            Expirado
                        </flux:menu.item>
                        <flux:menu.item wire:click="markAsStatus(3)">
                            Usado
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </x-slot>

            {{-- Contenido de la tabla --}}
            <x-slot name="desktop">
                @forelse($giftCards as $giftCard)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 {{ in_array($giftCard->id, $selected) ? 'bg-lime-50 dark:bg-lime-900/20' : '' }}">
                        <td class="px-4 py-3">
                            <flux:checkbox wire:model.live.debounce.150ms="selected" value="{{ $giftCard->id }}" />
                        </td>
                        <td class="px-4 py-3">
                            <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                #{{ str_pad($giftCard->id, 4, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $giftCard->created_at->isToday() ? 'Hoy a las ' . $giftCard->created_at->format('H:i') : $giftCard->created_at->format('d M, Y H:i') }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-mono text-zinc-900 dark:text-white bg-zinc-100 dark:bg-zinc-700 px-2 py-1 rounded">
                                {{ $giftCard->code }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                L {{ number_format($giftCard->valor_inicial, 2) }}
                            </div>
                            @if($giftCard->valor_usado > 0)
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Usado: L {{ number_format($giftCard->valor_usado, 2) }}
                                </div>
                                <div class="text-xs font-medium text-lime-600 dark:text-lime-400">
                                    Restante: L {{ number_format($giftCard->valor_restante, 2) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $giftCard->customer ? $giftCard->customer->name : '—' }}
                            </div>
                            @if($giftCard->customer && $giftCard->customer->email)
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $giftCard->customer->email }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-900 dark:text-white">
                                {{ $giftCard->expiry_date ? $giftCard->expiry_date->format('d M, Y') : '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" :color="$giftCard->status_color" variant="soft">
                                {{ $giftCard->status_text }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if($giftCard->id_status_gift_card === 1 && $giftCard->valor_restante > 0)
                                    <flux:button variant="ghost" size="xs" wire:click="openUseModal({{ $giftCard->id }})" icon="credit-card">
                                        Usar
                                    </flux:button>
                                @endif
                                @if($giftCard->customer && $giftCard->customer->email)
                                    <flux:button variant="ghost" size="xs" wire:click="sendEmail({{ $giftCard->id }})" icon="envelope">
                                        Enviar
                                    </flux:button>
                                @endif
                                <flux:button variant="ghost" size="xs" wire:click="edit({{ $giftCard->id }})" icon="pencil-square">
                                    Editar
                                </flux:button>
                                <flux:button variant="ghost" size="xs" wire:click="delete({{ $giftCard->id }})" icon="trash" onclick="confirm('¿Estás seguro?') || event.stopImmediatePropagation()">
                                    Eliminar
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-zinc-500 dark:text-zinc-400">
                            No se encontraron tarjetas de regalo
                        </td>
                    </tr>
                @endforelse
            </x-slot>

            {{-- Vista móvil --}}
            <x-slot name="mobile">
                @forelse($giftCards as $giftCard)
                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 mb-4 {{ in_array($giftCard->id, $selected) ? 'bg-lime-50 dark:bg-lime-900/20 border-lime-300 dark:border-lime-700' : 'bg-white dark:bg-zinc-800' }}">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <flux:checkbox wire:model.live.debounce.150ms="selected" value="{{ $giftCard->id }}" />
                                <div>
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                        #{{ str_pad($giftCard->id, 4, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $giftCard->created_at->format('d M, Y H:i') }}
                                    </div>
                                </div>
                            </div>
                            <flux:badge size="sm" :color="$giftCard->status_color" variant="soft">
                                {{ $giftCard->status_text }}
                            </flux:badge>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Código:</span>
                                <span class="text-sm font-mono bg-zinc-100 dark:bg-zinc-700 px-2 py-0.5 rounded">{{ $giftCard->code }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Valor:</span>
                                <div class="text-right">
                                    <div class="text-sm font-medium">L {{ number_format($giftCard->valor_inicial, 2) }}</div>
                                    @if($giftCard->valor_usado > 0)
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Usado: L {{ number_format($giftCard->valor_usado, 2) }}</div>
                                        <div class="text-xs font-medium text-lime-600 dark:text-lime-400">Restante: L {{ number_format($giftCard->valor_restante, 2) }}</div>
                                    @endif
                                </div>
                            </div>
                            @if($giftCard->customer)
                                <div class="flex justify-between">
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Cliente:</span>
                                    <span class="text-sm">{{ $giftCard->customer->name }}</span>
                                </div>
                            @endif
                            @if($giftCard->expiry_date)
                                <div class="flex justify-between">
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">Expira:</span>
                                    <span class="text-sm">{{ $giftCard->expiry_date->format('d M, Y') }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex justify-end gap-2 mt-3 pt-3 border-t border-zinc-200 dark:border-zinc-700">
                            @if($giftCard->id_status_gift_card === 1 && $giftCard->valor_restante > 0)
                                <flux:button variant="ghost" size="xs" wire:click="openUseModal({{ $giftCard->id }})" icon="credit-card">
                                    Usar
                                </flux:button>
                            @endif
                            @if($giftCard->customer && $giftCard->customer->email)
                                <flux:button variant="ghost" size="xs" wire:click="sendEmail({{ $giftCard->id }})" icon="envelope">
                                    Enviar
                                </flux:button>
                            @endif
                            <flux:button variant="ghost" size="xs" wire:click="edit({{ $giftCard->id }})" icon="pencil-square">
                                Editar
                            </flux:button>
                            <flux:button variant="ghost" size="xs" wire:click="delete({{ $giftCard->id }})" icon="trash" onclick="confirm('¿Estás seguro?') || event.stopImmediatePropagation()">
                                Eliminar
                            </flux:button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                        No se encontraron tarjetas de regalo
                    </div>
                @endforelse
            </x-slot>
        </x-saved-views-table>
    </div>

    {{-- Modal de creación/edición --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="bg-white dark:bg-zinc-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <flux:heading size="lg">
                    {{ $isEditing ? 'Editar Tarjeta de Regalo' : 'Nueva Tarjeta de Regalo' }}
                </flux:heading>
                <flux:button wire:click="cancel" variant="ghost" size="sm" icon="x-mark" />
            </div>

            <div class="space-y-4">
                <div>
                    <flux:input wire:model="code" label="Código" placeholder="Ej: GIFT-2024-001" required />
                    <flux:error name="code" />
                </div>

                <div>
                    <flux:input wire:model="valor_inicial" label="Valor inicial" type="number" step="0.01" min="0" placeholder="0.00" required />
                    <flux:error name="valor_inicial" />
                </div>

                <div>
                    <flux:input wire:model="expiry_date" label="Fecha de expiración" type="date" />
                    <flux:error name="expiry_date" />
                </div>

                <div>
                    <flux:select wire:model="id_customer" label="Cliente" placeholder="Seleccionar cliente (opcional)">
                        @foreach($customers as $customer)
                            <flux:select.option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->email }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="id_customer" />
                </div>

                <div>
                    <flux:select wire:model="id_status_gift_card" label="Estado" required>
                        <flux:select.option value="1">Activo</flux:select.option>
                        <flux:select.option value="2">Expirado</flux:select.option>
                        <flux:select.option value="3">Usado</flux:select.option>
                    </flux:select>
                    <flux:error name="id_status_gift_card" />
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <flux:button wire:click="cancel" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button wire:click="{{ $isEditing ? 'update' : 'store' }}" variant="primary">
                    {{ $isEditing ? 'Actualizar' : 'Crear' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal de exportación --}}
    <flux:modal wire:model="showExportModal" class="max-w-md">
        <div class="bg-white dark:bg-zinc-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <flux:heading size="lg">Exportar tarjetas de regalo</flux:heading>
                <flux:button wire:click="closeExportModal" variant="ghost" size="sm" icon="x-mark" />
            </div>

            <div class="space-y-4">
                <div>
                    <flux:radio.group wire:model="exportOption" label="¿Qué deseas exportar?">
                        <flux:radio value="current_page" label="Página actual" />
                        <flux:radio value="all" label="Todas las tarjetas" />
                        <flux:radio value="selected" label="Solo seleccionadas" />
                        <flux:radio value="search" label="Resultados de búsqueda" />
                        <flux:radio value="filtered" label="Vista filtrada actual" />
                    </flux:radio.group>
                </div>

                <div>
                    <flux:radio.group wire:model="exportFormat" label="Formato">
                        <flux:radio value="csv" label="CSV para Excel" />
                        <flux:radio value="plain_csv" label="CSV simple" />
                    </flux:radio.group>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <flux:button wire:click="closeExportModal" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button wire:click="export" variant="primary">
                    Exportar
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal para usar tarjeta --}}
    <flux:modal wire:model="showUseModal" class="max-w-md">
        <div class="bg-white dark:bg-zinc-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <flux:heading size="lg">Usar Tarjeta de Regalo</flux:heading>
                <flux:button wire:click="closeUseModal" variant="ghost" size="sm" icon="x-mark" />
            </div>

            @if($useGiftCardId)
                @php
                    $currentCard = $giftCards->firstWhere('id', $useGiftCardId);
                @endphp
                
                @if($currentCard)
                    <div class="mb-4 p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                        <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $currentCard->code }}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                            Saldo disponible: <span class="font-medium text-lime-600 dark:text-lime-400">L {{ number_format($currentCard->valor_restante, 2) }}</span>
                        </div>
                        @if($currentCard->valor_usado > 0)
                            <div class="w-full bg-zinc-200 dark:bg-zinc-600 rounded-full h-2 mt-2">
                                <div class="bg-lime-600 h-2 rounded-full" style="width: {{ $currentCard->porcentaje_usado }}%"></div>
                            </div>
                        @endif
                    </div>
                @endif
            @endif

            <div class="space-y-4">
                <div>
                    <flux:input wire:model="useAmount" label="Monto a usar" type="number" step="0.01" min="0.01" placeholder="0.00" required />
                    <flux:error name="useAmount" />
                </div>

                <div>
                    <flux:textarea wire:model="useDescription" label="Descripción (opcional)" placeholder="Ej: Compra en tienda, pedido #123" rows="2" />
                    <flux:error name="useDescription" />
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <flux:button wire:click="closeUseModal" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button wire:click="useGiftCard" variant="primary">
                    Usar Tarjeta
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
