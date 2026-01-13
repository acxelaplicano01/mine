<?php

namespace App\Livewire\UserCuenta;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.cuenta')]
class UserCuentas extends Component
{
    public function render()
    {
        return view('livewire.user-cuenta.user-cuentas');
    }
}
