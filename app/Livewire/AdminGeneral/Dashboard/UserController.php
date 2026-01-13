<?php

namespace App\Livewire\AdminGeneral\Dashboard;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class UserController extends Component
{

    public function render()
    {
       return view('livewire.admin-general.dashboard.user-controller');
    }
}