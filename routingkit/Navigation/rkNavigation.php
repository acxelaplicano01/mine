<?php

use Rk\RoutingKit\Entities\RkNavigation;

return [

    RkNavigation::makeGroup('dashboard_group')
        ->setLabel('Main Group')
        ->setHeroIcon('home')
        ->setItems([])
        ->setEndBlock('dashboard_group'),

    RkNavigation::makeSimple('dashboard')
        ->setParentId('dashboard_group')
        ->setLabel('Dashboard')
        ->setHeroIcon('plus')
        ->setEndBlock('dashboard'),

    RkNavigation::make('usercontroller_D0Q')
        ->setParentId('dashboard_group')
        ->setLabel('User Controller D0Q')
        ->setBageInt(2)
        ->setHeroIcon('plus')
        ->setItems([])
        ->setEndBlock('usercontroller_D0Q'),

    RkNavigation::make('usercontroller_yff')
        ->setLabel('MARLON ')
        ->setHeroIcon('plus')
        ->setItems([])
        ->setEndBlock('usercontroller_yff'),


    RkNavigation::make('marlon_test', 'usercontroller_D0Q')
        ->setLabel('Marlon Test')
        ->setHeroIcon('plus')
        ->setEndBlock('marlon_test'),


];
