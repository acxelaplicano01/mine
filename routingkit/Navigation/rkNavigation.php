<?php

use Rk\RoutingKit\Entities\RkNavigation;

return [

    
        RkNavigation::make('usercontroller_D0Q')
            ->setParentId('dashboard_group')
            ->setLabel('Inicio')
            ->setDescription('Panel de Control')
            ->setHeroIcon('home')
            ->setItems([])
            ->setEndBlock('usercontroller_D0Q'),

        RkNavigation::makeGroup('orders_group')
        ->setLabel('Pedidos')
        ->setHeroIcon('cube')
        ->setItems([
            
            RkNavigation::make('orders')
                ->setLabel('Pedidos')
                ->setDescription('Gestión de Pedidos')
                ->setHeroIcon('inbox')
                ->setItems([
                    RkNavigation::make('orders_create')
                        ->setLabel('Crear Pedido')
                        ->setDescription('Crear un nuevo pedido')
                        ->setHeroIcon('plus-circle')
                        ->setItems([])
                        ->setEndBlock('orders_create'),
                ])
                ->setEndBlock('orders'),

            RkNavigation::make('draft')
                ->setLabel('Borradores')
                ->setDescription('Gestión de Borradores')
                ->setHeroIcon('clipboard-document')
                ->setItems([])
                ->setEndBlock('draft'),
            
            RkNavigation::make('abandoned_order')
                ->setLabel('Pedidos Abandonados')
                ->setDescription('Gestión de Pedidos Abandonados')
                ->setHeroIcon('archive-box-x-mark')
                ->setItems([])
                ->setEndBlock('abandoned_order'),

            
        ])
        ->setEndBlock('orders_group'),

        RkNavigation::makeGroup('products_group')
        ->setLabel('Productos')
        ->setDescription('Gestión de Productos')
        ->setHeroIcon('tag')
        ->setItems([
            
            RkNavigation::make('list_products')
                ->setLabel('Productos')
                ->setDescription('Gestión de Productos')
                ->setHeroIcon('tag')
                ->setItems([])
                ->setEndBlock('list_products'),

            RkNavigation::make('collections')
                ->setLabel('Colecciones')
                ->setDescription('Gestión de Colecciones')
                ->setHeroIcon('rectangle-stack')
                ->setItems([])
                ->setEndBlock('collections'),

            RkNavigation::make('inventories')
                ->setLabel('Inventarios')
                ->setDescription('Gestión de Inventarios')
                ->setHeroIcon('archive-box')
                ->setItems([])
                ->setEndBlock('inventories'),

            RkNavigation::make('orders_purchases')
                ->setLabel('Órdenes y Compras')
                ->setDescription('Gestión de Órdenes y Compras')
                ->setHeroIcon('shopping-cart')
                ->setItems([])
                ->setEndBlock('orders_purchases'),
                
            RkNavigation::make('transfers')
                ->setLabel('Reubicación')
                ->setDescription('Gestión de Reubicación de Productos')
                ->setHeroIcon('arrows-right-left')
                ->setItems([])
                ->setEndBlock('transfers'),
            
            RkNavigation::make('giftscards')
                ->setLabel('Tarjeta de Regalo')
                ->setDescription('Gestión de Tarjetas de Regalo')
                ->setHeroIcon('gift')
                ->setItems([])
                ->setEndBlock('giftscards'),

        ])
        ->setEndBlock('products_group'),

        RkNavigation::makeGroup('customers_group')
        ->setLabel('Clientes')
        ->setDescription('Gestión de Clientes')
        ->setHeroIcon('user-group')
        ->setItems([
            
            RkNavigation::make('customers')
                ->setLabel('Clientes')
                ->setDescription('Gestión de Clientes')
                ->setHeroIcon('user-group')
                ->setItems([])
                ->setEndBlock('customers'),

            RkNavigation::make('segments')
                ->setLabel('Segmentos')
                ->setDescription('Gestión de Segmentos')
                ->setHeroIcon('puzzle-piece')
                ->setItems([])
                ->setEndBlock('segments'),
        ])
        ->setEndBlock('customers_group'),

        RkNavigation::makeGroup('marketing_group')
        ->setLabel('Marketing')
        ->setDescription('Gestión de Marketing')
        ->setHeroIcon('cursor-arrow-ripple')
        ->setItems([
            
             RkNavigation::make('marketing')
                ->setLabel('Marketing')
                ->setDescription('Gestión de Marketing')
                ->setHeroIcon('cursor-arrow-ripple')
                ->setItems([])
                ->setEndBlock('marketing'),

            RkNavigation::make('campaigns')
                ->setLabel('Campañas')
                ->setDescription('Gestión de Campañas')
                ->setHeroIcon('megaphone')
                ->setItems([])
                ->setEndBlock('campaigns'),

            RkNavigation::make('attribution')
                ->setLabel('Atribución')
                ->setDescription('Gestión de Atribución')
                ->setHeroIcon('chart-bar-square')
                ->setItems([])
                ->setEndBlock('attribution'),

            RkNavigation::make('automations')
                ->setLabel('Automatizaciones')
                ->setDescription('Gestión de Automatizaciones')
                ->setHeroIcon('adjustments-horizontal')
                ->setItems([])
                ->setEndBlock('automations'),
        ])
        ->setEndBlock('marketing_group'),

        RkNavigation::make('discounts')
            ->setParentId('discounts_group')
            ->setLabel('Descuentos')
            ->setDescription('Gestión de Descuentos')
            ->setHeroIcon('percent-badge')
            ->setItems([])
            ->setEndBlock('discounts'),

         RkNavigation::makeGroup('markets_group')
        ->setLabel('Mercados')
        ->setDescription('Gestión de Mercados y Catálogos')
        ->setHeroIcon('globe-alt')
        ->setItems([
            RkNavigation::make('markets')
                ->setLabel('Mercados')
                ->setDescription('Gestión de Mercados')
                ->setHeroIcon('globe-alt')
                ->setItems([])
                ->setEndBlock('markets'),

            RkNavigation::make('catalogs')
                ->setLabel('Catálogos')
                ->setDescription('Gestión de Catálogos')
                ->setHeroIcon('book-open')
                ->setItems([])
                ->setEndBlock('catalogs'),
        ])
        ->setEndBlock('markets_group'),

        
];
