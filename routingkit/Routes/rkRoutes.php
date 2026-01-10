<?php

use Rk\RoutingKit\Entities\RkRoute;

return [

    RkRoute::makeGroup('grupo_auth')
        ->setUrlMiddleware(['auth'])
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

            RkRoute::make('usercontroller_yff')
                ->setParentId('grupo_auth')
                ->setAccessPermission('acceder-usercontroller_yff')
                ->setUrlMethod('get')
                ->setUrlController('App\Livewire\AdminGeneral\Marlon\UserController')
                ->setRoles(['admin_general'])
                ->setItems([])
                ->setEndBlock('usercontroller_yff'),
        ])
        ->setEndBlock('grupo_auth'),
];
