<?php

namespace App\Livewire\AdminGeneral\Marlon;
use Livewire\Component;

use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class UserController extends Component
{
   
    public function render()
    {
       return view('livewire.admin-general.marlon.user-controller');
    }
}