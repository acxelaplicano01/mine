<?php

use Rk\RoutingKit\Contracts\RkEntityInterface;
use Rk\RoutingKit\Entities\RkRoute;

return [

    RkRoute::makeGroup('grupo_auth')
        ->setUrlMiddleware(['auth', 'verified'])
        ->setItems([
            RkRoute::make('usercontroller_D0Q')
                ->setParentId('grupo_auth')
                ->setAccessPermission('acceder-usercontroller_D0Q')
                ->setUrl('/usercontroller_D0Q')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\AdminGeneral\Dashboard\UserController')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('usercontroller_D0Q'),

            RkRoute::make('user_cuenta')
                 ->setParentId('grupo_auth')
                ->setAccessPermission('acceder-usercuenta')
                ->setUrl('/user-cuenta')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\UserCuenta\UserCuentas')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('user_cuenta'),

            RkRoute::make('list_products')
                ->setAccessPermission('acceder-list-products')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Product\Products')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('list_products'),

            RkRoute::make('transfers')
                ->setAccessPermission('acceder-transfers')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Product\Transfers')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('transfers'),

            RkRoute::make('collections')
                ->setAccessPermission('acceder-collections')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Product\Colections')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('collections'),

            RkRoute::make('orders')
                ->setAccessPermission('acceder-orders')
                ->setUrl('/orders')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Order\Orders')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('orders'),

            RkRoute::make('orders_create')
                ->setAccessPermission('acceder-orders-create')
                ->setUrl('/orders/create')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Order\CreateOrder')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('orders_create'),

            RkRoute::make('giftscards')
                ->setAccessPermission('acceder-giftscards')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Product\GiftCards')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('giftscards'),

            RkRoute::make('inventories')
                ->setAccessPermission('acceder-inventories')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Product\Inventories')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('inventories'),

            RkRoute::make('orders_purchases')
                ->setAccessPermission('acceder-orders-purchases')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Product\OrderPurchases')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('orders_purchases'),

            RkRoute::make('customers')
                ->setAccessPermission('acceder-customers')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Customer\Customers')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('customers'),

            RkRoute::make('segments')
                ->setAccessPermission('acceder-segments')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Customer\Segments')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('segments'),

            RkRoute::make('orders')
                ->setAccessPermission('acceder-orders')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Order\Orders')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('orders'),

            RkRoute::make('draft')
                ->setAccessPermission('acceder-draft')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Order\Drafts')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('draft'),

            RkRoute::make('abandoned_order')
                ->setAccessPermission('acceder-abandoned_order')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Order\AbandonedOrders')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('abandoned_order'),

             RkRoute::make('marketing')
                ->setAccessPermission('acceder-marketing')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Marketing\Marketing')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('marketing'),

            RkRoute::make('campaigns')
                ->setAccessPermission('acceder-campaigns')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Marketing\Campaigns')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('campaigns'),

            RkRoute::make('attribution')
                ->setAccessPermission('acceder-atribution')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Marketing\Attribution')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('attribution'),

            RkRoute::make('automations')
                ->setAccessPermission('acceder-automations')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Marketing\Automations')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('automations'),
    
            RkRoute::make('discounts')
                ->setAccessPermission('acceder-discounts')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Discount\Discounts')
                ->setRoles(['admin_general'])
                ->setItems([])            
                ->setEndBlock('discounts'),

            RkRoute::make('discounts_create')
                ->setAccessPermission('acceder-discounts-create')
                ->setUrl('/discounts/create')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Discount\CreateDiscount')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('discounts_create'),

            RkRoute::make('discounts_edit')
                ->setAccessPermission('acceder-discounts-edit')
                ->setUrl('/discounts/{id}/edit')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Discount\CreateDiscount')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('discounts_edit'),
           
            RkRoute::make('markets')
                ->setAccessPermission('acceder-markets')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Market\Markets')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('markets'),

            RkRoute::make('catalogs')
                ->setAccessPermission('acceder-catalogs')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\Market\Catalogs')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('catalogs'),

        ])
        ->setEndBlock('grupo_auth'),
];
